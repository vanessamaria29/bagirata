@extends('layouts.master')

@section('title', 'Buat Sesi Patungan - OCR')

@section('content')
<div class="max-w-3xl mx-auto py-10" x-data="activityForm">

    <div class="mb-10 flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all border border-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic uppercase">Sesi Baru (OCR)</h2>
    </div>

    <form action="{{ route('activities.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50 space-y-6">
            <h3 class="text-lg font-black text-gray-950 uppercase tracking-tight italic border-b border-gray-100 pb-3">1. Detail Acara</h3>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ditalangi Oleh</label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium cursor-not-allowed flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-slate-400 w-4 h-4 mr-2 inline-block shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>{{ auth()->user()->name }} (Anda)</span>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Nama Acara</label>
                <input type="text" name="title" required placeholder="Misal: Healing Bareng" 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Tanggal</label>
                <input type="date" name="event_date" required 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Masukkan ke Trip (Opsional)</label>
                <select name="trip_id" x-model="selectedTripId" @change="loadTripMembers()" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none cursor-pointer">
                    <option value="">-- Tanpa Trip --</option>
                    @foreach($trips as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Mata Uang Pengeluaran</label>
                <select name="currency" x-model="currencyCode" @change="updateExchangeRate()" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none cursor-pointer">
                    <option value="IDR">Rupiah (IDR)</option>
                    <option value="USD">US Dollar (USD)</option>
                    <option value="SGD">Singapore Dollar (SGD)</option>
                    <option value="JPY">Japanese Yen (JPY)</option>
                </select>
                <p x-show="currencyCode !== 'IDR'" class="text-[11px] text-blue-600 font-semibold mt-2 flex items-center gap-1 animate-pulse" x-cloak>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.82 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.496 1.508 1.333 1.508 2.316V18" /></svg> Otomatis dikonversi menggunakan kurs live API: <span class="font-black">1 <span x-text="currencyCode"></span> = Rp <span x-text="formatRate(exchangeRate)"></span></span>
                </p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50 space-y-6">
            <h3 class="text-lg font-black text-gray-950 uppercase tracking-tight italic border-b border-gray-100 pb-3">2. Anggota Patungan</h3>
            
            <div class="flex gap-3">
                <input type="text" x-model="newFriend" @keydown.enter.prevent="addFriend()" placeholder="Ketik nama teman (ex: Williams)" 
                    class="flex-1 px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
                <button type="button" @click="addFriend()" class="bg-gray-950 text-white px-6 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors">
                    + TAMBAH
                </button>
            </div>

            <div class="flex flex-wrap gap-4 pt-2 items-center">
                <template x-for="(friend, index) in friends" :key="index">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-75"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="flex items-center gap-2 pl-2 pr-3 py-2 bg-gray-50 hover:bg-blue-50/80 rounded-full border border-gray-100 transition-all shadow-sm relative group">
                        
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-black text-xs uppercase tracking-wider shadow-sm shadow-blue-100 shrink-0">
                            <span x-text="friend.charAt(0)"></span>
                        </div>
                        
                        <span class="font-black text-xs text-gray-900 uppercase tracking-tight max-w-[100px] truncate" x-text="friend"></span>
                        
                        <button type="button" @click="removeFriend(index)" 
                            class="w-4 h-4 rounded-full bg-gray-200 text-gray-500 hover:bg-red-500 hover:text-white flex items-center justify-center text-[10px] font-black transition-colors ml-1 active:scale-90">
                            ×
                        </button>
                    </div>
                </template>
                <p x-show="friends.length === 0" class="text-xs text-gray-400 italic font-medium py-2">Belum ada nama teman yang dimasukkan.</p>
            </div>
            
            <template x-for="(friend, index) in friends" :key="'input-'+index">
                <input type="hidden" name="friends[]" :value="friend">
            </template>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50 space-y-6">
            <h3 class="text-lg font-black text-gray-950 uppercase tracking-tight italic border-b border-gray-100 pb-3">3. Upload & Scan Struk Belanja</h3>
            
            <div class="border-4 border-dashed border-gray-200 rounded-[2rem] p-8 text-center hover:border-blue-500 transition-colors group cursor-pointer relative bg-gray-50/50" 
                 x-show="!isOcrProcessed && !isUploading">
                
                <input type="file" name="image" accept="image/*"
    @change="scanOcr($event)"
    class="absolute inset-0 opacity-0 cursor-pointer">
                
                <span class="group-hover:scale-120 transition-transform inline-block mb-2 text-gray-400 group-hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                </span>
                <p class="text-sm font-black text-gray-700 uppercase tracking-wide">Upload Struk Belanja</p>
                <p class="text-[10px] text-gray-400 font-bold mt-1">Pilih / Ambil Foto Struk </p>
            </div>

            <div class="text-center py-4" x-show="!isOcrProcessed && !isUploading">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] my-3">— ATAU —</p>
                <button type="button" @click="isOcrProcessed = true; ocrItems.push({ name: '', price: 0, friend: '', friends: [] })" 
                    class="px-6 py-4 bg-gray-950 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors shadow-lg active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1 inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg> Input Menu Secara Manual
                </button>
            </div>

            <div class="text-center py-12 bg-gray-50 rounded-[2rem] border border-gray-100" x-show="isUploading" x-cloak>
                <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest animate-pulse">Memilah menu struk...</p>
            </div>

                <div class="space-y-6" x-show="isOcrProcessed" x-cloak>
                    @if(session('error'))
                        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="flex justify-between items-center bg-green-50 border border-green-100 p-4 rounded-2xl">
                        <p class="text-xs font-bold text-green-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <span>Ekstraksi OCR Berhasil! Kamu bisa mengedit teks/harga jika ada kesalahan pembacaan.</span>
                        </p>
                        <button type="button" @click="isOcrProcessed = false; ocrItems = []" class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-red-500">Ulangi</button>
                    </div>
                    
                    <div class="flex flex-col items-center gap-3 bg-gray-50 border border-gray-100 p-4 rounded-3xl mt-4">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe Patungan Sesi Ini</span>
                        <div class="flex bg-gray-200 p-1 rounded-2xl w-fit">
                            <button type="button" @click="splitType = 'proportional'" :class="splitType === 'proportional' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest transition-all">Sesuai Pesanan</button>
                            <button type="button" @click="splitType = 'equal'" :class="splitType === 'equal' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-900'" class="px-6 py-2 rounded-xl font-black text-xs uppercase tracking-widest transition-all">Sama Rata Semua</button>
                        </div>
                        <input type="hidden" name="split_type" :value="splitType">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="pb-3 w-2/5">Hasil Deteksi Menu</th>
                                    <th class="pb-3 w-1/5" x-text="'Harga (' + currencyCode + ')'">Harga (Rp)</th>
                                    <th class="pb-3 w-1/3" x-show="splitType === 'proportional'">Alokasi Pemesan</th>
                                    <th class="pb-3 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold text-gray-900">
                                <template x-for="(item, index) in ocrItems" :key="index">
                                    <tr class="border-b border-gray-55 group">
                                        <td class="py-3 pr-2">
                                            <input type="text" x-model="item.name" 
                                                class="w-full bg-transparent border-b border-transparent focus:border-blue-500 font-black text-gray-900 uppercase focus:bg-gray-50 px-2 py-1 rounded-md outline-none">
                                        </td>
                                        <td class="py-3 pr-4">
                                            <input type="number" step="any" x-model="item.price" 
                                                class="w-full bg-transparent border-b border-transparent focus:border-blue-500 font-bold text-gray-950 italic focus:bg-gray-50 px-2 py-1 rounded-md outline-none text-left">
                                        </td>
                                        <td class="py-3 pr-2" x-show="splitType === 'proportional'">
                                            <div class="flex flex-wrap gap-2 max-w-xs">
                                                <template x-for="friend in friends" :key="friend">
                                                    <label class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-tight cursor-pointer border-2 transition-all active:scale-95 select-none"
                                                           :class="item.friends && item.friends.includes(friend) ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-200' : 'bg-gray-50 text-gray-500 border-gray-100 hover:bg-blue-50 hover:border-blue-100'">
                                                        <input type="checkbox" 
                                                               :value="friend" 
                                                               x-model="item.friends" 
                                                               @change="item.friend = item.friends.join(',')"
                                                               class="hidden">
                                                        <span x-text="friend"></span>
                                                    </label>
                                                </template>
                                                <p x-show="friends.length === 0" class="text-[9px] text-gray-400 italic">Tambahkan teman di atas</p>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-start pt-2">
                        <button type="button" @click="ocrItems.push({ name: '', price: 0, friend: '', friends: [] })" 
                            class="px-5 py-3 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg> Tambah Baris Menu
                        </button>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-3xl p-6 space-y-4 shadow-sm">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Biaya Tambahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 italic">Pajak (PPN)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400" x-text="$store.currency.symbol"></span>
                                    <input type="number" step="any" x-model="tax" name="tax" min="0" placeholder="0"
                                        class="w-full pl-8 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 italic">Service Charge</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400" x-text="$store.currency.symbol"></span>
                                    <input type="number" step="any" x-model="serviceCharge" name="service_charge" min="0" placeholder="0"
                                        class="w-full pl-8 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-3xl p-6 space-y-3 border border-gray-100">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Subtotal Menu</span>
                            <span class="font-bold text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format(getSubtotal())"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Pajak (PPN)</span>
                            <span class="font-bold text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format(parseInt(tax || 0))"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Service Charge</span>
                            <span class="font-bold text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format(parseInt(serviceCharge || 0))"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between items-center bg-blue-600 -mx-6 -mb-6 px-6 py-5 rounded-b-3xl text-white">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-widest opacity-80">Grand Total</span>
                                <span x-show="currencyCode !== 'IDR'" class="text-[10px] font-black uppercase tracking-wider text-blue-100 mt-0.5" x-cloak>
                                    ≈ Rp <span x-text="Math.round(getGrandTotal() * exchangeRate).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                            <span class="text-2xl font-black italic" x-text="currencyCode + ' ' + Math.round(getGrandTotal()).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- Live Breakdown Preview -->
                    <div class="bg-gray-50 border border-gray-100 rounded-3xl p-6 space-y-4 shadow-sm animate-fade-in" x-show="friends.length > 0" x-cloak>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Estimasi Pembagian Sementara</h4>
                        <div class="space-y-3">
                            <template x-for="member in getLiveBreakdown()" :key="member.name">
                                <div class="bg-white p-4 rounded-2xl border border-gray-100 flex justify-between items-center transition-all">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-black text-[10px] uppercase shadow-sm">
                                            <span x-text="member.name.charAt(0)"></span>
                                        </div>
                                        <span class="font-black text-xs text-gray-950 uppercase tracking-tight" x-text="member.name"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-black text-base text-blue-600 italic" x-text="currencyCode + ' ' + Math.round(member.total).toLocaleString('id-ID')"></span>
                                        <div x-show="currencyCode !== 'IDR'" class="text-[10px] text-gray-500 font-black tracking-tight mt-0.5" x-cloak>
                                            ≈ Rp <span x-text="Math.round(member.total * exchangeRate).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div class="text-[8px] text-gray-400 font-bold uppercase tracking-wider mt-1">
                                            Sub: <span x-text="Math.round(member.subtotal).toLocaleString('id-ID')"></span> | 
                                            Pajak: <span x-text="Math.round(member.tax).toLocaleString('id-ID')"></span> | 
                                            SC: <span x-text="Math.round(member.serviceCharge).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            
            <template x-for="(item, index) in ocrItems" :key="'submit-'+index">
                <div>
                    <input type="hidden" :name="'items['+index+'][name]'" :value="item.name">
                    <input type="hidden" :name="'items['+index+'][price]'" :value="item.price">
                    <input type="hidden" :name="'items['+index+'][friend]'" :value="item.friend">
                </div>
            </template>
        </div>

        <button type="submit" class="w-full py-6 bg-blue-600 text-white rounded-[2rem] font-black text-xl shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 uppercase tracking-widest italic flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.45c.019-.104.039-.208.06-.311m-2.7-2.699a14.92 14.92 0 015.841-2.58m-.119 8.54a6 6 0 01-7.38-5.84h4.8m2.58-5.84a14.92 14.92 0 00-2.58 5.84" /></svg>
            SELESAIKAN & BAGIRATA KAN
        </button>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('activityForm', () => ({
        tripsData: @json($trips),
        selectedTripId: '{{ request('trip_id', '') }}',
        friends: ['{{ auth()->user()->name }}'], 
        newFriend: '',
        isOcrProcessed: false,
        isUploading: false,
        ocrItems: [],
        tax: 0,
        serviceCharge: 0,
        splitType: 'proportional',
        currencyCode: 'IDR',
        exchangeRate: 1.0,
        rates: { IDR: 1.0, USD: 16400, SGD: 12100, JPY: 104 },
        formatRate(val) {
            return parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        updateExchangeRate() {
            this.exchangeRate = this.rates[this.currencyCode] || 1.0;
        },
        loadTripMembers() {
            if (!this.selectedTripId) return;
            let trip = this.tripsData.find(t => t.id == this.selectedTripId);
            if (trip && trip.participants) {
                let newFriends = trip.participants.map(p => p.name);
                newFriends.forEach(f => {
                    if (!this.friends.includes(f)) {
                        this.friends.push(f);
                    }
                });
            }
        },
        init() {
            this.loadTripMembers();
            fetch('/api/exchange-rates')
                .then(res => res.json())
                .then(data => {
                    if (data.rates) {
                        this.rates = data.rates;
                        this.updateExchangeRate();
                    }
                })
                .catch(err => console.log('Using default rates fallback'));
        },
        scanOcr(event) {
            if (event.target.files.length === 0) return;

            this.isUploading = true;
            this.isOcrProcessed = false; 
            this.ocrItems = []; 

            let formData = new FormData();
            formData.append('image', event.target.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route('ocr.scan') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => {
                        throw new Error(errData.error || 'Terjadi kesalahan pada server cloud.');
                    });
                }
                return res.json();
            })
            .then(resBody => {
                this.isUploading = false;
                
                if (resBody.items && resBody.items.length > 0) {
                    this.ocrItems = resBody.items.map(item => ({
                        name: item.name,
                        price: item.price,
                        friend: item.friend || '',
                        friends: item.friend ? item.friend.split(',').filter(Boolean) : []
                    }));
                    this.isOcrProcessed = true; 
                    
                    if (resBody.date) {
                        document.getElementsByName('event_date')[0].value = resBody.date;
                    }
                    if (resBody.description) {
                        document.getElementsByName('title')[0].value = 'PATUNGAN DI ' + resBody.description.toUpperCase();
                    }

                    if (resBody.tax) {
                        this.tax = resBody.tax;
                    }
                    if (resBody.service) {
                        this.serviceCharge = resBody.service;
                    }
                } else {
                    alert(resBody.error || 'Mesin OCR sukses membaca struk, namun format menu & harga tidak cocok.');
                }
            })
            .catch(err => {
                this.isUploading = false;
                alert('Gagal memproses koneksi: ' + err.message);
            });
        },
        addFriend() {
            if (this.newFriend.trim() !== '') {
                this.friends.push(this.newFriend.trim());
                this.newFriend = '';
            }
        },
        removeFriend(index) {
            let removedFriend = this.friends[index];
            this.friends.splice(index, 1);
            this.ocrItems.forEach(item => {
                if (item.friends) {
                    item.friends = item.friends.filter(f => f !== removedFriend);
                    item.friend = item.friends.join(',');
                } else if (item.friend === removedFriend) {
                    item.friend = '';
                }
            });
        },
        removeItem(index) {
            this.ocrItems.splice(index, 1);
        },
        getSubtotal() {
            return this.ocrItems.reduce((sum, item) => sum + parseFloat(item.price || 0), 0);
        },
        getGrandTotal() {
            return this.getSubtotal() + parseFloat(this.tax || 0) + parseFloat(this.serviceCharge || 0);
        },
        getLiveBreakdown() {
            if (this.friends.length === 0) return [];
            
            let breakdowns = {};
            this.friends.forEach(friend => {
                breakdowns[friend] = {
                    name: friend,
                    subtotal: 0,
                    tax: 0,
                    serviceCharge: 0,
                    total: 0
                };
            });

            if (this.splitType === 'equal') {
                let totalMembers = this.friends.length;
                let grandTotal = this.getGrandTotal();
                let perPerson = grandTotal / totalMembers;
                let sharedSubtotal = this.getSubtotal() / totalMembers;
                let sharedTax = parseFloat(this.tax || 0) / totalMembers;
                let sharedSc = parseFloat(this.serviceCharge || 0) / totalMembers;

                return this.friends.map(friend => ({
                    name: friend,
                    subtotal: sharedSubtotal,
                    tax: sharedTax,
                    serviceCharge: sharedSc,
                    total: perPerson
                }));
            }

            // proportional split
            this.ocrItems.forEach(item => {
                let checkedFriends = item.friends || [];
                if (checkedFriends.length > 0) {
                    let share = parseInt(item.price || 0) / checkedFriends.length;
                    checkedFriends.forEach(friend => {
                        if (breakdowns[friend]) {
                            breakdowns[friend].subtotal += share;
                        }
                    });
                }
            });

            let totalAssignedSubtotal = 0;
            this.friends.forEach(friend => {
                totalAssignedSubtotal += breakdowns[friend].subtotal;
            });

            this.friends.forEach(friend => {
                let b = breakdowns[friend];
                let proportion = totalAssignedSubtotal > 0 ? (b.subtotal / totalAssignedSubtotal) : 0;
                b.tax = proportion * parseFloat(this.tax || 0);
                b.serviceCharge = proportion * parseFloat(this.serviceCharge || 0);
                b.total = b.subtotal + b.tax + b.serviceCharge;
            });

            return this.friends.map(friend => breakdowns[friend]);
        }
    }));
});
</script>
@endpush
@endsection