@extends('layouts.master')

@section('title', 'Daftar Trip Folder')

@section('content')
<div class="py-4 space-y-10" x-data="{ filter: 'semua' }">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic leading-none uppercase">Trip Folders</h2>
        </div>
        <a href="{{ route('trips.create') }}" class="bg-blue-600 text-white px-6 py-4 rounded-2xl font-black text-xs shadow-lg shadow-blue-100 tracking-wider hover:bg-blue-700 transition-all min-h-[48px] flex items-center justify-center">+ TRIP BARU</a>
    </div>

    <!-- Bento Grid Trip Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-tr from-blue-600 to-indigo-950 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group flex flex-col justify-between h-full min-h-[140px]">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl transition-transform group-hover:scale-125 duration-700"></div>
            <div>
                <p class="relative z-10 text-[10px] font-black uppercase tracking-[0.2em] text-blue-100 italic">Total Trip</p>
                <h3 class="relative z-10 text-3xl font-black italic mt-4 tracking-tighter">{{ $trips->count() }}</h3>
            </div>
            <p class="relative z-10 text-[11px] text-blue-200 mt-4 font-semibold opacity-85">Kelola seluruh rangkaian liburan dalam satu wadah bersih.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[140px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Trip Aktif</p>
                <h3 class="text-3xl font-black text-gray-900 mt-4 tracking-tighter">{{ $trips->where('status', 'active')->count() }}</h3>
            </div>
            <p class="text-[11px] text-orange-500 font-bold mt-4 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                Rangkaian trip sedang berlangsung
            </p>
        </div>
    </div>

    <!-- Trips Grid -->
    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-2xl font-black text-gray-950 tracking-tighter italic leading-none uppercase">Daftar Folder</h2>
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
                <button @click="filter = 'settled'" 
                    :class="filter === 'settled' ? 'bg-white text-green-500 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                    Selesai
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($trips as $trip)
                <div x-show="filter === 'semua' || (filter === 'active' && '{{ $trip->status }}' === 'active') || (filter === 'settled' && '{{ $trip->status }}' === 'settled')"
                     class="group relative p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between min-h-[180px]">
                    
                    <a href="{{ route('trips.show', $trip->id) }}" class="absolute inset-0 z-10"></a>

                    <div class="relative z-20 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black tracking-widest uppercase text-blue-600">📂 Trip Folder</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border
                                  {{ $trip->status === 'settled' ? 'bg-emerald-50 text-emerald-700 border-emerald-100/60' : 'bg-amber-50 text-amber-700 border-amber-100/60' }}">
                                {{ $trip->status === 'settled' ? 'Selesai' : 'Aktif' }}
                            </span>
                        </div>
                        <h4 class="font-black text-2xl text-gray-900 group-hover:text-blue-600 transition-colors duration-300 uppercase tracking-tight">{{ $trip->name }}</h4>
                        <p class="text-xs text-gray-400 font-semibold line-clamp-2">
                            {{ $trip->description ?? 'Tidak ada deskripsi.' }}
                        </p>
                    </div>

                    <div class="relative z-20 pt-4 flex items-center justify-between border-t border-gray-50 mt-4">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                            {{ $trip->activities->count() }} Sesi Pengeluaran
                        </span>
                        <div class="text-gray-300 group-hover:text-blue-600 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 bg-slate-50 rounded-3xl border-4 border-dashed border-slate-200 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-400 font-black italic uppercase tracking-widest">Belum ada folder Trip</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
