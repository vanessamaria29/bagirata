@extends('layouts.master')

@section('title', 'Edit Sesi')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <div class="mb-10 flex items-center gap-4">
        <a href="{{ url()->previous() }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic">Edit Sesi</h2>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50">
        <form action="{{ route('activities.update', $activity->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Nama Acara</label>
                <input type="text" name="title" value="{{ $activity->title }}" required 
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Lokasi</label>
                    <input type="text" name="location" value="{{ $activity->location }}" 
                        class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Tanggal</label>
                    <input type="date" name="event_date" value="{{ $activity->event_date }}" required 
                        class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 font-bold text-gray-900">
                </div>
            </div>

            <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all hover:-translate-y-1">
                UPDATE SESI
            </button>
        </form>
    </div>
</div>
@endsection