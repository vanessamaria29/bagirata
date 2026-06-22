<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bagirata - @yield('title')</title>

    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="currency-route" content="{{ route('profile.currency') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Load Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        /* Premium Typography Fallback */
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Sensory Glassmorphism Effect */
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body x-data class="bg-[#f8fafc] antialiased text-[#0f172a]" data-currency="{{ auth()->user()->currency ?? 'IDR' }}"> 

   @if(session('success'))
<div x-data="{ show: true }" 
     x-init="setTimeout(() => show = false, 2500)" 
     x-show="show"
     class="fixed inset-0 flex items-center justify-center z-[200] pointer-events-none p-6">
    
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-950/20 backdrop-blur-sm"></div>

    <div x-show="show" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-50 rotate-12"
         x-transition:enter-end="opacity-100 scale-100 rotate-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-110"
         class="relative bg-white rounded-[3.5rem] p-12 shadow-[0_40px_100px_rgba(0,0,0,0.2)] border border-white flex flex-col items-center max-w-sm w-full text-center">
        
        <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mb-8 shadow-2xl shadow-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white animate-[bounce_1s_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h2 class="text-2xl font-black text-gray-900 italic tracking-tighter uppercase mb-2">Berhasil!</h2>
        <p class="text-gray-500 font-bold text-xs tracking-widest uppercase opacity-60">{{ session('success') }}</p>
    </div>
</div>
@endif
    
    @if(!Request::is('/'))
    <nav class="glass-nav sticky top-0 z-40 border-b border-gray-100 shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2 group">
                <div class="p-1.5 bg-blue-600 rounded-lg group-hover:rotate-12 transition-transform duration-300">
                    <img src="{{ asset('icon-bagirata.png') }}" class="w-6 h-6 invert brightness-0" alt="Logo">
                </div>
                <span class="font-black text-xl tracking-tighter italic">bagirata.</span>
            </a>
            
            <div class="flex items-center space-x-4">
                @auth
                <div class="hidden md:flex items-center space-x-6 mr-6 border-r border-gray-100 pr-6">
                    <a href="{{ route('dashboard') }}" class="text-xs font-black uppercase tracking-widest {{ Request::is('dashboard') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }} transition-colors">Home</a>
                    <a href="{{ route('activities.index') }}" class="text-xs font-black uppercase tracking-widest {{ Request::is('activities*') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }} transition-colors">Bills</a>
                    <a href="{{ route('trips.index') }}" class="text-xs font-black uppercase tracking-widest {{ Request::is('trips*') ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600' }} transition-colors">Trips</a>
                </div>

                @php
                    $rawName = Auth::user()->name; 
                    $displayName = ucfirst($rawName);
                    $initial = strtoupper(substr($displayName, 0, 1));
                @endphp

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black text-xl shadow-inner border-2 border-white">
                        {{ $initial }}
                    </div>
                    
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Host Active</span>
                        <span class="text-base font-bold text-gray-900">{{ $displayName }}</span>
                    </div>
                </div>

                    <select x-model="$store.currency.code"
                            @change="$store.currency.set($store.currency.code)"
                            class="px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-blue-200 cursor-pointer">
                        <option value="IDR">IDR (Rp)</option>
                        <option value="USD">USD ($)</option>
                        <option value="SGD">SGD (S$)</option>
                    </select>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
               @else
                {{-- Tombol Masuk --}}
                <a href="{{ route('login') }}" 
                    class="px-6 py-2.5 rounded-xl font-black text-sm transition-all duration-300
                    {{ request()->routeIs('login') 
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' 
                        : 'text-gray-400 hover:text-gray-950 hover:bg-gray-100' }}">
                    Masuk
                </a>

                {{-- Tombol Daftar --}}
                <a href="{{ route('register') }}" 
                    class="px-6 py-2.5 rounded-xl font-black text-sm transition-all duration-300
                    {{ request()->routeIs('register') 
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' 
                        : 'text-gray-400 hover:text-gray-950 hover:bg-gray-100' }}">
                    DAFTAR
                </a>
            @endauth
            </div>
        </div>
    </nav>
    @endif

    <main class="container mx-auto px-6 pb-32 pt-6">
        @yield('content')
    </main>

    @if(!Request::is('/') && auth()->check())
    <div class="md:hidden fixed bottom-6 left-6 right-6 z-50">
        <div class="glass-nav border border-white/50 shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-[2rem] px-4 h-20 flex items-center justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ Request::is('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Home</span>
            </a>
            
            <a href="{{ route('activities.index') }}" class="flex flex-col items-center gap-1 {{ Request::is('activities*') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Bills</span>
            </a>

            <a href="{{ route('trips.index') }}" class="flex flex-col items-center gap-1 {{ Request::is('trips*') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Trips</span>
            </a>

            <a href="{{ route('friends.index') }}" class="flex flex-col items-center gap-1 {{ Request::is('friends*') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Friends</span>
            </a>

            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 {{ Request::is('profile*') ? 'text-blue-600' : 'text-gray-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest">Me</span>
            </a>
        </div>
    </div>
    @endif
    
    <footer class="text-center py-10 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">
        &copy; {{ date('Y') }} BAGIRATA PROJECT • FTC UKRIDA
    </footer>

<script>
        document.addEventListener('alpine:init', () => {
            const currencyData = {
                'IDR': { symbol: 'Rp', locale: 'id-ID', decimals: 0 },
                'USD': { symbol: '$', locale: 'en-US', decimals: 2 },
                'SGD': { symbol: 'S$', locale: 'en-SG', decimals: 2 }
            };

            // Ambil mata uang terakhir yang disimpan user dari tag body
            const initialCode = document.body.getAttribute('data-currency') || 'IDR';

            Alpine.store('currency', {
                code: initialCode,
                symbol: currencyData[initialCode].symbol,
                locale: currencyData[initialCode].locale,
                decimals: currencyData[initialCode].decimals,

                // Fungsi yang dipanggil saat dropdown diubah
                set(newCode) {
                    this.code = newCode;
                    this.symbol = currencyData[newCode].symbol;
                    this.locale = currencyData[newCode].locale;
                    this.decimals = currencyData[newCode].decimals;

                    // Mengirim pilihan mata uang ke database di belakang layar (AJAX)
                    let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    let route = document.querySelector('meta[name="currency-route"]')?.getAttribute('content');
                    
                    if (token && route) {
                        fetch(route, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ currency: newCode })
                        }).catch(err => console.log('Gagal menyimpan preferensi mata uang.'));
                    }
                },

                // Fungsi untuk merender angka (Yang bikin halamanmu crash sebelumnya)
                format(value) {
                    let num = parseFloat(value) || 0;
                    return num.toLocaleString(this.locale, {
                        minimumFractionDigits: this.decimals,
                        maximumFractionDigits: this.decimals
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>