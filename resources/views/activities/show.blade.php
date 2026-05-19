@extends('layouts.master')

@section('title', 'Detail Sesi')

@section('content')
<div class="max-w-4xl mx-auto py-6 space-y-8" x-data="{ openDelete: false }">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all active:scale-90">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none">{{ $activity->title }}</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">
                    {{ $activity->location ?? 'Lokasi belum diset' }}
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('activities.edit', $activity->id) }}" class="bg-gray-100 text-gray-600 p-3 rounded-2xl hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </a>

            <button @click="openDelete = true" class="bg-red-50 text-red-500 p-3 rounded-2xl hover:bg-red-500 hover:text-white transition-all active:scale-95 shadow-sm shadow-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="openDelete" class="fixed inset-0 z-[150] flex items-center justify-center p-6">
            <div @click="openDelete = false" x-show="openDelete" x-transition.opacity class="fixed inset-0 bg-gray-950/60 backdrop-blur-md"></div>

            <div x-show="openDelete" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="relative bg-white rounded-[3rem] p-10 max-w-md w-full shadow-2xl border border-gray-100 text-center">
                
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-gray-900 italic tracking-tighter mb-2 uppercase">Hapus Sesi?</h3>
                <p class="text-gray-500 font-medium mb-8 text-sm">Semua data tagihan bakal hilang permanen, Van.</p>

                <div class="flex gap-4">
                    <button @click="openDelete = false" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-[10px]">Batal</button>
                    <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-4 bg-red-500 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-xl shadow-red-200">Ya, Hapus!</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-8 bg-blue-600 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">Total Tagihan Sesi</p>
            <h3 class="text-4xl font-black italic mt-2">Rp {{ number_format($activity->total_amount, 0, ',', '.') }}</h3>
        </div>

        <div class="p-8 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm flex flex-col justify-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status Sesi</p>
            <div class="flex items-center gap-2 mt-2">
                <span class="w-3 h-3 rounded-full {{ $activity->status == 'active' ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }}"></span>
                <span class="font-black text-xl italic uppercase tracking-tighter text-gray-900">{{ $activity->status }}</span>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 border-4 border-dashed border-gray-200 rounded-[3rem] py-24 text-center group hover:border-blue-100 transition-colors">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 group-hover:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <p class="text-gray-400 font-black italic uppercase tracking-widest px-6">Daftar Anggota & Rincian Item Segera Hadir</p>
        <p class="text-gray-300 text-[10px] font-bold mt-2 uppercase tracking-tighter">Fitur PBI 03 & PBI 11</p>
        
        <button class="mt-8 bg-gray-950 text-white px-10 py-4 rounded-[1.5rem] font-black text-xs hover:bg-blue-600 transition-all shadow-xl shadow-gray-200 active:scale-95">
            + TAMBAH ITEM TAGIHAN
        </button>
    </div>
</div>
@endsection