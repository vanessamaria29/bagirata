@extends('layouts.master')

@section('title', 'Dashboard Utama')

@section('content')
<style>
    .create-session-card {
        background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.3);
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    .create-session-card::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        blur: 20px;
    }
    .create-session-inner {
        position: relative;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }
    .create-header h2 {
        font-size: 28px;
        font-weight: 900;
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
    }
    .create-header p {
        color: #E0E7FF;
        margin: 0;
        font-weight: 500;
        opacity: 0.9;
        line-height: 1.5;
    }
    .create-btn {
        padding: 20px;
        background-color: #ffffffdc;
        color: #4F46E5;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-text {
        letter-spacing: -0.2px;
    }

    .create-btn:hover {
        background-color: #F8FAFC;
        color: #3730A3;
        transform: translateY(-3px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    @media (max-width: 640px) {
        .create-session-inner {
            flex-direction: column;
            align-items: flex-start;
        }
        .create-btn {
            width: 100%;
            margin-top: 10px;
        }
    }
</style>

<div class="py-4 space-y-10" x-data="{ filter: 'semua' }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-[2.5rem] shadow-xl border border-blue-50 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
            <p class="relative z-10 text-gray-500 font-black italic text-xs uppercase tracking-widest">Total Keluar</p>
            <h3 class="relative z-10 text-3xl font-black text-gray-900 mt-2" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $total_spent }})">Rp {{ number_format($total_spent, 0, ',', '.') }}</h3>
        </div>

        <div class="p-8 bg-blue-600 rounded-[2.5rem] shadow-2xl shadow-blue-200 relative overflow-hidden">
            <p class="text-blue-100 font-black italic text-xs uppercase tracking-widest">Uang Nyangkut</p>
            <h3 class="text-3xl font-black text-white mt-2" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $stuck_money }})">Rp {{ number_format($stuck_money, 0, ',', '.') }}</h3>
        </div>

        <div class="p-8 bg-white rounded-[2.5rem] shadow-xl border border-gray-100 relative">
            <p class="text-gray-500 font-black italic text-xs uppercase tracking-widest">Sesi Aktif</p>
            <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $active_sessions }} Sesi</h3>
        </div>
    </div>

    <div class="create-session-card">
        <div class="create-session-inner">
            <div class="create-header">
                <h2>Patungan Apa Hari Ini?</h2>
                <p>Buat sesi baru, foto struknya, dan biarkan sistem yang membagi tagihannya.</p>
            </div>
            
            <a href="{{ route('activities.create') }}" class="create-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="80">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="btn-text">Buat Sesi & Scan</span>
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none">Sesi Terakhir</h2>
        
            <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-2xl border border-gray-200">
                <button @click="filter = 'semua'" 
                    :class="filter === 'semua' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                    Semua
                </button>
                <button @click="filter = 'active'" 
                    :class="filter === 'active' ? 'bg-white text-green-500 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                    Aktif
                </button>
                <button @click="filter = 'lunas'" 
                    :class="filter === 'lunas' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                    Lunas
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($activities as $activity)
                <div x-show="filter === 'semua' || filter === '{{ $activity->status }}'"
                     class="group relative p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all flex justify-between items-center">
                    
                    <a href="{{ route('activities.show', $activity->id) }}" class="absolute inset-0 z-10"></a>

                    <div class="flex items-center gap-4 relative z-20">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-xl text-gray-900 group-hover:text-blue-600 transition-colors">{{ $activity->title }}</h4>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                                {{ \Carbon\Carbon::parse($activity->event_date)->format('d M Y') }} • {{ $activity->location ?? 'Lokasi' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="relative z-20 text-gray-300 group-hover:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            @empty
                <div class="py-20 bg-gray-50 rounded-[3rem] border-4 border-dashed border-gray-200 text-center">
                    <p class="text-gray-400 font-black italic uppercase tracking-widest">Belum ada sesi kegiatan</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection