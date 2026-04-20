<?php

use Livewire\Component;

new class extends Component {
}; ?>

<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>

    <div class="grid md:grid-cols-2 gap-4">
        <a
            href="{{ route('booking.form') }}"
            wire:navigate
            class="block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition"
        >
            <h2 class="text-xl font-semibold">Book a Room</h2>
            <p class="text-zinc-600 mt-2">Create a new booking and view room pricing.</p>
        </a>

        <a
            href="{{ route('my-bookings') }}"
            wire:navigate
            class="block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition"
        >
            <h2 class="text-xl font-semibold">My Bookings</h2>
            <p class="text-zinc-600 mt-2">Track your reservations and booking status.</p>
        </a>

        @if(auth()->user()->role === 'admin')
            <a
                href="{{ route('admin.dashboard') }}"
                wire:navigate
                class="block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-md transition md:col-span-2"
            >
                <h2 class="text-xl font-semibold">Admin Panel</h2>
                <p class="text-zinc-600 mt-2">Manage room types, rooms, pricing, and bookings.</p>
            </a>
        @endif
    </div>
</div>