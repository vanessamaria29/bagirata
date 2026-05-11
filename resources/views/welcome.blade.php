@extends('layouts.master')

@section('title', 'Bagirata - Patungan Jadi Adil')

@section('content')
<style>
    /* Animasi Blob yang lebih halus & sensitif */
    @keyframes orbit {
        0% { transform: translate(0, 0) rotate(0deg) scale(1); }
        50% { transform: translate(5vw, -5vh) rotate(90deg) scale(1.2); }
        100% { transform: translate(-2vw, 5vh) rotate(180deg) scale(1); }
    }
    .animate-orbit {
        animation: orbit 20s infinite linear alternate;
    }

    /* Paksa Full Screen Total */
    html, body {
        height: 100vh;
        width: 100vw;
        margin: 0;
        padding: 0;
        overflow: hidden; /* No Scroll */
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
</style>

<div class="fixed inset-0 w-full h-full bg-white flex flex-col overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-[60vw] h-[60vw] bg-blue-100/50 rounded-full blur-[120px] animate-orbit opacity-70"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] bg-indigo-50/50 rounded-full blur-[100px] animate-orbit opacity-60" style="animation-delay: -5s;"></div>

    <header class="relative z-30 w-full p-8 md:p-12">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-white rounded-2xl shadow-xl shadow-blue-100 border border-white transition-transform hover:scale-110 duration-500">
                <img src="{{ asset('icon-bagirata.png') }}" alt="Logo" class="w-10 h-10 object-contain">
            </div>
            <span class="font-black text-3xl text-gray-950 tracking-tighter italic">bagirata.</span>
        </div>
    </header>

    <main class="relative z-20 flex-grow flex items-center justify-center px-6 md:px-20">
        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-10">
                <div class="space-y-4">
                    <h1 class="text-[12vw] md:text-[8vw] font-black text-gray-950 leading-[0.85] tracking-tighter">
                        Patungan <br> <span class="text-blue-600">Simpel.</span>
                    </h1>
                    
                    <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-md leading-relaxed">
                        Kelola uang grup dalam satu aplikasi modern. <br> Tanpa drama, tanpa pusing.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-6 pt-4">
                    <a href="{{ route('register') }}" class="group relative px-12 py-6 bg-gray-950 text-white rounded-3xl font-black text-2xl shadow-2xl transition-all duration-500 hover:bg-blue-600 hover:-translate-y-2 hover:shadow-blue-200">
                        Daftar Sekarang
                        <span class="absolute -top-2 -right-2 bg-blue-500 text-[10px] py-1 px-2 rounded-full animate-bounce">FREE</span>
                    </a>
                    <a href="{{ route('login') }}" class="px-12 py-6 border-4 border-gray-950 text-gray-950 rounded-3xl font-black text-2xl transition-all duration-500 hover:bg-gray-950 hover:text-white hover:-translate-y-2">
                        Masuk Kembali
                    </a>
                </div>
            </div>

            <div class="hidden md:flex justify-end items-center p-10">
                <div class="group relative w-[35vw] h-[35vw] max-w-[500px] max-h-[500px]">
                    <div class="absolute inset-0 bg-blue-600 rounded-[4rem] blur-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-1000"></div>
                    
                    <div class="relative w-full h-full bg-white rounded-[4rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.15)] p-16 transition-all duration-700 ease-out transform rotate-6 group-hover:rotate-0 group-hover:scale-105 border border-white flex items-center justify-center">
                        <img src="{{ asset('icon-bagirata.png') }}" alt="Logo Bagirata" class="w-full h-full object-contain filter drop-shadow-2xl transition-transform duration-700 group-hover:scale-110">
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="relative z-30 w-full p-8 md:p-12 flex justify-between items-end">
        <div class="text-gray-400 font-bold tracking-widest text-xs uppercase">
            EST. 2026 • UKRIDA SI
        </div>
        <div class="flex gap-4">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">System Online</span>
        </div>
    </footer>

</div>
@endsection