@extends('layouts.master')

@section('title', 'Detail Sesi (Shared)')

@section('content')
<div class="max-w-4xl mx-auto py-6 space-y-8">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all active:scale-90">
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
    </div>

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
                        <span id="payment-badge-{{ $member['id'] }}"
                              class="w-fit mt-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider select-none border
                              {{ $member['payment_status'] === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100/60' : 'bg-rose-50 text-rose-700 border-rose-100/60' }}">
                            {{ $member['payment_status'] === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                        </span>
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
@endsection
