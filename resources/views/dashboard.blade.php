@extends('layouts.master')

@section('title', 'Dashboard Utama')

@section('content')
<div class="py-4 space-y-10" x-data="{ filter: 'semua' }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-[2.5rem] shadow-xl border border-blue-50 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
            <p class="relative z-10 text-gray-500 font-black italic text-xs uppercase tracking-widest">Total Keluar</p>
            <h3 class="relative z-10 text-3xl font-black text-gray-900 mt-2">Rp {{ number_format($total_spent, 0, ',', '.') }}</h3>
        </div>

        <div class="p-8 bg-blue-600 rounded-[2.5rem] shadow-2xl shadow-blue-200 relative overflow-hidden">
            <p class="text-blue-100 font-black italic text-xs uppercase tracking-widest">Uang Nyangkut</p>
            <h3 class="text-3xl font-black text-white mt-2">Rp {{ number_format($stuck_money, 0, ',', '.') }}</h3>
        </div>

        <div class="p-8 bg-white rounded-[2.5rem] shadow-xl border border-gray-100 relative">
            <p class="text-gray-500 font-black italic text-xs uppercase tracking-widest">Sesi Aktif</p>
            <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $active_sessions }} Sesi</h3>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all group">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                </svg>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Scan Struk</span>
        </a>

        <a href="{{ route('activities.create') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all group">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-indigo-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H9" />
                </svg>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Buat Sesi</span>
        </a>

        <a href="{{ route('friends.index') }}" class="flex flex-col items-center justify-center p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all group">
            <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-purple-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Teman</span>
        </a>

        <button @click="filter = 'lunas'" class="flex flex-col items-center justify-center p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all group">
            <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-green-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Lunas</span>
        </button>
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
        
        <
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