<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-white">
    <header class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-blue-700">Hotel Tropicana</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-700 hover:text-blue-700">
                    Home
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-zinc-700 hover:text-blue-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-700 hover:text-blue-700">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        {{ $slot }}
    </main>
    
        <footer class="border-t bg-white mt-10">
            <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-zinc-500">
                © {{ date('Y') }} Hotel Tropicana. All rights reserved.
            </div>
        </footer>
    @fluxScripts
</body>
</html>