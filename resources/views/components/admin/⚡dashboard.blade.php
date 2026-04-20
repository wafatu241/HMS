<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Booking;

new class extends Component {

    public $total_rooms = 0;
    public $available_rooms = 0;
    public $pending_bookings = 0;
    public $checked_in = 0;

    public function mount()
    {
        $this->total_rooms = Room::count();
        $this->available_rooms = Room::where('status', 'available')->count();
        $this->pending_bookings = Booking::where('status', 'pending')->count();
        $this->checked_in = Booking::where('status', 'checked_in')->count();
    }

}; ?>

<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid md:grid-cols-4 gap-4">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Total Rooms</p>
            <h2 class="text-3xl font-bold mt-2">{{ $total_rooms }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Available Rooms</p>
            <h2 class="text-3xl font-bold mt-2">{{ $available_rooms }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Pending Bookings</p>
            <h2 class="text-3xl font-bold mt-2">{{ $pending_bookings }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Checked In</p>
            <h2 class="text-3xl font-bold mt-2">{{ $checked_in }}</h2>
        </div>
    </div>
</div>