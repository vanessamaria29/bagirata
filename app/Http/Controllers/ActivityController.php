<?php

namespace App\Http\Controllers;

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
        // 1. Validasi file gambar (Maksimal 4MB sesuai aturan OCR.space)
        $request->validate([
            'image' => 'required|image|max:4096', 
        ]);

        try {
            // 2. Ambil API Key dari .env (jika kosong, pakai backup key gratisan ini)
           $apiKey = env('OCR_SPACE_API_KEY');
            $file = $request->file('image');
            $fileName = $file->getClientOriginalName();
            
            // 3. Ambil isi binari gambar agar tidak stuck loading di Windows
            $fileContents = file_get_contents($file->getRealPath());

            // 4. Proses kirim data ke server OCR.space Cloud
            $response = Http::asMultipart()
                ->timeout(40)
                ->connectTimeout(20)
                ->withoutVerifying()
                ->attach('file', $fileContents, $fileName)
                ->post('https://api.ocr.space/parse/image', [
                    'apikey'            => $apiKey,
                    'language'          => 'eng',
                    'isTable'           => 'true',
                    'OCREngine'         => '2', 
                    'scale'             => 'true',
                    'detectOrientation' => 'true',
                ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Server OCR Cloud sedang sibuk, coba lagi nanti.'], 500);
            }

            $result = $response->json();
            
            // Cek jika token salah, limit, atau respons teks mentahnya kosong
            if (!isset($result['ParsedResults'][0]['ParsedText']) || empty($result['ParsedResults'][0]['ParsedText'])) {
                $errorMessage = $result['ErrorMessage'][0] ?? 'Gambar kurang jelas atau kuota API akun ini habis.';
                return response()->json(['error' => $errorMessage], 422);
            }

            $fullText = $result['ParsedResults'][0]['ParsedText'];
            
            $amountFound = 0;
            $descriptionFound = null;
            $dateFound = null;
            $itemsFound = []; 

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
                'Thank', 'Terima', 'Kasih', 'Welcome', 'Selamat', 'Datang', 'Operator', 'Kasir', 'Cashier'
            ];

            foreach ($lines as $line) {
                $line = trim($line);
                
                if (preg_match('/(Total|Subtotal|Amount|Tagihan).*?([\d\.,]+)$/i', $line, $matches)) {
                    $cleanNum = (int)preg_replace('/[^\d]/', '', $matches[2]);
                    if ($cleanNum > $amountFound) $amountFound = $cleanNum; 
                }

                $isSystemLine = false;
                foreach ($ignoredWords as $word) {
                    if (stripos($line, $word) !== false) {
                        $isSystemLine = true; break;
                    }
                }

                if (!$isSystemLine && preg_match('/^(\d+\s+)?(.+?)\s+([\d,\.]+)$/', $line, $itemMatches)) {
                    $rawPrice = end($itemMatches); 
                    $cleanPrice = (int)preg_replace('/[^\d]/', '', $rawPrice);
                    $itemName = trim($itemMatches[2]);

                    if ($cleanPrice >= 500 && $cleanPrice <= 10000000 && strlen($itemName) > 2 && !is_numeric(str_replace(' ', '', $itemName)) && strpos($line, ':') === false) {
                        $itemName = preg_replace('/^[^a-zA-Z0-9]+/', '', $itemName);

                        $itemsFound[] = [
                            'name'   => strtoupper($itemName),
                            'price'  => $cleanPrice,
                            'friend' => ''
                        ];
                    }
                }
            }

            // Balikkan JSON sukses ke front-end
            return response()->json([
                'items'       => $itemsFound,
                'date'        => $dateFound ?? date('Y-m-d'),
                'description' => $descriptionFound
            ]);

            $response = Http::withoutVerifying()->post('https://api.ocr.space/parse/image', [
                        // parameter api...
                    ]);

                    if ($response->failed()) {
                        throw new \Exception("Server OCR sedang sibuk, coba lagi nanti ya!");
                    }

                    // ... proses hasil scan ...
                    
                } catch (\Exception $e) {
                    // Daripada error, kita kembalikan ke halaman sebelumnya dengan pesan
                    return back()->with('error', 'Maaf, fitur scan sedang sibuk (Server OCR down). Silakan coba 1 menit lagi.');
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