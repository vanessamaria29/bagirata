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
            'Table', 'Meja', 'Trans', 'Ref', 'Auth', 'Telp', 'Fax', 'Call', 'Jl.', 'Jalan', 
            'Thank', 'Terima', 'Kasih', 'Welcome', 'Selamat', 'Datang', 'Operator', 'Kasir', 'Cashier', 
            'Disc', 'Diskon', 'Tanggal', 'Date', 'Waktu', 'Time', 'Jam'
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
                $pendingItems = []; 
                $lastQty = ""; // Bersihkan memori qty
                continue;
            }

            if ($isWaitingForTotal && preg_match('/^[IlO\d\.,\s]+$/i', $line)) {
                $cleanNum = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $line));
                if ($cleanNum > $amountFound) $amountFound = $cleanNum;
                $isWaitingForTotal = false;
                continue;
            }

            // 2. FILTER KATA ABAIKAN
            $isSystemLine = false;
            foreach ($ignoredWords as $word) {
                if (stripos($line, $word) !== false) {
                    $isSystemLine = true; break;
                }
            }

            if ($isSystemLine) {
                $pendingItems = []; 
                $lastQty = ""; 
                continue;
            }

            // 3. TANGKAP QUANTITY TERPISAH
            if (preg_match('/^(\d+\s*[xX]?)$/', $line)) {
                $lastQty = $line;
                continue; // Simpan di memori
            }

            // 4. SKENARIO A: Format Angka Saja (Harga dengan toleransi typo OCR)
            if (preg_match('/^[IlO\d\.,\s]+$/i', $line)) {
                // Koreksi otomatis jika huruf O dibaca sebagai 0, atau l/I dibaca sebagai 1
                $cleanPrice = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $line));

                if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && count($pendingItems) > 0) {
                    $matchedName = array_shift($pendingItems); 
                    
                    $itemsFound[] = [
                        'name'   => strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $matchedName)),
                        'price'  => $cleanPrice,
                        'friend' => ''
                    ];
                }
            }
            // 5. SKENARIO B: Format Sebaris (Nama dan Harga gabung)
            elseif (preg_match('/^(.+?)\s+([IlO\d,\.\s]+)$/i', $line, $itemMatches)) {
                $rawPrice = end($itemMatches); 
                $cleanPrice = (int)preg_replace('/[^\d]/', '', str_ireplace(['l','O','I'], ['1','0','1'], $rawPrice));
                $itemName = trim($itemMatches[1]);

                if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && strlen($itemName) > 2 && !is_numeric(str_replace(' ', '', $itemName)) && strpos($line, ':') === false) {
                    // Gabungkan dengan quantity yang tersimpan di memori (jika ada)
                    if (!empty($lastQty)) {
                        $itemName = $lastQty . ' ' . $itemName;
                        $lastQty = "";
                    }
                    
                    $itemsFound[] = [
                        'name'   => strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $itemName)),
                        'price'  => $cleanPrice,
                        'friend' => ''
                    ];
                    $pendingItems = []; 
                }
            }
            // 6. SKENARIO C: Simpan calon nama menu ke antrean
            else {
                $isTimeOrDate = preg_match('/\d{2}:\d{2}/', $line) || preg_match('/\d{2}[\/\-]\d{2}[\/\-]\d{2,4}/', $line);
                
                if (!$isTimeOrDate && strlen($line) > 2 && !is_numeric(str_replace(' ', '', $line))) {
                    // Jika sebelumnya ada angka quantity yang menggantung, gabungkan sekarang!
                    if (!empty($lastQty)) {
                        $line = $lastQty . ' ' . $line;
                        $lastQty = ""; 
                    }
                    $pendingItems[] = $line; // Masukkan nama utuh ke antrean
                }
            }
        }

        // 6. Balikkan JSON sukses ke front-end
        return response()->json([
            'items'       => $itemsFound,
            'date'        => $dateFound ?? date('Y-m-d'),
            'description' => $descriptionFound
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
        ]);

        // 1. Buat Sesi Utamanya dulu
        $activity = auth()->user()->activities()->create([
            'title'        => $request->title,
            'location'     => $request->location,
            'event_date'   => $request->event_date,
            'status'       => 'active',
            'total_amount' => 0
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
        if ($request->has('items')) {
            $total = 0;
            foreach ($request->items as $item) {
                if (!empty($item['name']) && !empty($item['price'])) {
                    $activity->items()->create([
                        'name'        => strtoupper($item['name']),
                        'price'       => (int) $item['price'],
                        'friend_name' => $item['friend'] ?? null 
                    ]);
                    $total += (int) $item['price'];
                }
            }
            $activity->update(['total_amount' => $total]);
        }

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
        ]);

        $activity->update($request->all());

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