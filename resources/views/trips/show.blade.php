@extends('layouts.master')

@section('title', 'Detail Trip ' . $trip->name)

@section('content')
<div id="trip-detail-container" class="max-w-4xl mx-auto py-6 space-y-8" x-data="{ 
    openDelete: false,
    openConfirmPayment: false,
    pendingMemberName: '',
    confirmTitle: '',
    confirmMessage: '',
    confirmTheme: 'blue',
    executePayment() {
        this.openConfirmPayment = false;
        sendTogglePaymentAjax(this.pendingMemberName);
    }
}">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('trips.index') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none">{{ $trip->name }}</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"> Trip Folder</p>
            </div>
        </div>

        <div class="flex gap-2">
            @php
                $waText = "─── 🧾 *KONSOLIDASI TRIP: " . strtoupper($trip->name) . "* 🧾 ───\n\n";
                foreach ($consolidated as $normalized => $data) {
                    $statusStr = $data['is_fully_paid'] ? "Lunas" : "Belum Lunas";
                    $waText .= "* " . $data['name'] . ": Rp " . number_format($data['total'], 0, ',', '.') . " (" . $statusStr . ")\n";
                }
                $waText .= "\nLihat rincian lengkap folder trip di sini:\n";
                $waText .= route('trips.shared', $trip->uuid);

                $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($waText);
            @endphp
            <a href="{{ $whatsappUrl }}" target="_blank" class="bg-green-50 text-green-600 p-3 rounded-2xl hover:bg-green-500 hover:text-white transition-all active:scale-95 shadow-sm shadow-green-100 flex items-center gap-1.5 font-black text-xs uppercase tracking-wider border border-green-100/50">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.666.988 3.311 1.63 5.351 1.633 5.415.002 9.825-4.405 9.828-9.823.002-2.625-1.02-5.093-2.883-6.958C17.08 2.14 14.618 1.117 12 1.115 6.582 1.115 2.176 5.522 2.173 10.94c-.001 2.083.548 4.116 1.59 5.897l-1.04 3.799 3.924-1.03c.001-.001.001-.001.001 0z"/>
                    <path d="M17.472 14.382c-.302-.15-1.787-.882-2.063-.982-.277-.1-.478-.15-.678.15-.2.3-.775.982-.95 1.183-.175.2-.35.225-.65.075-3.05-1.52-3.85-2.28-4.575-3.53-.19-.325-.02-.5-.17-.65-.135-.135-.3-.35-.45-.525-.15-.175-.2-.3-.3-.5s-.05-.375.05-.525c.1-.15.678-.775.975-1.125.3-.35.4-.575.6-.975.2-.4.1-.75-.05-.9s-.775-1.883-1.075-2.583c-.29-.7-2.025-.575-2.225-.55-.2.025-.65.2-1.025.575-.375.375-1.45 1.425-1.45 3.475s1.475 4.025 1.675 4.3c.2.275 2.9 4.425 7.025 6.2 2.975 1.28 3.75 1.275 5.075 1.15.525-.05 1.787-.725 2.037-1.425.25-.7.25-1.3.175-1.425-.075-.125-.275-.2-.575-.35z"/>
                </svg>
                <span class="hidden sm:inline">Bagikan Trip</span>
            </a>

            <button @click="openDelete = true" class="bg-red-50 text-red-500 p-3 rounded-2xl hover:bg-red-500 hover:text-white transition-all active:scale-95 shadow-sm shadow-red-100 border border-red-100/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Delete Modal -->
    <template x-teleport="body">
        <div x-show="openDelete" class="fixed inset-0 z-[150] flex items-center justify-center p-6" x-cloak>
            <div @click="openDelete = false" x-show="openDelete" x-transition.opacity class="fixed inset-0 bg-gray-950/60 backdrop-blur-md"></div>
            <div x-show="openDelete" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="relative bg-white rounded-3xl p-10 max-w-md w-full shadow-2xl border border-gray-100 text-center">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 italic tracking-tighter mb-2 uppercase">Hapus Trip Folder?</h3>
                <p class="text-gray-500 font-medium mb-8 text-sm">Semua sesi pengeluaran di dalam folder ini akan ikut terhapus secara permanen.</p>
                <div class="flex gap-4">
                    <button @click="openDelete = false" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-[10px]">Batal</button>
                    <form action="{{ route('trips.destroy', $trip->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-4 bg-red-500 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-xl shadow-red-200">Ya, Hapus!</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Payment Toggle Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="openConfirmPayment" class="fixed inset-0 z-[150] flex items-center justify-center p-6" x-cloak>
            <div @click="openConfirmPayment = false" x-show="openConfirmPayment" x-transition.opacity class="fixed inset-0 bg-gray-950/60 backdrop-blur-md"></div>
            <div x-show="openConfirmPayment" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="relative bg-white rounded-3xl p-10 max-w-md w-full shadow-2xl border border-gray-100 text-center">
                
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
                     :class="confirmTheme === 'orange' ? 'bg-orange-50' : 'bg-blue-50'">
                    
                    <template x-if="confirmTheme === 'orange'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                    
                    <template x-if="confirmTheme === 'blue'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>

                <h3 class="text-2xl font-black text-gray-900 italic tracking-tighter mb-2 uppercase" x-text="confirmTitle"></h3>
                <p class="text-gray-500 font-medium mb-8 text-sm" x-text="confirmMessage"></p>

                <div class="flex gap-4">
                    <button @click="openConfirmPayment = false" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-[10px]">Batal</button>
                    <button @click="executePayment()" 
                            class="flex-1 py-4 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] shadow-xl"
                            :class="confirmTheme === 'orange' ? 'bg-orange-500 shadow-orange-200' : 'bg-blue-600 shadow-blue-200'">
                        Ya, Ubah!
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Bento Grid Trip Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-tr from-blue-600 to-indigo-950 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group flex flex-col justify-between h-full min-h-[160px]">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl transition-transform group-hover:scale-125 duration-700"></div>
            <div>
                <p class="relative z-10 text-[10px] font-black uppercase tracking-[0.2em] text-blue-100 italic">Total Pengeluaran Trip</p>
                <h3 class="relative z-10 text-3xl font-black italic mt-4 tracking-tighter">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
            </div>
            <p class="relative z-10 text-[11px] text-blue-200 mt-4 font-semibold opacity-85">Akumulasi seluruh biaya ditalangi di trip ini.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Jumlah Sesi</p>
                <h3 class="text-4xl font-black text-gray-900 mt-4 tracking-tighter">{{ $activities->count() }}</h3>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold mt-4">Total catatan pembayaran ditalangi</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full transition-transform group-hover:scale-150 duration-700 opacity-60"></div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Status Sesi Aktif</p>
                <h3 class="text-4xl font-black text-gray-900 mt-4 tracking-tighter">{{ $activeSessions }}</h3>
            </div>
            <p class="text-[11px] text-orange-500 font-bold mt-4 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                Sesi belum diselesaikan
            </p>
        </div>
    </div>

    <!-- Action Banner to Add Expense -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6 group">
        <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>
        <div class="space-y-2 relative z-10">
            <h2 class="text-2xl font-black tracking-tight italic uppercase">Tambah Pengeluaran Trip?</h2>
            <p class="text-xs text-blue-100 font-medium opacity-90 max-w-xl">Catat alokasi menu makanan, tiket masuk, atau pengeluaran ditalangi lainnya khusus untuk folder trip ini.</p>
        </div>
        <a href="{{ route('activities.create') }}?trip_id={{ $trip->id }}" class="relative z-10 px-8 py-4 bg-white text-blue-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 hover:scale-105 active:scale-95 transition-all shadow-lg flex items-center gap-2 whitespace-nowrap min-h-[48px] justify-center">
            ➕ TAMBAH SESI BARU
        </a>
    </div>

    <!-- Consolidated Settlement Bento Grid -->
    <div class="space-y-6">
        <h3 class="text-2xl font-black text-gray-950 tracking-tighter italic uppercase flex items-center gap-3">
            Ringkasan Tagihan
        </h3>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest -mt-4">Total Tagihan Bersih per Anggota</p>
        
        @if(count($consolidated) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($consolidated as $normalized => $data)
                    @php
                        $hostFirstName = strtoupper(explode(' ', auth()->user()->name)[0]);
                        $hostFullName = strtoupper(auth()->user()->name);
                        $memberName = strtoupper($data['name']);
                        $isHost = ($memberName === $hostFirstName || $memberName === $hostFullName);
                    @endphp
                    <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-blue-900/5 border border-blue-50 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300" id="member-row-{{ $normalized }}">
                        <!-- Premium abstract shape background -->
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full opacity-60 group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <div class="relative z-10 flex items-start justify-between mb-8">
                            <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-xl uppercase shadow-lg shadow-blue-200">
                                {{ substr($data['name'], 0, 1) }}
                            </div>
                            
                            @if($isHost)
                                <span class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200 select-none shadow-sm">
                                    HOST
                                </span>
                            @else
                                <button onclick="toggleMemberPayment('{{ addslashes($data['name']) }}')" 
                                        id="payment-badge-{{ $normalized }}"
                                        class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none border-2 active:scale-95 shadow-sm
                                        {{ $data['is_fully_paid'] ? 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-100 shadow-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 shadow-rose-100' }}">
                                    {{ $data['is_fully_paid'] ? 'LUNAS' : 'BELUM BAYAR' }}
                                </button>
                            @endif
                        </div>

                        <div class="relative z-10">
                            <span class="font-black text-sm text-gray-400 uppercase tracking-widest">{{ $data['name'] }}</span>
                            <div class="font-black text-2xl text-blue-600 italic mt-1 tracking-tight">
                                Rp {{ number_format($data['total'], 0, ',', '.') }}
                            </div>
                            
                            @if(!$isHost)
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sisa Hutang:</span>
                                <span class="text-[11px] font-black uppercase tracking-widest" id="unpaid-info-{{ $normalized }}"
                                      :class="{{ $data['unpaid'] }} > 0 ? 'text-rose-500' : 'text-emerald-500'">
                                    Rp {{ number_format($data['unpaid'], 0, ',', '.') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 bg-white rounded-[2rem] border border-gray-100 text-center shadow-sm">>
                <p class="text-gray-400 font-black italic uppercase tracking-widest text-xs">Belum ada tagihan konsolidasi.</p>
            </div>
        @endif
    </div>

    <!-- Sesi Pengeluaran Linier List -->
    <div class="space-y-6">
        <h3 class="text-2xl font-black text-gray-950 tracking-tighter italic uppercase flex items-center gap-3">
            Daftar Sesi Pengeluaran
        </h3>

        <div class="grid grid-cols-1 gap-4">
            @forelse($activities as $activity)
                <div class="group relative p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex justify-between items-center">
                    <a href="{{ route('activities.show', $activity->id) }}" class="absolute inset-0 z-10"></a>
                    <div class="flex items-center gap-4 relative z-20">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-50 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-xl text-gray-900 group-hover:text-blue-600 transition-colors duration-300 uppercase tracking-tight">{{ $activity->title }}</h4>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                                {{ \Carbon\Carbon::parse($activity->event_date)->format('d M Y') }} • {{ $activity->location ?? 'Lokasi' }}
                                @if($activity->original_currency && $activity->original_currency !== 'IDR')
                                    • <span class="text-blue-600 font-black">Asal: {{ number_format($activity->original_amount, 2) }} {{ $activity->original_currency }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 relative z-20 text-right">
                        <div class="flex flex-col items-end">
                            <span class="font-black text-xl text-blue-600 italic">
                                Rp {{ number_format($activity->total_amount, 0, ',', '.') }}
                            </span>
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border
                                  {{ $activity->status === 'settled' ? 'bg-emerald-50 text-emerald-700 border-emerald-100/60' : 'bg-amber-50 text-amber-700 border-amber-100/60' }}">
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
                <div class="py-20 bg-slate-50 rounded-3xl border-4 border-dashed border-slate-200 text-center">
                    <p class="text-gray-400 font-black italic uppercase tracking-widest">Belum ada sesi pengeluaran di dalam trip ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleMemberPayment(memberName) {
    const normalized = memberName.toUpperCase();
    const badge = document.getElementById(`payment-badge-${normalized}`);
    if (!badge) return;

    const isCurrentlyPaid = badge.textContent.trim().toUpperCase() === 'LUNAS';
    
    const container = document.getElementById('trip-detail-container');
    if (container && window.Alpine) {
        const alpineData = window.Alpine.$data(container);
        alpineData.pendingMemberName = memberName;
        if (isCurrentlyPaid) {
            alpineData.confirmTitle = 'Batalkan Pembayaran Trip?';
            alpineData.confirmMessage = `Peringatan: Status pembayaran ${memberName} saat ini sudah LUNAS untuk seluruh trip. Apakah Anda yakin ingin membatalkannya kembali menjadi BELUM BAYAR?`;
            alpineData.confirmTheme = 'orange';
        } else {
            alpineData.confirmTitle = 'Tandai Lunas Seluruh Trip?';
            alpineData.confirmMessage = `Apakah Anda yakin ingin menandai ${memberName} sebagai LUNAS untuk semua tagihan di trip ini? Pastikan Anda sudah menerima total transfer.`;
            alpineData.confirmTheme = 'blue';
        }
        alpineData.openConfirmPayment = true;
    }
}

function sendTogglePaymentAjax(memberName) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    fetch(`/trips/{{ $trip->id }}/toggle-member-payment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ member_name: memberName })
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memperbarui status pembayaran.');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const normalized = memberName.toUpperCase();
            const badge = document.getElementById(`payment-badge-${normalized}`);
            if (badge) {
                if (data.is_fully_paid) {
                    badge.textContent = 'LUNAS';
                    badge.className = 'w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none border bg-emerald-50 text-emerald-700 border-emerald-100/60 hover:bg-emerald-100';
                } else {
                    badge.textContent = 'BELUM BAYAR';
                    badge.className = 'w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none border bg-rose-50 text-rose-700 border-rose-100/60 hover:bg-rose-100';
                }
            }

            const unpaidInfo = document.getElementById(`unpaid-info-${normalized}`);
            if (unpaidInfo) {
                unpaidInfo.innerHTML = `Rp ${new Intl.NumberFormat('id-ID').format(data.unpaid_total).replace(/,/g, '.')}`;
                if (data.unpaid_total > 0) {
                    unpaidInfo.classList.remove('text-emerald-500');
                    unpaidInfo.classList.add('text-rose-500');
                } else {
                    unpaidInfo.classList.remove('text-rose-500');
                    unpaidInfo.classList.add('text-emerald-500');
                }
            }

            // Reload the page silently or trigger visual updates if necessary
            // For robust styling updates of original Sesi statuses, we can reload
            window.location.reload();
        }
    })
    .catch(err => {
        alert(err.message);
    });
}
</script>
@endpush
@endsection
