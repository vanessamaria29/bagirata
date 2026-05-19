@extends('layouts.master')

@section('title', 'Semua Tagihan')

@section('content')
<div class="py-4 space-y-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic leading-none">Semua Tagihan</h2>
        </div>
        <a href="{{ route('activities.create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-xs shadow-lg shadow-blue-100">+ BARU</a>
    </div>

    <div class="bg-white p-4 rounded-3xl border border-gray-100 flex gap-4 overflow-x-auto no-scrollbar">
        <button class="px-6 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Semua</button>
        <button class="px-6 py-2 bg-gray-50 text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600">Aktif</button>
        <button class="px-6 py-2 bg-gray-50 text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600">Lunas</button>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @forelse($activities as $activity)
            <div class="group p-6 bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-black text-xl text-gray-900 group-hover:text-blue-600">{{ $activity->title }}</h4>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">
                            {{ \Carbon\Carbon::parse($activity->event_date)->format('d M Y') }} • {{ $activity->location }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="font-black text-xl text-gray-950 italic tracking-tighter">Rp {{ number_format($activity->total_amount, 0, ',', '.') }}</p>
                        <span class="text-[9px] font-black {{ $activity->status == 'active' ? 'text-green-500' : 'text-gray-300' }} uppercase tracking-[0.2em] italic">
                            ● {{ $activity->status }}
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('activities.edit', $activity->id) }}" class="p-2 text-gray-300 hover:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-20 text-center bg-gray-50 rounded-[3rem] border-4 border-dashed border-gray-200">
                <p class="text-gray-400 font-black italic uppercase">Belum ada catatan tagihan</p>
            </div>
        @endforelse
    </div>
</div>
@endsection