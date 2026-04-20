<?php

use Livewire\Component;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Carbon\Carbon;

new class extends Component {
    use WithFileUploads;

    public $rooms = [];
    public $room_id = '';
    public $check_in = '';
    public $check_in_time = '14:00';
    public $check_out = '';
    public $check_out_time = '12:00';
    public $guests_count = 1;
    public $special_requests = '';
    public $payment_method = 'cash';
    public $payment_proof = null;

    public $selectedRoom = null;
    public $price_per_night = 0;
    public $number_of_nights = 0;
    public $total_amount = 0;

    public function mount()
    {
        $this->loadRooms();
    }

    public function loadRooms()
    {
        $this->rooms = Room::with('roomType.rates')
            ->where('status', 'available')
            ->get();
    }

    public function updatedRoomId()
    {
        $this->selectedRoom = Room::with('roomType.rates')->find($this->room_id);

        if ($this->selectedRoom && $this->selectedRoom->roomType->rates->count()) {
            $this->price_per_night = $this->selectedRoom->roomType->rates->first()->price_per_night;
        } else {
            $this->price_per_night = 0;
        }

        $this->calculateTotal();
    }

    public function updatedCheckIn()
    {
        $this->calculateTotal();
    }

    public function updatedCheckOut()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->number_of_nights = 0;
        $this->total_amount = 0;

        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->gt($checkIn)) {
                $this->number_of_nights = $checkIn->diffInDays($checkOut);
                $this->total_amount = $this->number_of_nights * $this->price_per_night;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_in_time' => 'required',
            'check_out' => 'required|date|after:check_in',
            'check_out_time' => 'required',
            'guests_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,gcash,card',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        if ($this->payment_method === 'gcash' && ! $this->payment_proof) {
            $this->addError('payment_proof', 'GCash proof of payment is required.');
            return;
        }

        $hasConflict = Booking::where('room_id', $this->room_id)
            ->whereIn('status', ['pending', 'approved', 'checked_in'])
            ->where(function ($query) {
                $query->whereBetween('check_in_date', [$this->check_in, $this->check_out])
                    ->orWhereBetween('check_out_date', [$this->check_in, $this->check_out])
                    ->orWhere(function ($q) {
                        $q->where('check_in_date', '<=', $this->check_in)
                          ->where('check_out_date', '>=', $this->check_out);
                    });
            })
            ->exists();

        if ($hasConflict) {
            $this->addError('room_id', 'This room is already booked for the selected dates.');
            return;
        }

        $downpayment = $this->total_amount * 0.30;
        $remainingBalance = $this->total_amount - $downpayment;

        $paymentProofPath = null;
        if ($this->payment_proof) {
            $paymentProofPath = $this->payment_proof->store('payment-proofs', 'public');
        }

        Booking::create([
            'user_id' => Auth::id(),
            'room_id' => $this->room_id,
            'check_in_date' => $this->check_in,
            'check_in_time' => $this->check_in_time,
            'check_out_date' => $this->check_out,
            'check_out_time' => $this->check_out_time,
            'guests_count' => $this->guests_count,
            'total_amount' => $this->total_amount,
            'downpayment_amount' => $downpayment,
            'remaining_balance' => $remainingBalance,
            'downpayment_status' => 'unpaid',
            'payment_method' => $this->payment_method,
            'payment_proof' => $paymentProofPath,
            'payment_status' => 'unpaid',
            'special_requests' => $this->special_requests,
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        session()->flash('success', 'Booking created successfully.');

        $this->reset([
            'room_id',
            'check_in',
            'check_in_time',
            'check_out',
            'check_out_time',
            'guests_count',
            'special_requests',
            'payment_method',
            'payment_proof',
            'selectedRoom',
            'price_per_night',
            'number_of_nights',
            'total_amount',
        ]);

        $this->check_in_time = '14:00';
        $this->check_out_time = '12:00';
        $this->guests_count = 1;
        $this->payment_method = 'cash';
        $this->payment_proof = null;

        $this->loadRooms();
    }

}; ?>

<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Book a Room</h1>

    @if(session()->has('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border rounded-2xl shadow-sm p-6 space-y-4">
        <div>
            <label class="block mb-1 font-medium">Room</label>
            <select wire:model="room_id" class="w-full border p-2 rounded">
                <option value="">Select Room</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}">
                        Room {{ $room->room_number }} - {{ $room->roomType->name }} - {{ $room->roomType->capacity }} People - ₱{{ number_format(optional($room->roomType->rates->first())->price_per_night ?? 0, 2) }}
                    </option>
                @endforeach
            </select>
            @error('room_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        @if($selectedRoom)
            <div class="rounded-xl bg-zinc-50 border p-4">
                @if($selectedRoom->image)
                    <img
                        src="{{ asset('storage/' . $selectedRoom->image) }}"
                        alt="Selected Room Image"
                        class="w-full h-56 object-cover rounded-xl mb-4"
                    >
                @endif

                <p><strong>Room Number:</strong> {{ $selectedRoom->room_number }}</p>
                <p><strong>Room Type:</strong> {{ $selectedRoom->roomType->name }}</p>
                <p><strong>Capacity:</strong> {{ $selectedRoom->roomType->capacity }} People</p>
                <p><strong>Beds:</strong> {{ $selectedRoom->roomType->beds }} Bed{{ $selectedRoom->roomType->beds > 1 ? 's' : '' }}</p>

                @if($selectedRoom->roomType->description)
                    <p><strong>Details:</strong> {{ $selectedRoom->roomType->description }}</p>
                @endif

                <p><strong>Price Per Night:</strong> ₱{{ number_format($price_per_night, 2) }}</p>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Check In Date</label>
                <input type="date" wire:model="check_in" class="w-full border p-2 rounded">
                @error('check_in') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Check In Time</label>
                <input type="time" wire:model="check_in_time" class="w-full border p-2 rounded">
                @error('check_in_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Check Out Date</label>
                <input type="date" wire:model="check_out" class="w-full border p-2 rounded">
                @error('check_out') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Check Out Time</label>
                <input type="time" wire:model="check_out_time" class="w-full border p-2 rounded">
                @error('check_out_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block mb-1 font-medium">Guests Count</label>
            <input type="number" wire:model="guests_count" min="1" class="w-full border p-2 rounded">
            @error('guests_count') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1 font-medium">Payment Method</label>
            <select wire:model="payment_method" class="w-full border p-2 rounded">
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
                <option value="card">Card</option>
            </select>
            @error('payment_method') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        @if($payment_method === 'gcash')
            <div>
                <label class="block mb-1 font-medium">Upload GCash Proof</label>
                <input type="file" wire:model="payment_proof" class="w-full border p-2 rounded">
                @error('payment_proof') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

                <div wire:loading wire:target="payment_proof" class="text-blue-600 text-sm mt-2">
                    Uploading proof...
                </div>

                @if($payment_proof)
                    <div class="mt-3">
                        <img src="{{ $payment_proof->temporaryUrl() }}" class="h-40 rounded-xl object-cover">
                    </div>
                @endif
            </div>
        @endif

        <div>
            <label class="block mb-1 font-medium">Special Requests</label>
            <textarea wire:model="special_requests" rows="4" class="w-full border p-2 rounded" placeholder="Extra pillow, near window, late check-in, etc."></textarea>
            @error('special_requests') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl bg-blue-50 border p-4 space-y-1">
            @if($check_in && \Carbon\Carbon::parse($check_in)->isFuture())
                <p>
                    <strong>Booking Type:</strong>
                    <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">
                        ADVANCE BOOKING
                    </span>
                </p>
            @endif

            <p><strong>Price Per Night:</strong> ₱{{ number_format($price_per_night, 2) }}</p>
            <p><strong>Number of Nights:</strong> {{ $number_of_nights }}</p>
            <p><strong>Total Amount:</strong> ₱{{ number_format($total_amount, 2) }}</p>
            <p><strong>Downpayment (30%):</strong> ₱{{ number_format($total_amount * 0.30, 2) }}</p>
            <p><strong>Remaining Balance:</strong> ₱{{ number_format($total_amount - ($total_amount * 0.30), 2) }}</p>

            @if($payment_method === 'gcash')
                <p class="text-sm text-blue-700">
                    GCash bookings require proof of payment before admin approval.
                </p>
            @endif
        </div>

        <button
            wire:click="save"
            wire:loading.attr="disabled"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="save">Book Now</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>
</div>