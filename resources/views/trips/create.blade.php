@extends('layouts.master')

@section('title', 'Buat Trip Folder Baru')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <div class="mb-10 flex items-center gap-4">
        <a href="{{ route('trips.index') }}" class="p-4 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all active:scale-90 border border-gray-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic uppercase">Buat Trip Folder</h2>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-xl border border-gray-50 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-50 rounded-full opacity-50"></div>
        
        <form action="{{ route('trips.store') }}" method="POST" class="space-y-8 relative z-10">
            @csrf
            
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 italic">Nama Rangkaian Trip / Folder</label>
                <input type="text" name="name" required placeholder="Misal: Liburan Jepang 2026" 
                    class="w-full px-8 py-5 bg-gray-50 border-2 border-transparent focus:border-blue-100 focus:bg-white focus:ring-4 focus:ring-blue-50 rounded-2xl transition-all font-black text-lg text-gray-900 outline-none">
                @error('name') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 italic">Deskripsi / Keterangan (Opsional)</label>
                <textarea name="description" rows="4" placeholder="Misal: Kumpulan alokasi biaya ditalangi selama trip Tokyo - Kyoto"
                    class="w-full px-8 py-5 bg-gray-50 border-2 border-transparent focus:border-blue-100 focus:bg-white focus:ring-4 focus:ring-blue-50 rounded-2xl transition-all font-bold text-gray-900 outline-none"></textarea>
                @error('description') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-6 bg-blue-600 text-white rounded-2xl font-black text-xl shadow-2xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-0.5 transition-all active:scale-95 uppercase tracking-widest italic flex items-center justify-center gap-3">
                    🚀 Buat Trip Folder
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
