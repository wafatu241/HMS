<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900">

    @auth
        <div class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside class="w-64 shrink-0 border-r border-zinc-200 bg-zinc-50">
                <div class="flex h-full flex-col">
                    <div class="border-b border-zinc-200 px-6 py-5">
                        <a href="{{ route('dashboard') }}" wire:navigate class="text-lg font-semibold text-zinc-900">
                            Hotel Tropicana
                        </a>
                    </div>

                    <div class="flex-1 px-4 py-6">
                        <p class="mb-4 text-sm font-semibold text-zinc-400">Platform</p>

                        <div class="space-y-2">
                            <a
                                href="{{ route('dashboard') }}"
                                wire:navigate
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                            >
                                <span>🏠</span>
                                <span>Dashboard</span>
                            </a>

                            <a
                                href="{{ route('my-bookings') }}"
                                wire:navigate
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('my-bookings') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                            >
                                <span>📋</span>
                                <span>My Bookings</span>
                            </a>

                            <a
                                href="{{ route('booking.form') }}"
                                wire:navigate
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('booking.form') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                            >
                                <span>➕</span>
                                <span>Book a Room</span>
                            </a>

                            @if(auth()->user()->role === 'admin')
                                <div class="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-zinc-400">
                                    Admin
                                </div>

                                <a
                                    href="{{ route('admin.dashboard') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>🛡️</span>
                                    <span>Admin Dashboard</span>
                                </a>

                                <a
                                    href="{{ route('admin.room-types.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.room-types.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>🏨</span>
                                    <span>Room Types</span>
                                </a>

                                <a
                                    href="{{ route('admin.rooms.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.rooms.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>🗝️</span>
                                    <span>Rooms</span>
                                </a>

                                <a
                                    href="{{ route('admin.rates.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.rates.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>💵</span>
                                    <span>Rates</span>
                                </a>

                                <a
                                    href="{{ route('admin.bookings.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.bookings.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>📚</span>
                                    <span>Bookings</span>
                                </a>
                                <a
                                    href="{{ route('admin.login-history.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.login-history.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>🕒</span>
                                    <span>Login History</span>
                                </a>

                                <a
                                    href="{{ route('admin.audit-logs.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audit-logs.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>📜</span>
                                    <span>Audit Logs</span>
                                </a>

                                <a
                                    href="{{ route('admin.reports.index') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.reports.index') ? 'bg-white border border-zinc-200 text-black shadow-sm' : 'text-zinc-600 hover:bg-white hover:border hover:border-zinc-200' }}"
                                >
                                    <span>📊</span>
                                    <span>Sales Reports</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 p-4">
                        <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-200 text-sm font-semibold text-zinc-700">
                                    {{ auth()->user()->initials() }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-zinc-900">
                                        {{ auth()->user()->name }}
                                    </p>
                                    <p class="truncate text-xs text-zinc-500">
                                        {{ auth()->user()->email }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    wire:navigate
                                    class="block rounded-lg px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100"
                                >
                                    Settings
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                    >
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <main class="flex-1 p-6 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="min-h-screen">
            {{ $slot }}
        </main>
    @endauth

    @fluxScripts
</body>
</html>