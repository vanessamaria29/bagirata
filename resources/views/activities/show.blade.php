@extends('layouts.master')

@section('title', 'Detail Sesi')

@section('content')
<div id="activity-detail-container" class="max-w-4xl mx-auto py-6 space-y-8" x-data="{ 
    openDelete: false,
    openConfirmPayment: false,
    pendingMemberId: null,
    pendingMemberName: '',
    confirmTitle: '',
    confirmMessage: '',
    confirmTheme: 'blue',
    executePayment() {
        this.openConfirmPayment = false;
        sendTogglePaymentAjax(this.pendingMemberId);
    }
}">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ $activity->trip_id ? route('trips.show', $activity->trip_id) : route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all active:scale-90">
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
            @php
                $waText = "─── 🧾 *STRUK BAGIRATA* 🧾 ───\n";
                $waText .= "Nama Sesi: " . $activity->title . "\n\n";
                foreach ($activity->member_breakdown as $member) {
                    if ($member['name'] !== 'Unassigned') {
                        $waText .= "* " . $member['name'] . ": Rp " . number_format($member['total'], 0, ',', '.') . "\n";
                    }
                }
                $waText .= "\nCek rincian pesanan lengkap kamu di sini:\n";
                $waText .= route('activities.shared', $activity->uuid);

                $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($waText);
            @endphp
            <a href="{{ $whatsappUrl }}" target="_blank" class="bg-green-50 text-green-600 p-3 rounded-2xl hover:bg-green-500 hover:text-white transition-all active:scale-95 shadow-sm shadow-green-100 flex items-center gap-1.5 font-black text-xs uppercase tracking-wider">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.666.988 3.311 1.63 5.351 1.633 5.415.002 9.825-4.405 9.828-9.823.002-2.625-1.02-5.093-2.883-6.958C17.08 2.14 14.618 1.117 12 1.115 6.582 1.115 2.176 5.522 2.173 10.94c-.001 2.083.548 4.116 1.59 5.897l-1.04 3.799 3.924-1.03c.001-.001.001-.001.001 0z"/>
                    <path d="M17.472 14.382c-.302-.15-1.787-.882-2.063-.982-.277-.1-.478-.15-.678.15-.2.3-.775.982-.95 1.183-.175.2-.35.225-.65.075-3.05-1.52-3.85-2.28-4.575-3.53-.19-.325-.02-.5-.17-.65-.135-.135-.3-.35-.45-.525-.15-.175-.2-.3-.3-.5s-.05-.375.05-.525c.1-.15.678-.775.975-1.125.3-.35.4-.575.6-.975.2-.4.1-.75-.05-.9s-.775-1.883-1.075-2.583c-.29-.7-2.025-.575-2.225-.55-.2.025-.65.2-1.025.575-.375.375-1.45 1.425-1.45 3.475s1.475 4.025 1.675 4.3c.2.275 2.9 4.425 7.025 6.2 2.975 1.28 3.75 1.275 5.075 1.15.525-.05 1.787-.725 2.037-1.425.25-.7.25-1.3.175-1.425-.075-.125-.275-.2-.575-.35z"/>
                </svg>
                <span class="hidden sm:inline">Bagikan ke WA</span>
            </a>

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
                 class="relative bg-white rounded-3xl p-10 max-w-md w-full shadow-2xl border border-gray-100 text-center">
                
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @php
            $subtotal = $activity->total_amount - $activity->tax - $activity->service_charge;
        @endphp
        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rincian Biaya</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-xs font-bold text-gray-500">Subtotal Menu</span>
                    <span class="font-black text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $subtotal }})">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($activity->tax > 0)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-xs font-bold text-gray-500">Pajak (PPN)</span>
                    <span class="font-black text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $activity->tax }})">Rp {{ number_format($activity->tax, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($activity->service_charge > 0)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-xs font-bold text-gray-500">Service Charge</span>
                    <span class="font-black text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $activity->service_charge }})">Rp {{ number_format($activity->service_charge, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                    <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Grand Total</span>
                    <span class="text-xl font-black text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $activity->total_amount }})">Rp {{ number_format($activity->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status Sesi</p>
            <div class="mt-2 flex items-center" id="activity-status-container">
                @if($activity->status == 'active')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-100/60 animate-pulse">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        BELUM LUNAS
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100/60">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        LUNAS
                    </span>
                @endif
            </div>

            @php
                $unpaidTotalSum = $activity->member_breakdown->where('payment_status', 'unpaid')->sum('total');
            @endphp
            <div class="mt-4 border-t border-gray-100 pt-3" x-data="{ unpaidTotal: {{ $unpaidTotalSum }} }" id="unpaid-total-container">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Sisa Utang Kelompok</p>
                <p class="text-xl font-black text-blue-600 italic mt-1" id="unpaid-total-text" x-text="$store.currency.symbol + ' ' + $store.currency.format(unpaidTotal)">
                    Rp {{ number_format($unpaidTotalSum, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <h3 class="text-2xl font-black text-gray-950 tracking-tighter italic uppercase flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Pembagian per Anggota
        </h3>

        @forelse($activity->member_breakdown as $member)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" id="member-card-{{ $member['id'] ?? 'unassigned' }}">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-black text-sm uppercase shadow-sm shadow-blue-100">
                        {{ substr($member['name'], 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-lg text-gray-900 uppercase tracking-tight">{{ $member['name'] }}</span>
                        @if($member['id'] !== null && $member['name'] !== 'Unassigned')
                        <button onclick="togglePayment({{ $member['id'] }}, '{{ addslashes($member['name']) }}')" 
                                id="payment-badge-{{ $member['id'] }}"
                                class="w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none active:scale-95 border
                                {{ $member['payment_status'] === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100/60 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100/60 hover:bg-rose-100' }}">
                            {{ $member['payment_status'] === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                        </button>
                        @endif
                    </div>
                </div>
                <span class="font-black text-xl text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $member['total'] }})">
                    Rp {{ number_format($member['total'], 0, ',', '.') }}
                </span>
            </div>
            @if($member['items']->isNotEmpty())
            <div class="px-6 py-4 space-y-2">
                @foreach($member['items'] as $item)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-bold">{{ $item->name }}</span>
                    <span class="font-black text-gray-900" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $item->price }})">
                        Rp {{ number_format($item->price, 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 italic font-medium">
                    @if($activity->split_type == 'equal')
                        🤝 Semua pesanan digabung (Sistem Bagi Rata)
                    @else
                        Tidak ada pesanan khusus
                    @endif
                </p>
            </div>
            @endif
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-sm">
                <div class="flex gap-4">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Subtotal: <span class="text-gray-700" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $member['subtotal'] }})">Rp {{ number_format($member['subtotal'], 0, ',', '.') }}</span>
                    </span>
                    @if($activity->tax > 0)
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        PPN: <span class="text-orange-500" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $member['tax'] }})">Rp {{ number_format($member['tax'], 0, ',', '.') }}</span>
                    </span>
                    @endif
                    @if($activity->service_charge > 0)
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        SC: <span class="text-orange-500" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $member['sc'] }})">Rp {{ number_format($member['sc'], 0, ',', '.') }}</span>
                    </span>
                    @endif
                </div>
                <span class="font-black text-base text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $member['total'] }})">
                    Rp {{ number_format($member['total'], 0, ',', '.') }}
                </span>
            </div>
        </div>
        @empty
        <div class="bg-gray-50 border-4 border-dashed border-gray-200 rounded-[3rem] py-24 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <p class="text-gray-400 font-black italic uppercase tracking-widest">Belum ada anggota</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function togglePayment(memberId, memberName) {
    const badge = document.getElementById(`payment-badge-${memberId}`);
    if (!badge) return;

    const isCurrentlyPaid = badge.textContent.trim().toUpperCase() === 'LUNAS';
    
    const container = document.getElementById('activity-detail-container');
    if (container && window.Alpine) {
        const alpineData = window.Alpine.$data(container);
        alpineData.pendingMemberId = memberId;
        alpineData.pendingMemberName = memberName;
        if (isCurrentlyPaid) {
            alpineData.confirmTitle = 'Batalkan Pembayaran?';
            alpineData.confirmMessage = `Peringatan: Status ${memberName} saat ini sudah LUNAS. Apakah Anda yakin ingin membatalkannya kembali menjadi BELUM BAYAR?`;
            alpineData.confirmTheme = 'orange';
        } else {
            alpineData.confirmTitle = 'Tandai Lunas?';
            alpineData.confirmMessage = `Apakah Anda yakin ingin menandai ${memberName} sebagai LUNAS? Pastikan uang/bukti transfer sudah Anda terima.`;
            alpineData.confirmTheme = 'blue';
        }
        alpineData.openConfirmPayment = true;
    }
}

function sendTogglePaymentAjax(memberId) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    fetch(`/members/${memberId}/toggle-payment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memperbarui status pembayaran.');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            // Update badge style and text
            const badge = document.getElementById(`payment-badge-${memberId}`);
            if (badge) {
                if (data.payment_status === 'paid') {
                    badge.textContent = 'LUNAS';
                    badge.className = 'w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none active:scale-95 border bg-emerald-50 text-emerald-700 border-emerald-100/60 hover:bg-emerald-100';
                } else {
                    badge.textContent = 'BELUM BAYAR';
                    badge.className = 'w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 cursor-pointer select-none active:scale-95 border bg-rose-50 text-rose-700 border-rose-100/60 hover:bg-rose-100';
                }
            }

            // Update activity status header & dot
            const statusContainer = document.getElementById('activity-status-container');
            if (statusContainer) {
                if (data.activity_status === 'active') {
                    statusContainer.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-100/60 animate-pulse">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            BELUM LUNAS
                        </span>
                    `;
                } else {
                    statusContainer.innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100/60">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            LUNAS
                        </span>
                    `;
                }
            }

            // Update remaining unpaid total text
            const unpaidText = document.getElementById('unpaid-total-text');
            if (unpaidText && window.Alpine) {
                const container = document.getElementById('unpaid-total-container');
                if (container) {
                    window.Alpine.$data(container).unpaidTotal = data.unpaid_total;
                } else {
                    unpaidText.textContent = window.Alpine.store('currency').symbol + ' ' + window.Alpine.store('currency').format(data.unpaid_total);
                }
            }
        }
    })
    .catch(err => {
        alert(err.message);
    });
}
</script>
@endpush
@endsection