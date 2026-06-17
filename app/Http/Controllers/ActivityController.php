<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        return view('dashboard', compact('activities', 'total_spent', 'active_sessions', 'stuck_money'));
    }

    /**
     * Tampilan Form Create Sesi Baru
     */
    public function create()
    {
        return view('activities.create');
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

            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => $credentialsPath
            ]);

            // Gunakan documentTextDetection untuk struk
            $response = $imageAnnotator->documentTextDetection($fileContents);
            $texts = $response->getTextAnnotations();

            // Jika Google tidak menemukan teks sama sekali
            if (empty($texts)) {
                $imageAnnotator->close();
                return response()->json(['error' => 'Gambar kurang jelas atau teks tidak ditemukan.'], 422);
            }

            // Ambil teks utuh dan langsung tutup koneksi
            $fullText = $texts[0]->getDescription();
            $imageAnnotator->close();
            
        // ==========================================
        // 5. MULAI PARSING TEKS (Sistem Antrean / FIFO + Qty Memory)
        // ==========================================
        $amountFound = 0;
        $taxFound = 0;
        $serviceFound = 0;
        $isWaitingForTax = false;
        $isWaitingForService = false;
        $descriptionFound = null;
        $dateFound = null;
        $itemsFound = []; 
        $pendingItems = []; 
        $isWaitingForTotal = false; 
        $lastQty = ""; // Khusus menangkap angka '1' atau '2x' yang terputus baris

        $lines = explode("\n", $fullText);
        
        // --- PARSING TANGGAL ---
        if (preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{2,4})/', $fullText, $dateMatches)) {
            try {
                $dateString = str_replace('-', '/', $dateMatches[1]); 
                $parts = explode('/', $dateString);
                if (strlen($parts[2]) == 2) $parts[2] = '20' . $parts[2];
                $dateFound = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; 
            } catch (\Exception $e) {}
        }

        // --- PARSING NAMA TOKO ---
        foreach ($lines as $line) {
            $cleanLine = trim(preg_replace('/[^a-zA-Z0-9\s\.,-]/', '', $line));
            if (strlen($cleanLine) > 3 && !preg_match('/[\d\/]{6,}/', $cleanLine) && stripos($cleanLine, 'Total') === false) {
                $descriptionFound = ucwords(strtolower($cleanLine));
                break; 
            }
        }

        // --- PARSING ITEM MENU & HARGA ---
        $ignoredWords = [
            'Total', 'Subtotal', 'Amount', 'Net', 'Jml', 'Bayar', 'Cash', 'Change', 'Kembali', 
            'Tunai', 'Debit', 'Credit', 'Visa', 'Master', 'Card', 'Tax', 'Ppn', 'Pb1', 'Service', 
            'Harga', 'Price', 'Qty', 'Item', 'Shift', 'Pos', 'No', 'Check', 'Bill', 'Order', 
            'Table', 'Meja', 'Trans', 'Ref', 'Auth', 'Telp', 'Fax', 'Call', 'Jl', 'Jalan', 
            'Thank', 'Terima', 'Kasih', 'Welcome', 'Selamat', 'Datang', 'Operator', 'Kasir', 'Cashier', 
            'Disc', 'Diskon', 'Tanggal', 'Date', 'Waktu', 'Time', 'Jam', 'taxable',
            // Tambahan Pembasmi Teks Header Struk
            'Jakarta', 'Indonesia', 'Business', 'Hours', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'NPWP', 'Shop', 'Till', 'Op', 'Served'
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
        // 1. CARI TOTAL / SUBTOTAL
            if (preg_match('/(Total|Subtotal|Amount|Tagihan|Pay|Grand)/i', $line)) {
                if (preg_match('/([\d\.,]+)$/', $line, $matches)) {
                    $cleanNum = (int)preg_replace('/[^\d]/', '', $matches[1]);
                    if ($cleanNum > $amountFound) $amountFound = $cleanNum;
                } else {
                    $isWaitingForTotal = true; 
                }
                $lastQty = ""; 
                continue;
            }

            if ($isWaitingForTotal && preg_match('/^[IlO\d\.,\s]+$/i', $line)) {
                $cleanNum = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $line));
                if ($cleanNum > $amountFound) $amountFound = $cleanNum;
                $isWaitingForTotal = false;
                continue;
            }

            // 1.5. CARI SERVICE CHARGE & PAJAK (Mode Tahan Banting + Gembok)
            if (preg_match('/(Service|Serv\.|SC)/i', $line)) {
                if (preg_match('/([\d\.,]{4,})$/', trim($line), $matches)) {
                    $val = (int)preg_replace('/[^\d]/', '', $matches[1]);
                    // GEMBOK: Hanya simpan jika serviceFound masih 0 (belum ketemu)
                    if ($val >= 500 && $serviceFound == 0) $serviceFound = $val;
                } else {
                    $isWaitingForService = true;
                }
                $lastQty = "";
                continue;
            }
            if ($isWaitingForService && preg_match('/^[\d\.,\s]+$/', $line)) {
                $val = (int)preg_replace('/[^\d]/', '', $line);
                if ($val >= 500 && $serviceFound == 0) { $serviceFound = $val; $isWaitingForService = false; }
                continue;
            }

            if (preg_match('/(PB1|Ppn|Tax|Pajak)/i', $line) && !preg_match('/(Rate|Amt)/i', $line)) {
                if (preg_match('/([\d\.,]{4,})$/', trim($line), $matches)) {
                    $val = (int)preg_replace('/[^\d]/', '', $matches[1]);
                    // GEMBOK: Hanya simpan jika taxFound masih 0 agar tidak tertimpa 300.300
                    if ($val >= 500 && $taxFound == 0) $taxFound = $val;
                } else {
                    $isWaitingForTax = true;
                }
                $lastQty = "";
                continue;
            }
            if ($isWaitingForTax && preg_match('/^[\d\.,\s]+$/', $line)) {
                $val = (int)preg_replace('/[^\d]/', '', $line);
                if ($val >= 500 && $taxFound == 0) { $taxFound = $val; $isWaitingForTax = false; }
                continue;
            }

            // Cek PPN / Tax / PB1 (Cegah kata Tax Rate / Taxable Amt)
            if (preg_match('/(PB1|Ppn|Tax|Pajak)\b/i', $line) && !preg_match('/(Rate|Amt)/i', $line)) {
                if (preg_match('/([\d\.,]{4,})$/', $line, $matches)) {
                    $val = (int)preg_replace('/[^\d]/', '', $matches[1]);
                    if ($val >= 500) $taxFound = $val;
                } else {
                    $isWaitingForTax = true;
                }
                $lastQty = "";
                continue;
            }
            // Tangkap angka Pajak di baris berikutnya
            if ($isWaitingForTax && preg_match('/^[\d\.,\s]+$/', $line)) {
                $val = (int)preg_replace('/[^\d]/', '', $line);
                if ($val >= 500) { $taxFound = $val; $isWaitingForTax = false; }
                continue;
            }

            // 2. FILTER KATA ABAIKAN (Gunakan Word Boundary \b)
            $isSystemLine = false;
            foreach ($ignoredWords as $word) {
                $escapedWord = preg_quote($word, '/');
                if (preg_match("/\b" . $escapedWord . "\b/i", $line)) {
                    $isSystemLine = true; break;
                }
            }

            if ($isSystemLine) {
                $lastQty = ""; 
                continue;
            }

            // 3. TANGKAP QUANTITY TERPISAH (Hanya angka 1-99 untuk mencegah '00' masuk)
            if (preg_match('/^([1-9]\d?\s*[xX]?)$/i', $line)) {
                $lastQty = $line;
                continue;
            }

            // 4. SKENARIO A: Format Angka Saja (Harga dengan toleransi typo OCR)
            if (preg_match('/^[IlO\d\.,\s]+$/i', $line)) {
                $cleanPrice = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $line));

                // WAJIB KELIPATAN 10: Mengusir angka aneh seperti 8888, 2017, kode pos, dll.
                if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && $cleanPrice % 10 == 0 && count($pendingItems) > 0) {
                    $matchedName = array_shift($pendingItems); 
                    $finalName = strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $matchedName));
                    
                    if (!empty($finalName)) {
                        $itemsFound[] = [
                            'name'   => $finalName,
                            'price'  => $cleanPrice,
                            'friend' => ''
                        ];
                    }
                }
            }
            // 5. SKENARIO B: Format Sebaris (Nama dan Harga gabung)
            elseif (preg_match('/^(.+?)\s+([IlO\d,\.\s]+)$/i', $line, $itemMatches)) {
                $rawPrice = end($itemMatches); 
                $cleanPrice = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $rawPrice));
                $itemName = trim($itemMatches[1]);
                $finalName = strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $itemName));

                if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && $cleanPrice % 10 == 0 && preg_match('/[a-zA-Z]{2,}/', $finalName)) {
                    if (!empty($lastQty)) {
                        $finalName = $lastQty . ' ' . $finalName;
                        $lastQty = "";
                    }
                    
                    $itemsFound[] = [
                        'name'   => $finalName,
                        'price'  => $cleanPrice,
                        'friend' => ''
                    ];
                    $pendingItems = []; 
                }
            }
            // 6. SKENARIO C: Simpan calon nama menu ke antrean
            else {
                $isTimeOrDate = preg_match('/\d{2}:\d{2}/', $line) || preg_match('/\d{2}[\/\-]\d{2}[\/\-]\d{2,4}/', $line);
                $isRestaurantName = ($descriptionFound && stripos($line, $descriptionFound) !== false);
                
                // FILTER KETAT: Baris Wajib memiliki minimal 2 huruf alfabet!
                // Ini akan langsung menendang garis putus-putus (----) atau karakter aneh (***)
                $hasLetters = preg_match('/[a-zA-Z]{2,}/', $line);
                
                if (!$isTimeOrDate && strlen($line) > 2 && strlen($line) < 35 && $hasLetters && !$isRestaurantName) {
                    if (!empty($lastQty)) {
                        $line = $lastQty . ' ' . $line;
                        $lastQty = ""; 
                    }
                    $pendingItems[] = $line; 
                }
            }
        }

        // 6. Balikkan JSON sukses ke front-end
        return response()->json([
            'items'       => $itemsFound,
            'date'        => $dateFound ?? date('Y-m-d'),
            'description' => $descriptionFound,
            'tax'         => $taxFound ?? 0,       // <-- Kirim data pajak
            'service'     => $serviceFound ?? 0    // <-- Kirim data service
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Gagal memproses struk: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Menyimpan Sesi Patungan Final ke Database (Info Sesi, Anggota, & Detail Menu)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'location'   => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'friends'    => 'nullable|array',
            'tax'        => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
        ]);

        // 1. Buat Sesi Utamanya dulu
        $activity = auth()->user()->activities()->create([
            'title'           => $request->title,
            'location'        => $request->location,
            'event_date'      => $request->event_date,
            'status'          => 'active',
            'split_type'      => $request->split_type ?? 'proportional',
            'total_amount'    => 0,
            'tax'             => $request->tax ?? 0,
            'service_charge'  => $request->service_charge ?? 0,
        ]);

        // 2. Simpan nama-nama teman yang ikut patungan ke database
        if ($request->has('friends')) {
            foreach ($request->friends as $friendName) {
                if (!empty($friendName)) {
                    $activity->members()->create([
                        'name' => strtoupper($friendName)
                    ]);
                }
            }
        }

        // 3. Tangkap array items dan simpan namanya ke kolom friend_name
        $itemsTotal = 0;
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                if (!empty($item['name']) && !empty($item['price'])) {
                    $activity->items()->create([
                        'name'        => strtoupper($item['name']),
                        'price'       => (int) $item['price'],
                        'friend_name' => $item['friend'] ?? null 
                    ]);
                    $itemsTotal += (int) $item['price'];
                }
            }
        }

        $activity->update([
            'total_amount' => $itemsTotal + (int) $request->tax + (int) $request->service_charge
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
        return view('activities.edit', compact('activity'));
    }

    /**
     * Proses Update Data Sesi
     */
    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'location'   => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'tax'        => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
        ]);

        $activity->update($request->only(['title', 'location', 'event_date', 'tax', 'service_charge']));

        // Recalculate total_amount = sum of items + tax + service_charge
        $itemsTotal = $activity->items()->sum('price');
        $activity->update([
            'total_amount' => $itemsTotal + (int) $request->tax + (int) $request->service_charge
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
}