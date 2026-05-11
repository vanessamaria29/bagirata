@extends('layouts.master')

@section('title', 'Masuk ke Bagirata')

@section('content')
<style>
    @keyframes orbit-reverse {
        0% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(-3vw, 3vh) scale(1.1); }
        100% { transform: translate(1vw, -2vh) scale(1); }
    }
    .animate-reverse { animation: orbit-reverse 15s infinite linear alternate; }

    html, body {
        height: 100vh;
        overflow: hidden;
    }
</style>

<div class="fixed inset-0 w-full h-full bg-white flex items-center justify-center overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-indigo-50 rounded-full blur-[100px] animate-reverse opacity-60"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40vw] h-[40vw] bg-blue-50 rounded-full blur-[80px] animate-reverse opacity-50" style="animation-delay: -5s;"></div>

    <div class="relative z-10 w-full max-w-lg px-6">
        <div class="text-center mb-10">
            <div class="inline-block p-3 bg-white rounded-2xl shadow-xl border border-gray-50 mb-4 transition-transform hover:rotate-12 duration-500">
                <img src="{{ asset('icon-bagirata.png') }}" alt="Logo" class="w-12 h-12 object-contain">
            </div>
            <h2 class="text-4xl font-black text-gray-950 tracking-tighter">Masuk Kembali</h2>
            <p class="text-gray-500 font-medium mt-2">Senang melihatmu lagi di Bagirata!</p>
        </div>

        <div class="bg-white/80 backdrop-blur-2xl p-8 rounded-[2.5rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.1)] border border-white">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-black text-gray-700 ml-1 mb-1 italic">EMAIL</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold text-gray-900 placeholder-gray-400"
                        placeholder="email@kamu.com">
                    @error('email') <span class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center ml-1 mb-1">
                        <label class="block text-sm font-black text-gray-700 italic">PASSWORD</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-600 hover:underline">Lupa?</a>
                        @endif
                    </div>
                    <input type="password" name="password" required
                        class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 transition-all font-bold text-gray-900"
                        placeholder="••••••••">
                    @error('password') <span class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center ml-1">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded-md border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="remember_me" class="ml-2 text-sm font-bold text-gray-600">Ingat saya</label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xl shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all active:scale-95">
                        Masuk Sekarang
                    </button>
                    <p class="text-center text-gray-500 font-bold text-sm mt-8">
                        Belum punya akun? 
                        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Daftar Gratis</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection