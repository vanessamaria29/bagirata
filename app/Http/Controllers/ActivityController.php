<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Menampilkan Dashboard (Summary) DAN Daftar Semua Tagihan (Index)
     * Menggabungkan PBI 09 & PBI 02
     */
    public function index()
    {
        // Ambil data milik user yang login
        $activities = auth()->user()->activities()->latest()->get();

        // JIKA URL-nya adalah /activities (Menu Bills), tampilkan view index lengkap
        if (request()->routeIs('activities.index')) {
            return view('activities.index', compact('activities'));
        }

        // JIKA di Dashboard, hitung summary-nya
        $total_spent = $activities->sum('total_amount');
        $active_sessions = $activities->where('status', 'active')->count();
        $stuck_money = 0; // Logic Settlement Tracker (PBI 12)

        return view('dashboard', compact('activities', 'total_spent', 'active_sessions', 'stuck_money'));
    }

    /**
     * Form Tambah Sesi (PBI 02)
     */
    public function create()
    {
        return view('activities.create');
    }

    /**
     * Simpan Sesi Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
        ]);

        auth()->user()->activities()->create([
            'title' => $request->title,
            'location' => $request->location,
            'event_date' => $request->event_date,
            'status' => 'active',
            'total_amount' => 0,
        ]);

        return redirect()->route('dashboard')->with('success', 'Sesi berhasil dibuat!');
    }
/**
 * Menampilkan rincian sesi kegiatan (Detail Transaksi)
 */
public function show(Activity $activity)
{
    // Kamu bisa menambahkan logic untuk mengambil data teman/anggota di sini nanti
    return view('activities.show', compact('activity'));
}
    /**
     * Form Edit Sesi (PBI 02)
     */
    public function edit(Activity $activity)
    {
        return view('activities.edit', compact('activity'));
    }

    public function update(Request $request, Activity $activity)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'event_date' => 'required|date',
    ]);

    $activity->update($request->all());

    // Pesan ini yang bakal ditangkep sama Alpine.js di atas
    return redirect()->route('dashboard')->with('success', 'Sesi Berhasil Diupdate');
}

public function destroy(Activity $activity)
{
    $activity->delete();
    return redirect()->route('dashboard')->with('success', 'Sesi Berhasil Dihapus');
}
}