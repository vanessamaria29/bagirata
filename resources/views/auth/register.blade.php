@extends('layouts.master')

@section('title', 'Daftar Akun Bagirata')

@section('content')
<style>
    /* Animasi background agar tetap konsisten dengan Landing Page */
    @keyframes orbit-slow {
        0% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(3vw, -3vh) scale(1.1); }
        100% { transform: translate(-1vw, 2vh) scale(1); }
    }
    .animate-slow { animation: orbit-slow 15s infinite linear alternate; }

    html, body {
        height: 100vh;
        overflow: hidden;
    }
</style>

<div class="fixed inset-0 w-full h-full bg-white flex items-center justify-center overflow-hidden">
    
    <div class="absolute top-[-10%] right-[-10%] w-[50vw] h-[50vw] bg-blue-50 rounded-full blur-[100px] animate-slow opacity-60"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[40vw] h-[40vw] bg-indigo-50 rounded-full blur-[80px] animate-slow opacity-50" style="animation-delay: -7s;"></div>

    <div class="relative z-10 w-full max-w-lg px-6">
        <div class="text-center mb-10">
            <div class="inline-block p-3 bg-white rounded-2xl shadow-xl border border-gray-50 mb-4">
                <img src="{{ asset('icon-bagirata.png') }}" alt="Logo" class="w-12 h-12 object-contain">
            </div>
            <h2 class="text-4xl font-black text-gray-950 tracking-tighter">Buat Akun Baru</h2>
            <p class="text-gray-500 font-medium mt-2">Gabung sekarang dan mulai bagi rata!</p>
        </div>

        <div class="bg-white/80 backdrop-blur-2xl p-8 rounded-[2.5rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.1)] border border-white">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-black text-gray-700 ml-1 mb-1 italic">NAMA LENGKAP</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold text-gray-900 placeholder-gray-400"
                        placeholder="Siapa nama kamu?">
                    @error('name') <span class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-700 ml-1 mb-1 italic">EMAIL</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold text-gray-900 placeholder-gray-400"
                        placeholder="email@kamu.com">
                    @error('email') <span class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-black text-gray-700 ml-1 mb-1 italic">PASSWORD</label>
                        <input type="password" name="password" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold"
                            placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 ml-1 mb-1 italic">KONFIRMASI</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold"
                            placeholder="••••••••">
                    </div>
                </div>
                @error('password') <span class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</span> @enderror

                <div class="pt-4">
                    <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xl shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all active:scale-95">
                        Daftar Akun
                    </button>
                    <p class="text-center text-gray-500 font-bold text-sm mt-6">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk Kembali</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection