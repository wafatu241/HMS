<?php

use Livewire\Component;
use App\Models\Room;

new class extends Component {

    public $rooms = [];
    public $search = '';

    public function mount()
    {
        $this->loadRooms();
    }

    public function updatedSearch()
    {
        $this->loadRooms();
    }

    public function loadRooms()
    {
        $this->rooms = Room::with('roomType.rates')
            ->when($this->search, function ($query) {
                $query->where('room_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('roomType', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->get();
    }

}; ?>

<div class="min-h-screen bg-zinc-50 text-zinc-900">

    {{-- HEADER --}}
    <header class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-blue-700">Hotel Tropicana</h1>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-700 hover:text-blue-700">
                    Home
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-zinc-700 hover:text-blue-700">
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

    {{-- MAIN --}}
    <main class="max-w-7xl mx-auto p-6">

        {{-- HERO --}}
        <div class="mb-8 rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-8 shadow-sm">
            <h1 class="text-4xl font-bold mb-3">Welcome to Hotel Tropicana</h1>
            <p class="text-lg opacity-90 max-w-2xl">
                Discover comfortable rooms with modern amenities and easy booking.
            </p>
        </div>

        {{-- SEARCH --}}
        <div class="mb-6">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search room number or type..."
                class="w-full border rounded-xl p-3 bg-white"
            >
        </div>

        {{-- ROOM LIST --}}
        <div class="grid md:grid-cols-3 gap-6">
            @forelse ($rooms as $room)
                <div class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition">

                    {{-- IMAGE --}}
                    @if($room->image)
                        <img
                            src="{{ asset('storage/' . $room->image) }}"
                            class="w-full h-48 object-cover rounded-xl mb-4"
                        >
                    @else
                        <div class="w-full h-48 bg-zinc-100 rounded-xl mb-4 flex items-center justify-center text-zinc-500">
                            No Image
                        </div>
                    @endif

                    {{-- TITLE --}}
                    <div class="flex items-start justify-between">
                        <h2 class="text-xl font-semibold">{{ $room->roomType->name }}</h2>
                        <span class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full">
                            Room {{ $room->room_number }}
                        </span>
                    </div>

                    {{-- DETAILS LABELS --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="bg-zinc-100 px-3 py-1 text-sm rounded-full">
                            {{ $room->roomType->capacity }} People
                        </span>

                        <span class="bg-zinc-100 px-3 py-1 text-sm rounded-full">
                            {{ $room->roomType->beds }} Bed{{ $room->roomType->beds > 1 ? 's' : '' }}
                        </span>

                        <span class="bg-zinc-100 px-3 py-1 text-sm rounded-full">
                            Floor {{ $room->floor ?: 'N/A' }}
                        </span>
                    </div>

                    {{-- DESCRIPTION --}}
                    @if($room->roomType->description)
                        <p class="mt-3 text-sm text-zinc-600">
                            {{ $room->roomType->description }}
                        </p>
                    @endif

                    {{-- STATUS BADGE --}}
                    <div class="mt-3">
                        @if($room->status === 'available')
                            <span class="bg-green-100 text-green-700 px-3 py-1 text-sm rounded-full font-medium">
                                Available
                            </span>
                        @elseif($room->status === 'occupied')
                            <span class="bg-red-100 text-red-700 px-3 py-1 text-sm rounded-full font-medium">
                                Occupied
                            </span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 text-sm rounded-full font-medium">
                                Maintenance
                            </span>
                        @endif
                    </div>

                    {{-- PRICE --}}
                    <p class="mt-3 text-lg font-semibold text-blue-700">
                        ₱{{ number_format(optional($room->roomType->rates->first())->price_per_night ?? 0, 2) }}/night
                    </p>

                    {{-- ACTION BUTTON --}}
                    @if($room->status === 'available')
                        @auth
                            <a href="{{ route('booking.form') }}" wire:navigate
                               class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Book Now
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Login to Book
                            </a>
                        @endauth
                    @elseif($room->status === 'occupied')
                        <span class="inline-block mt-4 bg-red-100 text-red-700 px-4 py-2 rounded font-medium">
                            Not Available
                        </span>
                    @else
                        <span class="inline-block mt-4 bg-yellow-100 text-yellow-700 px-4 py-2 rounded font-medium">
                            Under Maintenance
                        </span>
                    @endif

                </div>
            @empty
                <div class="md:col-span-3 rounded-2xl border bg-white p-8 text-center shadow-sm">
                    <h2 class="text-xl font-semibold mb-2">No rooms found</h2>
                    <p class="text-zinc-600">Try another search term.</p>
                </div>
            @endforelse
        </div>

    </main>

    {{-- FOOTER --}}
    <footer class="border-t bg-white mt-10">
        <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-zinc-500">
            © {{ date('Y') }} Hotel Tropicana. All rights reserved.
        </div>
    </footer>

</div>