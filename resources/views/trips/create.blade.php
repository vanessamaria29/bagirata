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

    <div class="bg-white p-10 rounded-3xl shadow-xl border border-gray-50 relative overflow-hidden"
         x-data="{
            participants: [],
            newParticipant: '',
            addParticipant() {
                if (this.newParticipant.trim() !== '') {
                    this.participants.push(this.newParticipant.trim());
                    this.newParticipant = '';
                }
            },
            removeParticipant(index) {
                this.participants.splice(index, 1);
            }
         }">
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
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 italic">Anggota Trip (Otomatis Masuk Tiap Sesi)</label>
                <div class="flex gap-3">
                    <input type="text" x-model="newParticipant" @keydown.enter.prevent="addParticipant()" placeholder="Ketik nama (ex: Williams)" 
                        class="flex-1 px-8 py-5 bg-gray-50 border-2 border-transparent focus:border-blue-100 focus:bg-white focus:ring-4 focus:ring-blue-50 rounded-2xl transition-all font-black text-lg text-gray-900 outline-none">
                    <button type="button" @click="addParticipant()" class="bg-gray-950 text-white px-8 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors shadow-lg active:scale-95">
                        + TAMBAH
                    </button>
                </div>

                <div class="flex flex-wrap gap-4 pt-4 items-center">
                    <template x-for="(participant, index) in participants" :key="index">
                        <div x-transition class="flex items-center gap-2 pl-3 pr-4 py-3 bg-gray-50 hover:bg-blue-50/80 rounded-full border border-gray-100 transition-all shadow-sm relative group">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-black text-sm uppercase tracking-wider shadow-sm shadow-blue-100 shrink-0">
                                <span x-text="participant.charAt(0)"></span>
                            </div>
                            <span class="font-black text-sm text-gray-900 uppercase tracking-tight" x-text="participant"></span>
                            <button type="button" @click="removeParticipant(index)" 
                                class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 hover:bg-red-500 hover:text-white flex items-center justify-center text-xs font-black transition-colors ml-2 active:scale-90">
                                ×
                            </button>
                        </div>
                    </template>
                    <p x-show="participants.length === 0" class="text-sm text-gray-400 italic font-medium py-2">Belum ada anggota trip. Anda bisa menambahkan nama nanti.</p>
                </div>
                
                <template x-for="(participant, index) in participants" :key="'input-'+index">
                    <input type="hidden" name="participants[]" :value="participant">
                </template>
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
