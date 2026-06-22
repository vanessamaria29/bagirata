@extends('layouts.master')

@section('title', 'Dashboard Utama')

@section('content')
<div class="py-4 space-y-10" x-data="{ filter: 'semua' }">
    <!-- Bento Grid Dashboard Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Kolom 1: Uang Nyangkut (Belum Bayar) -->
        <div class="bg-gradient-to-tr from-blue-600 to-indigo-950 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group flex flex-col justify-between h-full min-h-[160px]">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl transition-transform group-hover:scale-125 duration-700"></div>
            <div>
                <p class="relative z-10 text-[10px] font-black uppercase tracking-[0.2em] text-blue-100 italic">Uang Nyangkut (Belum Bayar)</p>
                <h3 class="relative z-10 text-3xl font-black italic mt-4 tracking-tighter">Rp {{ number_format($stuck_money, 0, ',', '.') }}</h3>
            </div>
            <p class="relative z-10 text-[11px] text-blue-200 mt-4 font-semibold opacity-85">Segera tagih teman-temanmu sebelum mereka lupa!</p>
        </div>

        <!-- Kolom 2: Total Pengeluaran -->
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Total Pengeluaran</p>
                <h3 class="text-3xl font-black text-gray-900 mt-4 tracking-tighter">Rp {{ number_format($total_spent, 0, ',', '.') }}</h3>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold mt-4">Akumulasi seluruh sesi</p>
        </div>

        <!-- Kolom 3: Sesi Aktif -->
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Sesi Aktif</p>
                <h3 class="text-4xl font-black text-gray-900 mt-4 tracking-tighter">{{ $active_sessions }}</h3>
            </div>
            <p class="text-[11px] text-orange-500 font-bold mt-4 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                Sesi sedang berjalan
            </p>
        </div>
    </div>

    <!-- Bento Selection Grid (Menu Navigasi) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kartu 1: Sesi Instan -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-800 rounded-[2rem] p-8 text-white shadow-xl relative overflow-hidden flex flex-col justify-between h-full min-h-[220px] group">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl transition-transform duration-700 group-hover:scale-150"></div>
            <div class="space-y-2 relative z-10 mb-8">
                <h2 class="text-2xl font-black tracking-tight italic uppercase">Sesi Instan (Quick Bill)</h2>
                <p class="text-xs text-blue-100 font-medium opacity-90 leading-relaxed">Scan struk makanan atau buat tagihan tunggal yang langsung selesai sekali bayar.</p>
            </div>
            <a href="{{ route('activities.create') }}" class="relative z-10 w-full py-4 bg-white text-blue-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 hover:-translate-y-1 active:scale-95 transition-all shadow-lg flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg> SCAN STRUK / BUAT SESI
            </a>
        </div>

        <!-- Kartu 2: Folder Liburan -->
        <div class="bg-white border border-gray-100 rounded-[2rem] p-8 text-gray-900 shadow-sm relative overflow-hidden flex flex-col justify-between h-full min-h-[220px] group hover:shadow-md transition-shadow duration-300">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-50/50 rounded-full blur-2xl transition-transform duration-700 group-hover:scale-150"></div>
            <div class="space-y-2 relative z-10 mb-8">
                <h2 class="text-2xl font-black tracking-tight italic uppercase text-blue-950">Folder Liburan (Trip Groups)</h2>
                <p class="text-xs text-gray-400 font-bold leading-relaxed">Kumpulkan banyak struk dalam satu wadah trip (misal: Liburan Jepang) untuk rekap final di akhir acara.</p>
            </div>
            <a href="{{ route('trips.index') }}" class="relative z-10 w-full py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 hover:-translate-y-1 active:scale-95 transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg> KELOLA / BUAT TRIP
            </a>
        </div>
    </div>

    <!-- Unified Feed Section (Trips & Standalone Activities) -->
    <div class="space-y-6 mt-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between px-2 gap-4">
            <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none uppercase flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8 mr-3 text-blue-600"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg> Sesi & Trip Kamu</h2>
        
            <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-full border border-slate-100 self-start md:self-auto">
                <button @click="filter = 'semua'" 
                    :class="filter === 'semua' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:bg-slate-200/50'"
                    class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300">
                    Semua
                </button>
                <button @click="filter = 'active'" 
                    :class="filter === 'active' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:bg-slate-200/50'"
                    class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300">
                    Aktif
                </button>
                <button @click="filter = 'lunas'" 
                    :class="filter === 'lunas' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:bg-slate-200/50'"
                    class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300">
                    Lunas
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($feed as $item)
                @php
                    $isTrip = isset($item->name);
                    $status = $item->status;
                    $title = $isTrip ? $item->name : $item->title;
                    $dateStr = $isTrip ? $item->created_at->format('d M Y') : \Carbon\Carbon::parse($item->event_date)->format('d M Y');
                    $link = $isTrip ? route('trips.show', $item->id) : route('activities.show', $item->id);
                    $amountVal = $isTrip ? null : $item->total_amount;
                @endphp

                <div x-show="filter === 'semua' || (filter === 'active' && '{{ $status }}' === 'active') || (filter === 'lunas' && '{{ $status }}' === 'settled')"
                     class="group relative p-5 bg-white rounded-3xl border border-slate-100 hover:shadow-lg transition-all duration-300 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    
                    <a href="{{ $link }}" class="absolute inset-0 z-10"></a>

                    <div class="flex items-center gap-5 relative z-20 w-full md:w-auto">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white text-blue-600 transition-colors duration-300 shrink-0">
                            @if($isTrip)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-lg text-gray-900 group-hover:text-blue-600 transition-colors duration-300 uppercase tracking-tight">{{ $title }}</h4>
                            <div class="flex flex-wrap items-center gap-3 mt-1.5">
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">
                                    {{ $dateStr }}
                                </p>
                                @if($isTrip)
                                    <span class="bg-indigo-50 text-indigo-600 rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest border border-indigo-100/50 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg> FOLDER TRIP ({{ $item->participants->count() }} Anggota)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto relative z-20 gap-2">
                        @if(!$isTrip)
                            <span class="font-black text-xl text-gray-900 group-hover:text-blue-600 transition-colors italic tracking-tight">
                                Rp {{ number_format($amountVal, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="font-black text-sm text-gray-400 group-hover:text-blue-600 transition-colors uppercase tracking-widest flex items-center gap-1">
                                Lihat Rincian <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </span>
                        @endif
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                              {{ $status === 'settled' ? 'bg-green-50 text-green-600 border-green-100' : ($isTrip ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-red-50 text-red-500 border-red-100') }}">
                            {{ $status === 'settled' ? 'LUNAS' : ($isTrip ? 'AKTIF' : 'BELUM BAYAR') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-16 bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200 text-center">
                    <p class="text-gray-400 font-black italic uppercase tracking-widest text-xs">Belum ada riwayat sesi atau trip</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection