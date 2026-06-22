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
                <h3 class="relative z-10 text-3xl font-black italic mt-4 tracking-tighter" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $stuck_money }})">Rp {{ number_format($stuck_money, 0, ',', '.') }}</h3>
            </div>
            <p class="relative z-10 text-[11px] text-blue-200 mt-4 font-semibold opacity-85">Segera tagih teman-temanmu sebelum mereka lupa!</p>
        </div>

        <!-- Kolom 2: Total Pengeluaran -->
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Total Pengeluaran</p>
                <h3 class="text-3xl font-black text-gray-900 mt-4 tracking-tighter" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $total_spent }})">Rp {{ number_format($total_spent, 0, ',', '.') }}</h3>
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

    <!-- Action Banner (Patungan Apa Hari Ini?) -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6 group">
        <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>
        <div class="space-y-2 relative z-10">
            <h2 class="text-2xl font-black tracking-tight italic uppercase">Patungan Apa Hari Ini?</h2>
            <p class="text-xs text-blue-100 font-medium opacity-90 max-w-xl">Buat sesi baru secara manual atau scan struk belanjaan menggunakan sistem cerdas BagiRata OCR.</p>
        </div>
        <a href="{{ route('activities.create') }}" class="relative z-10 px-8 py-4 bg-white text-blue-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 hover:scale-105 active:scale-95 transition-all shadow-lg flex items-center gap-2 whitespace-nowrap min-h-[48px] justify-center">
            📸 Scan / Buat Sesi
        </a>
    </div>

    <!-- Sesi Terakhir Section -->
    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none uppercase">Sesi Terakhir</h2>
        
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl border border-slate-200">
                <button @click="filter = 'semua'" 
                    :class="filter === 'semua' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                    Semua
                </button>
                <button @click="filter = 'active'" 
                    :class="filter === 'active' ? 'bg-white text-orange-500 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                    Aktif
                </button>
                <button @click="filter = 'lunas'" 
                    :class="filter === 'lunas' ? 'bg-white text-green-500 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                    Lunas
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($activities as $activity)
                <div x-show="filter === 'semua' || (filter === 'active' && '{{ $activity->status }}' === 'active') || (filter === 'lunas' && '{{ $activity->status }}' === 'settled')"
                     class="group relative p-6 bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex justify-between items-center">
                    
                    <a href="{{ route('activities.show', $activity->id) }}" class="absolute inset-0 z-10"></a>

                    <div class="flex items-center gap-4 relative z-20">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-50 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-xl text-gray-900 group-hover:text-blue-600 transition-colors duration-300 uppercase tracking-tight">{{ $activity->title }}</h4>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                                {{ \Carbon\Carbon::parse($activity->event_date)->format('d M Y') }} • {{ $activity->location ?? 'Lokasi' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 relative z-20 text-right">
                        <div class="flex flex-col items-end">
                            <span class="font-black text-xl text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $activity->total_amount }})">
                                Rp {{ number_format($activity->total_amount, 0, ',', '.') }}
                            </span>
                            <span class="mt-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest
                                  {{ $activity->status === 'settled' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $activity->status === 'settled' ? 'LUNAS' : 'AKTIF' }}
                            </span>
                        </div>
                        <div class="text-gray-300 group-hover:text-blue-600 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-20 bg-slate-50 rounded-[2.5rem] border-4 border-dashed border-slate-200 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                        </svg>
                    </div>
                    <p class="text-gray-400 font-black italic uppercase tracking-widest">Belum ada sesi kegiatan</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection