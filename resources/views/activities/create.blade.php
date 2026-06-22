@extends('layouts.master')

@section('title', 'Buat Sesi Patungan - OCR')

@section('content')
<div class="max-w-3xl mx-auto py-10" x-data="{ 
    friends: [], 
    newFriend: '',
    isOcrProcessed: false,
    isUploading: false,
    ocrItems: [],
    tax: 0,
    serviceCharge: 0,
    splitType: 'proportional',

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
        return this.ocrItems.reduce((sum, item) => sum + parseInt(item.price || 0), 0);
    },
    getGrandTotal() {
        return this.getSubtotal() + parseInt(this.tax || 0) + parseInt(this.serviceCharge || 0);
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
            let sharedTax = parseInt(this.tax || 0) / totalMembers;
            let sharedSc = parseInt(this.serviceCharge || 0) / totalMembers;

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
            b.tax = proportion * parseInt(this.tax || 0);
            b.serviceCharge = proportion * parseInt(this.serviceCharge || 0);
            b.total = b.subtotal + b.tax + b.serviceCharge;
        });

        return this.friends.map(friend => breakdowns[friend]);
    }
}">

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
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Nama Acara</label>
                <input type="text" name="title" required placeholder="Misal: Healing Bareng" 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Lokasi</label>
                <input type="text" name="location" required placeholder="Misal: Ancol" 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Tanggal</label>
                <input type="date" name="event_date" required 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
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
    @change="
        if ($event.target.files.length === 0) return;

        isUploading = true;
        isOcrProcessed = false; 
        ocrItems = []; 

        let formData = new FormData();
        formData.append('image', $event.target.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Rute disesuaikan dengan web.php milikmu
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
            isUploading = false;
            
            // SINKRONISASI JSON: Disesuaikan dengan output dari backend Bagirata
            if (resBody.items && resBody.items.length > 0) {
                
                ocrItems = resBody.items.map(item => ({
                    name: item.name,
                    price: item.price,
                    friend: item.friend || '',
                    friends: item.friend ? item.friend.split(',').filter(Boolean) : []
                }));
                isOcrProcessed = true; 
                
                if (resBody.date) {
                    document.getElementsByName('event_date')[0].value = resBody.date;
                }
                if (resBody.description) {
                    document.getElementsByName('title')[0].value = 'PATUNGAN DI ' + resBody.description.toUpperCase();
                }

                // --- SINKRONISASI PAJAK & SERVICE ---
                if (resBody.tax) {
                    tax = resBody.tax;
                }
                if (resBody.service) {
                    serviceCharge = resBody.service;
                }

            } else {
                alert(resBody.error || 'Mesin OCR sukses membaca struk, namun format menu & harga tidak cocok.');
            }
        })
        .catch(err => {
            isUploading = false;
            alert('Gagal memproses koneksi: ' + err.message);
        });
    " 
    class="absolute inset-0 opacity-0 cursor-pointer">
                
                <span class="text-4xl group-hover:scale-120 transition-transform inline-block mb-2">📸</span>
                <p class="text-sm font-black text-gray-700 uppercase tracking-wide">Upload Struk Belanja</p>
                <p class="text-[10px] text-gray-400 font-bold mt-1">Pilih / Ambil Foto Struk (Sistem Cerdas Bagirata OCR)</p>
            </div>

            <div class="text-center py-4" x-show="!isOcrProcessed && !isUploading">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] my-3">— ATAU —</p>
                <button type="button" @click="isOcrProcessed = true; ocrItems.push({ name: '', price: 0, friend: '', friends: [] })" 
                    class="px-6 py-4 bg-gray-950 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors shadow-lg active:scale-95">
                    ✍️ Input Menu Secara Manual
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
                            ✨ <span>Ekstraksi OCR Berhasil! Kamu bisa mengedit teks/harga jika ada kesalahan pembacaan.</span>
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
                                    <th class="pb-3 w-1/5" x-text="'Harga (' + $store.currency.symbol + ')'">Harga (Rp)</th>
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
                                            <input type="number" x-model="item.price" 
                                                class="w-full bg-transparent border-b border-transparent focus:border-blue-500 font-bold text-gray-950 italic focus:bg-gray-50 px-2 py-1 rounded-md outline-none text-left">
                                        </td>
                                        <td class="py-3 pr-2" x-show="splitType === 'proportional'">
                                            <div class="flex flex-wrap gap-1.5 max-w-xs">
                                                <template x-for="friend in friends" :key="friend">
                                                    <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tight cursor-pointer border transition-all active:scale-95 select-none"
                                                           :class="item.friends && item.friends.includes(friend) ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-100' : 'bg-gray-50 text-gray-500 border-gray-100 hover:bg-gray-100'">
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
                            <span>➕</span> Tambah Baris Menu
                        </button>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-3xl p-6 space-y-4 shadow-sm">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Biaya Tambahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 italic">Pajak (PPN)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400" x-text="$store.currency.symbol"></span>
                                    <input type="number" x-model="tax" name="tax" min="0" placeholder="0"
                                        class="w-full pl-8 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 italic">Service Charge</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-black text-gray-400" x-text="$store.currency.symbol"></span>
                                    <input type="number" x-model="serviceCharge" name="service_charge" min="0" placeholder="0"
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
                            <span class="text-xs font-black uppercase tracking-widest opacity-80">Grand Total</span>
                            <span class="text-2xl font-black italic" x-text="$store.currency.symbol + ' ' + $store.currency.format(getGrandTotal())">Rp 0</span>
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
                                        <span class="font-black text-base text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format(Math.round(member.total))"></span>
                                        <div class="text-[8px] text-gray-400 font-bold uppercase tracking-wider mt-1">
                                            Sub: <span x-text="$store.currency.format(Math.round(member.subtotal))"></span> | 
                                            Pajak: <span x-text="$store.currency.format(Math.round(member.tax))"></span> | 
                                            SC: <span x-text="$store.currency.format(Math.round(member.serviceCharge))"></span>
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

        <button type="submit" class="w-full py-6 bg-blue-600 text-white rounded-[2rem] font-black text-xl shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 uppercase tracking-widest italic">
            🚀 SELESAIKAN & BAGIRATA KAN
        </button>
    </form>
</div>
@endsection