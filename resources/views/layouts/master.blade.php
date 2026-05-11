<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bagirata - @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50"> 
    
    @if(!Request::is('/'))
    <nav class="bg-white shadow-sm mb-6 border-b border-gray-200">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="font-extrabold text-2xl text-blue-600 tracking-tighter">
                BAGIRATA
            </a>
            
            <div class="flex items-center space-x-6">
                @auth
                    <span class="text-gray-600 font-medium hidden md:inline">Halo, {{ Auth::user()->name }}</span>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 px-3 py-1 rounded-lg transition">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 font-semibold">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>
    @endif

    <div class="container mx-auto px-4">
        @yield('content')
    </div>

    @if(!Request::is('/'))
    <div class="md:hidden fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 shadow-2xl">
        <div class="grid h-full grid-cols-4 mx-auto font-medium">
            <a href="{{ route('dashboard') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <span class="text-xs text-blue-600 font-bold">Home</span>
            </a>
            <a href="#" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <span class="text-xs text-gray-500 group-hover:text-blue-600">Bills</span>
            </a>
            <a href="#" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <span class="text-xs text-gray-500 group-hover:text-blue-600">Friends</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <span class="text-xs text-gray-500 group-hover:text-blue-600">Profile</span>
            </a>
        </div>
    </div>
    @endif

    <footer class="text-center py-10 text-gray-400 text-sm">
        <p>&copy; {{ date('Y') }} Bagirata Project • Dibuat dengan Laravel</p>
    </footer>

    @stack('scripts')
</body>
</html>