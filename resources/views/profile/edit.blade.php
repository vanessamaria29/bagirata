@extends('layouts.master')

@section('title', 'Profil Pengguna')

@section('content')
<div class="max-w-4xl mx-auto py-10 space-y-8">
    <div class="mb-10 flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="p-3 bg-white rounded-2xl shadow-sm text-gray-400 hover:text-blue-600 transition-all active:scale-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h2 class="text-4xl font-black text-gray-950 tracking-tighter italic uppercase">Profil Saya</h2>
    </div>

    <div class="space-y-8">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-50">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection