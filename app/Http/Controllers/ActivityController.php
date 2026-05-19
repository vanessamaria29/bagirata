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
            // 5. MULAI PARSING TEKS (Sistem Antrean / FIFO)
            // ==========================================
            $amountFound = 0;
            $descriptionFound = null;
            $dateFound = null;
            $itemsFound = []; 
            $pendingItems = []; // <-- Ganti string jadi array antrean
            $isWaitingForTotal = false; // <-- Penanda jika harga total beda baris

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
                        'Disc', 'Diskon', 'Tanggal', 'Date', 'Waktu', 'Time', 'Jam', 'Payment'
                ];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // 1. CARI TOTAL / SUBTOTAL (Mengatasi Format Beda Baris)
                if (preg_match('/(Total|Subtotal|Amount|Tagihan|Pay|Grand)/i', $line)) {
                    // Jika harga ada di baris yang sama
                    if (preg_match('/([\d\.,]+)$/', $line, $matches)) {
                        $cleanNum = (int)preg_replace('/[^\d]/', '', $matches[1]);
                        if ($cleanNum > $amountFound) $amountFound = $cleanNum;
                    } else {
                        // Jika harganya ada di baris berikutnya
                        $isWaitingForTotal = true; 
                    }
                    $pendingItems = []; // Bersihkan antrean agar aman
                    continue;
                }

                // Jika baris ini adalah harga dari kata "Total" di baris sebelumnya
                if ($isWaitingForTotal && preg_match('/^[\d\.,]+$/', $line)) {
                    $cleanNum = (int)preg_replace('/[^\d]/', '', $line);
                    if ($cleanNum > $amountFound) $amountFound = $cleanNum;
                    $isWaitingForTotal = false;
                    continue;
                }

                // 2. FILTER KATA ABAIKAN (Header/Footer Kasir)
                $isSystemLine = false;
                foreach ($ignoredWords as $word) {
                    if (stripos($line, $word) !== false) {
                        $isSystemLine = true; break;
                    }
                }

                if ($isSystemLine) {
                    $pendingItems = []; // Buang semua antrean salah sasaran
                    continue;
                }

                // 3. SKENARIO A: Format Angka Saja (Harga)
                if (preg_match('/^[\d\.,]+$/', $line)) {
                    $cleanPrice = (int)preg_replace('/[^\d]/', '', $line);

                    // Jika harga masuk akal dan ada menu yang ngantre
                    if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && count($pendingItems) > 0) {
                        $matchedName = array_shift($pendingItems); // Ambil (pop) dari paling depan antrean
                        
                        $itemsFound[] = [
                            'name'   => strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $matchedName)),
                            'price'  => $cleanPrice,
                            'friend' => ''
                        ];
                    }
                }
                // 4. SKENARIO B: Format Sebaris (Nama dan Harga gabung)
                elseif (preg_match('/^(\d+[\s\x]*)?(.+?)\s+([\d,\.]+)$/', $line, $itemMatches)) {
                    $rawPrice = end($itemMatches); 
                    $cleanPrice = (int)preg_replace('/[^\d]/', '', $rawPrice);
                    $itemName = trim($itemMatches[2]);

                    if ($cleanPrice >= 500 && $cleanPrice <= 5000000 && strlen($itemName) > 2 && !is_numeric(str_replace(' ', '', $itemName)) && strpos($line, ':') === false) {
                        $itemsFound[] = [
                            'name'   => strtoupper(preg_replace('/^[^a-zA-Z0-9]+/', '', $itemName)),
                            'price'  => $cleanPrice,
                            'friend' => ''
                        ];
                        $pendingItems = []; // Reset antrean
                    }
                }
                // 5. SKENARIO C: Simpan calon nama menu ke antrean
                else {
                    // Cegah baris yang mengandung format jam (12:34) atau format tanggal masuk ke antrean
                    $isTimeOrDate = preg_match('/\d{2}:\d{2}/', $line) || preg_match('/\d{2}[\/\-]\d{2}[\/\-]\d{2,4}/', $line);
                    
                    if (!$isTimeOrDate && strlen($line) > 2 && !is_numeric(str_replace(' ', '', $line))) {
                        $pendingItems[] = $line; // Push ke antrean
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