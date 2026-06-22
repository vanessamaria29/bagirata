<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Member;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Tampilan Dashboard Utama & List Semua Tagihan
     */
    public function index()
    {
        $activities = auth()->user()->activities()->latest()->get();

        if (request()->routeIs('activities.index')) {
            return view('activities.index', compact('activities'));
        }

        $total_spent = $activities->sum('total_amount');
        $active_sessions = $activities->where('status', 'active')->count();

        $stuck_money = 0;
        foreach ($activities as $activity) {
            if ($activity->status === 'active') {
                $stuck_money += $activity->member_breakdown->where('payment_status', 'unpaid')->sum('total');
            }
        }

        $trips = auth()->user()->trips()->with('participants')->get();
        $standalone_activities = $activities->whereNull('trip_id');

        $feed = $trips->concat($standalone_activities)->sortByDesc('created_at')->values();

        return view('dashboard', compact('feed', 'total_spent', 'active_sessions', 'stuck_money'));
    }

    /**
     * Tampilan Form Create Sesi Baru
     */
    public function create()
    {
        $trips = auth()->user()->trips()->where('status', 'active')->with('participants')->get();

        return view('activities.create', compact('trips'));
    }

    /**
     * REAL-TIME OCR SCAN
     */
    public function scanStruk(Request $request)
    {
        // 1. Validasi file gambar (Maksimal 4MB)
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        try {
            $file = $request->file('image');
            $fileContents = file_get_contents($file->getRealPath());
            $credentialsPath = storage_path('google-credentials.json');

            $httpClient = new Client(['verify' => false]);
            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => $credentialsPath,
                'transportConfig' => [
                    'rest' => [
                        'httpHandler' => function ($request, $options = []) use ($httpClient) {
                            return $httpClient->sendAsync($request, $options);
                        },
                    ],
                ],
            ]);

            // Gunakan documentTextDetection untuk struk
            $response = $imageAnnotator->documentTextDetection($fileContents);
            $texts = $response->getTextAnnotations();

            // Jika Google tidak menemukan teks sama sekali
            if (count($texts) === 0) {
                $imageAnnotator->close();

                return response()->json(['error' => 'Gambar kurang jelas atau teks tidak ditemukan.'], 422);
            }

            // Ekstrak teks penuh (elemen pertama adalah teks gabungan)
            $fullText = $texts[0]->getDescription();
            \Log::info("=== OCR TEXT ===\n".$fullText);

            $imageAnnotator->close();

            // ==========================================
            // 5. MULAI PARSING TEKS (Tokenization-based)
            // ==========================================
            $lines = explode("\n", $fullText);

            $merchantName = null;
            $dateFound = null;
            $itemsFound = [];

            // Regex patterns
            // Date: DD/MM/YYYY or DD-MM-YYYY or DD MMM YY
            $datePattern = '/(\d{2}[\/\-]\d{2}[\/\-]\d{2,4})|(\d{1,2}\s+[a-zA-Z]{3,9}\s+\d{2,4})/i';

            $ignoredWords = [
                'Total', 'Subtotal', 'Amount', 'Net', 'Jml', 'Bayar', 'Cash', 'Change', 'Kembali',
                'Tunai', 'Debit', 'Credit', 'Visa', 'Master', 'Card', 'Tax', 'Ppn', 'Pb1', 'Service',
                'Harga', 'Price', 'Qty', 'Item', 'Shift', 'Pos', 'No', 'Check', 'Bill', 'Order',
                'Table', 'Meja', 'Trans', 'Ref', 'Auth', 'Telp', 'Fax', 'Call', 'Jl', 'Jalan',
                'Thank', 'Terima', 'Kasih', 'Welcome', 'Selamat', 'Datang', 'Operator', 'Kasir', 'Cashier',
                'Disc', 'Diskon', 'Tanggal', 'Date', 'Waktu', 'Time', 'Jam', 'taxable', 'Payment', 'Pembayaran',
                'BCA', 'Mandiri', 'BNI', 'BRI', 'Gopay', 'OVO', 'Dana', 'LinkAja', 'QRIS',
                '小計', '合計', 'お支払い', 'カード', 'お釣り', 'レシート', '伝票番号',
            ];

            $pendingName = '';
            $pendingTax = false;
            $pendingService = false;
            $taxFound = 0;
            $serviceFound = 0;

            foreach ($lines as $line) {
                $cleanLine = trim($line);
                if (empty($cleanLine)) {
                    continue;
                }

                // --- 1. PARSING TANGGAL ---
                if (! $dateFound && preg_match($datePattern, $cleanLine, $matches)) {
                    $matchedDate = $matches[0];
                    try {
                        $dateString = str_replace('/', '-', $matchedDate);
                        $parsedDate = Carbon::parse($dateString);
                        $dateFound = $parsedDate->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }

                // --- 2. PARSING NAMA TOKO ---
                if (! $merchantName) {
                    $cleanForMerchant = trim(preg_replace('/[^\p{L}]/u', '', $cleanLine));
                    if (mb_strlen($cleanForMerchant) >= 3) {
                        $isIgnored = false;
                        foreach ($ignoredWords as $word) {
                            if (stripos($cleanLine, $word) !== false) {
                                $isIgnored = true;
                                break;
                            }
                        }
                        if (! $isIgnored) {
                            $merchantName = $cleanLine;
                        }
                    }
                }

                // --- 3. PARSING BARANG & HARGA (UNIVERSAL CURRENCY PARSER) ---

                // Cek jika baris ini adalah nominal pajak/service dari baris sebelumnya (multi-line OCR)
                if ($pendingTax) {
                    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
                        $rawPrice = $priceMatches[1];
                        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
                        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);
                        if ($isDecimalMode) {
                            $taxFound = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
                        } else {
                            $taxFound = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
                        }
                    }
                    $pendingTax = false;

                    continue;
                }

                if ($pendingService) {
                    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
                        $rawPrice = $priceMatches[1];
                        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
                        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);
                        if ($isDecimalMode) {
                            $serviceFound = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
                        } else {
                            $serviceFound = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
                        }
                    }
                    $pendingService = false;

                    continue;
                }

                // --- INTERCEPTOR PAJAK & SERVICE ---
                if (preg_match('/(tax|ppn|pb1|消費税)/i', $cleanLine)) {
                    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
                        $rawPrice = $priceMatches[1];
                        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
                        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);
                        if ($isDecimalMode) {
                            $taxFound = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
                        } else {
                            $taxFound = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
                        }
                    } else {
                        // Jika nominal pajak tidak ditemukan di baris yang sama, tangkap di baris berikutnya
                        $pendingTax = true;
                    }

                    continue;
                }

                if (preg_match('/(service|charge)/i', $cleanLine)) {
                    if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
                        $rawPrice = $priceMatches[1];
                        $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);
                        $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);
                        if ($isDecimalMode) {
                            $serviceFound = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
                        } else {
                            $serviceFound = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
                        }
                    } else {
                        $pendingService = true;
                    }

                    continue;
                }

                // Cek kata kunci sistem dulu
                $isSystemLine = false;
                foreach ($ignoredWords as $word) {
                    // Gunakan stripos/mb_stripos untuk mencocokkan kata (terutama huruf non-latin yang sulit dengan \b)
                    if (mb_stripos($cleanLine, $word, 0, 'UTF-8') !== false) {
                        // Pastikan jika kata bahasa inggris, dia tidak terjepit di tengah kata lain (misal: "Tax" di dalam "Taxable")
                        // Tapi untuk karakter Asia (Hanzi/Kanji dll), kita bisa membiarkannya cocok karena jarang tergabung dalam kata yang salah
                        if (preg_match('/^[a-zA-Z0-9]+$/', $word)) {
                            if (preg_match("/\b".preg_quote($word, '/')."\b/i", $cleanLine)) {
                                $isSystemLine = true;
                                break;
                            }
                        } else {
                            $isSystemLine = true;
                            break;
                        }
                    }
                }
                if ($isSystemLine) {
                    continue;
                }

                // Ambil harga dari bagian kanan string terlebih dahulu
                if (preg_match('/([IlO\d][IlO\d,.\s]{2,})\s*(?:JPY|SGD|USD|IDR|Rp|S\$|\$|¥)?\s*$/i', $cleanLine, $priceMatches)) {
                    $rawPrice = $priceMatches[1];
                    $isDecimalMode = preg_match('/(\$|S\$|USD|SGD)/i', $cleanLine) || preg_match('/\.\d{2}\s*$/', $rawPrice);

                    // Bersihkan string harga
                    $cleanPriceStr = str_ireplace(['l', 'O', 'I'], ['1', '0', '1'], $rawPrice);

                    if ($isDecimalMode) {
                        $cleanPrice = (float) preg_replace('/[^\d.]/', '', $cleanPriceStr);
                    } else {
                        $cleanPrice = (int) preg_replace('/[^\d]/', '', $cleanPriceStr);
                    }

                    if ($cleanPrice <= 0) {
                        continue;
                    } // Skip jika angkanya 0 atau error parsing

                    // Buat sisa string nama dan qty dengan menghapus teks harga dari baris
                    $pos = strrpos($cleanLine, $rawPrice);
                    if ($pos !== false) {
                        $remainingText = trim(substr($cleanLine, 0, $pos));
                    } else {
                        $remainingText = trim(str_replace($rawPrice, '', $cleanLine));
                    }

                    // MULTI-LINE FIX: Jika remainingText kosong (atau hanya berisi simbol mata uang), kemungkinan besar nama item ada di baris sebelumnya (OCR split)
                    $cleanRemaining = trim(preg_replace('/(Rp|IDR|USD|SGD|JPY|S\$|\$|¥)/i', '', $remainingText));
                    if (empty($cleanRemaining) && ! empty($pendingName)) {
                        $remainingText = $pendingName;
                    }

                    // Ekstrak Qty dari sisi paling kiri sisa teks
                    $qty = 1;
                    if (preg_match('/^(\d+)\s*[xX]?\s*/', $remainingText, $qtyMatches)) {
                        $qty = (int) $qtyMatches[1];
                        // Potong bagian depan yang match dengan qty
                        $remainingText = trim(substr($remainingText, strlen($qtyMatches[0])));
                    }

                    // Bersihkan sisa teks akhir untuk nama menu (Hapus simbol currency & karakter aneh)
                    $itemName = preg_replace('/(Rp|IDR|USD|SGD|JPY|S\$|\$|¥)/i', '', $remainingText);
                    $itemName = trim(preg_replace('/[^\p{L}0-9\s.\-]/u', '', $itemName));

                    if (preg_match('/[\p{L}]/u', $itemName)) {
                        $itemsFound[] = [
                            'qty' => $qty,
                            'name' => trim(mb_strtoupper($itemName, 'UTF-8')),
                            'price' => $cleanPrice,
                            'friend' => '',
                        ];
                        $pendingName = ''; // Reset setelah berhasil memasangkan harga dengan nama
                    }
                } else {
                    // Jika baris ini tidak ada harganya, simpan sebagai calon nama item untuk baris berikutnya
                    // Pastikan mengandung huruf dan tidak terlalu panjang
                    if (preg_match('/[\p{L}]/u', $cleanLine) && mb_strlen($cleanLine) < 50) {
                        $pendingName = $cleanLine;
                    }
                }
            }

            \Log::info('=== OCR PARSED ===', [
                'items' => $itemsFound,
                'tax' => $taxFound,
                'service' => $serviceFound,
            ]);

            return response()->json([
                'merchant_name' => $merchantName ?? 'Unknown Merchant',
                'description' => $merchantName ?? 'Unknown Merchant',
                'date' => $dateFound ?? date('Y-m-d'),
                'items' => $itemsFound,
                'tax' => $taxFound,
                'service' => $serviceFound,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses struk: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyimpan Sesi Patungan Final ke Database (Info Sesi, Anggota, & Detail Menu)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'friends' => 'nullable|array',
            'tax' => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'trip_id' => 'nullable|exists:trips,id',
            'currency' => 'nullable|string|in:IDR,USD,SGD,JPY',
        ]);

        $currency = $request->input('currency', 'IDR');
        $service = new CurrencyService;
        $rate = $service->getRateFromIdrTo($currency);

        $taxForeign = (float) ($request->tax ?? 0);
        $scForeign = (float) ($request->service_charge ?? 0);

        $taxIdr = $rate > 0 ? $taxForeign / $rate : $taxForeign;
        $scIdr = $rate > 0 ? $scForeign / $rate : $scForeign;

        // 1. Buat Sesi Utamanya dulu
        $activity = auth()->user()->activities()->create([
            'title' => $request->title,
            'location' => $request->location,
            'event_date' => $request->event_date,
            'status' => 'active',
            'split_type' => $request->split_type ?? 'proportional',
            'total_amount' => 0,
            'tax' => $taxIdr,
            'service_charge' => $scIdr,
            'trip_id' => $request->trip_id,
            'original_currency' => $currency,
            'exchange_rate' => $rate,
            'original_amount' => 0,
        ]);

        // 2. Simpan nama-nama teman yang ikut patungan ke database
        if ($request->has('friends')) {
            foreach ($request->friends as $friendName) {
                if (! empty($friendName)) {
                    $activity->members()->create([
                        'name' => strtoupper($friendName),
                    ]);
                }
            }
        }

        // 3. Tangkap array items dan simpan namanya ke kolom friend_name
        $itemsTotalForeign = 0;
        $itemsTotalIdr = 0;
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                if (! empty($item['name']) && ! empty($item['price'])) {
                    $priceForeign = (float) $item['price'];
                    $priceIdr = $rate > 0 ? $priceForeign / $rate : $priceForeign;

                    $activity->items()->create([
                        'name' => strtoupper($item['name']),
                        'price' => $priceIdr,
                        'friend_name' => $item['friend'] ?? null,
                    ]);
                    $itemsTotalForeign += $priceForeign;
                    $itemsTotalIdr += $priceIdr;
                }
            }
        }

        $activity->update([
            'total_amount' => $itemsTotalIdr + $taxIdr + $scIdr,
            'original_amount' => $itemsTotalForeign + $taxForeign + $scForeign,
        ]);

        return redirect()->route('dashboard')->with('success', 'Sesi Patungan Berhasil Disimpan!');
    }

    /**
     * Tampilan Detail Sesi (Show)
     */
    public function show(Activity $activity)
    {
        return view('activities.show', compact('activity'));
    }

    /**
     * Tampilan Form Edit Sesi
     */
    public function edit(Activity $activity)
    {
        $trips = auth()->user()->trips()->where('status', 'active')->get();

        return view('activities.edit', compact('activity', 'trips'));
    }

    /**
     * Proses Update Data Sesi
     */
    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'tax' => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'trip_id' => 'nullable|exists:trips,id',
        ]);

        $rate = $activity->exchange_rate ?: 1.0;
        $taxIdr = (float) ($request->tax ?? 0) * $rate;
        $scIdr = (float) ($request->service_charge ?? 0) * $rate;

        $activity->update([
            'title' => $request->title,
            'location' => $request->location,
            'event_date' => $request->event_date,
            'tax' => $taxIdr,
            'service_charge' => $scIdr,
            'trip_id' => $request->trip_id,
        ]);

        // Recalculate total_amount = sum of items + tax + service_charge
        $itemsTotalIdr = $activity->items()->sum('price');
        $activity->update([
            'total_amount' => $itemsTotalIdr + $taxIdr + $scIdr,
            'original_amount' => ($itemsTotalIdr / $rate) + ($request->tax ?? 0) + ($request->service_charge ?? 0),
        ]);

        return redirect()->route('dashboard')->with('success', 'Sesi Berhasil Diperbarui!');
    }

    /**
     * Proses Hapus Sesi (Destroy)
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('dashboard')->with('success', 'Sesi Berhasil Dihapus!');
    }

    /**
     * Mengubah status pembayaran anggota (Settlement Tracker)
     */
    public function togglePayment(Member $member)
    {
        // Pastikan host yang mengakses adalah pemilik sesi kegiatan
        if ($member->activity->user_id !== auth()->id()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // Ubah status pembayaran
        $member->payment_status = $member->payment_status === 'paid' ? 'unpaid' : 'paid';
        $member->save();

        // Cek jika seluruh anggota sudah melunasi pembayaran
        $activity = $member->activity;
        $totalMembersCount = $activity->members()->count();
        $paidMembersCount = $activity->members()->where('payment_status', 'paid')->count();

        if ($totalMembersCount > 0 && $totalMembersCount === $paidMembersCount) {
            $activity->status = 'settled';
        } else {
            $activity->status = 'active';
        }
        $activity->save();

        // Hitung sisa utang yang belum dibayar dalam sesi ini
        $unpaidTotal = 0;
        if ($activity->status === 'active') {
            $unpaidTotal = $activity->member_breakdown->where('payment_status', 'unpaid')->sum('total');
        }

        return response()->json([
            'success' => true,
            'member_id' => $member->id,
            'payment_status' => $member->payment_status,
            'activity_status' => $activity->status,
            'unpaid_total' => $unpaidTotal,
        ]);
    }

    /**
     * Tampilan Publik Share Link Sesi (View-Only POV)
     */
    public function sharedShow($uuid)
    {
        $activity = Activity::where('uuid', $uuid)->with(['members', 'items'])->firstOrFail();

        return view('activities.shared', compact('activity'));
    }

    /**
     * Get rates for AJAX
     */
    public function getRates()
    {
        $service = new CurrencyService;

        $usdRate = $service->getRateFromIdrTo('USD');
        $sgdRate = $service->getRateFromIdrTo('SGD');
        $jpyRate = $service->getRateFromIdrTo('JPY');

        return response()->json([
            'rates' => [
                'USD' => $usdRate > 0 ? 1 / $usdRate : 1,
                'SGD' => $sgdRate > 0 ? 1 / $sgdRate : 1,
                'JPY' => $jpyRate > 0 ? 1 / $jpyRate : 1,
                'IDR' => 1.0,
            ],
        ]);
    }
}
