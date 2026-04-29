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

<div class="min-h-screen bg-gray-50">

    {{-- NAVBAR --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-emerald-600">Hotel Tropicana</h1>

            <div class="flex gap-4 items-center">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-700 hover:text-emerald-600">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-emerald-600">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- HERO --}}
    <div class="bg-gradient-to-r from-emerald-600 to-indigo-600 text-white py-16">
        <div class="max-w-6xl mx-auto text-center px-6">
            <h1 class="text-4xl font-bold mb-4">Welcome to Hotel Tropicana</h1>
            <p class="text-lg opacity-90">Experience comfort, luxury, and convenience</p>
        </div>
    </div>

    {{-- CONTENT --}}
    <main class="max-w-7xl mx-auto p-6">

        {{-- SEARCH --}}
        <div class="mb-6">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search room number or type..."
                class="w-full border rounded-xl p-3 shadow-sm focus:ring-2 focus:ring-emerald-500"
            >
        </div>

      
        
        {{-- ROOMS GRID --}}
        <div class="grid md:grid-cols-3 gap-6">

            @forelse ($rooms as $room)
                <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden object-cover hover:scale-105 transition duration-300">

                    {{-- IMAGE --}}
                    @if($room->image)
                        <img 
                            src="{{ asset('storage/' . $room->image) }}" 
                            class="w-full h-auto object-cover"
                        >
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">
                            No Image
                        </div>
                    @endif

                    {{-- DETAILS --}}
                    <div class="p-5 space-y-2">

                        <h3 class="text-lg font-semibold">
                            Room {{ $room->room_number }}
                        </h3>

                        <p class="text-sm text-gray-600">
                            {{ $room->roomType->name }}
                        </p>

                        <p class="text-sm text-gray-500">
                            👥 {{ $room->roomType->capacity }} Guests • 🛏 {{ $room->roomType->beds }} Beds
                        </p>

                        @if($room->roomType->description)
                            <p class="text-sm text-gray-500">
                                {{ $room->roomType->description }}
                            </p>
                        @endif

                        {{-- PRICE --}}
                        <p class="text-emerald-600 font-bold">
                            ₱{{ number_format(optional($room->roomType->rates->first())->price_per_night ?? 0, 2) }} / night
                        </p>

                        {{-- STATUS --}}
                        @if($room->status === 'available')
                            <span class="inline-block px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                Available
                            </span>
                        @elseif($room->status === 'occupied')
                            <span class="inline-block px-3 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                Occupied
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                Maintenance
                            </span>
                        @endif

                        {{-- BUTTON --}}
                        @if($room->status === 'available')
                            @auth
                                <a 
                                    href="{{ route('booking.form') }}" 
                                    wire:navigate
                                    class="block text-center mt-3 bg-emerald-600 text-white py-2 rounded-xl hover:bg-emerald-700 transition"
                                >
                                    Book Now
                                </a>
                            @else
                                <a 
                                    href="{{ route('login') }}"
                                    class="block text-center mt-3 bg-emerald-600 text-white py-2 rounded-xl hover:bg-emerald-700 transition"
                                >
                                    Login to Book
                                </a>
                            @endif
                        @else
                            <button 
                                disabled 
                                class="w-full mt-3 bg-gray-300 text-gray-600 py-2 rounded-xl cursor-not-allowed"
                            >
                                Not Available
                            </button>
                        @endif

                    </div>
                </div>

            @empty
                <div class="md:col-span-3 bg-white p-8 rounded-2xl shadow text-center">
                    <h2 class="text-xl font-semibold mb-2">No rooms found</h2>
                    <p class="text-gray-500">Try another search.</p>
                </div>
            @endforelse

        </div>

    </main>

    {{-- FOOTER --}}
    <footer class="bg-white border-t mt-10">
        <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-500 text-center">
            © {{ date('Y') }} Hotel Tropicana. All rights reserved.
        </div>
    </footer>

</div>
