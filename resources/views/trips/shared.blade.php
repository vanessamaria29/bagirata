@extends('layouts.master')

@section('title', 'Detail Trip ' . $trip->name . ' (Shared)')

@section('content')
<div class="max-w-4xl mx-auto py-6 space-y-8">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tighter italic leading-none">{{ $trip->name }}</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"> Trip Folder (Shared)</p>
            </div>
        </div>
    </div>

    <!-- Bento Grid Trip Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between min-h-[140px]">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Total Pengeluaran Trip</p>
                <h3 class="text-3xl font-black text-gray-900 mt-4 tracking-tighter" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $totalSpent }})">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold mt-4">Akumulasi seluruh biaya ditalangi di trip ini.</p>
        </div>

        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between min-h-[140px]">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Jumlah Sesi</p>
                <h3 class="text-4xl font-black text-gray-900 mt-4 tracking-tighter">{{ $activities->count() }}</h3>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold mt-4">Total catatan pembayaran ditalangi</p>
        </div>

        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between min-h-[140px]">
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

    <!-- Consolidated Settlement Bento Grid -->
    <div class="space-y-6">
        <h3 class="text-2xl font-black text-gray-950 tracking-tighter italic uppercase flex items-center gap-3">
            Ringkasan Tagihan
        </h3>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest -mt-4">Total Tagihan Bersih per Anggota</p>
        
        @if(count($consolidated) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($consolidated as $normalized => $data)
                    <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-blue-900/5 border border-blue-50 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300" id="member-row-{{ $normalized }}">
                        <!-- Premium abstract shape background -->
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full opacity-60 group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <div class="relative z-10 flex items-start justify-between mb-8">
                            <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black text-xl uppercase shadow-lg shadow-blue-200">
                                {{ substr($data['name'], 0, 1) }}
                            </div>
                            <span id="payment-badge-{{ $normalized }}"
                                  class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 select-none border-2 shadow-sm
                                  {{ $data['is_fully_paid'] ? 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-100' }}">
                                {{ $data['is_fully_paid'] ? 'LUNAS' : 'BELUM BAYAR' }}
                            </span>
                        </div>

                        <div class="relative z-10">
                            <span class="font-black text-sm text-gray-400 uppercase tracking-widest">{{ $data['name'] }}</span>
                            <div class="font-black text-2xl text-blue-600 italic mt-1 tracking-tight" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $data['total'] }})">
                                Rp {{ number_format($data['total'], 0, ',', '.') }}
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sisa Hutang:</span>
                                <span class="text-[11px] font-black uppercase tracking-widest" id="unpaid-info-{{ $normalized }}"
                                      :class="{{ $data['unpaid'] }} > 0 ? 'text-rose-500' : 'text-emerald-500'">
                                    Rp {{ number_format($data['unpaid'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 bg-white rounded-[2rem] border border-gray-100 text-center shadow-sm">
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
                    <a href="{{ route('activities.shared', $activity->uuid) }}" class="absolute inset-0 z-10"></a>
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
                            <span class="font-black text-xl text-blue-600 italic" x-text="$store.currency.symbol + ' ' + $store.currency.format({{ $activity->total_amount }})">
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
@endsection
