<?php

use Livewire\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {

    public $bookings;

    public $extend_booking_id = null;
    public $new_check_out_date = '';
    public $new_check_out_time = '12:00';
    public $extension_fee_preview = 0;

    public function mount()
    {
        $this->loadBookings();
    }

    public function loadBookings()
    {
        $this->bookings = Booking::with('room.roomType.rates')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function cancelBooking($id)
    {
        $booking = Booking::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $booking->update([
            'status' => 'cancelled',
        ]);

        $this->loadBookings();

        session()->flash('success', 'Booking cancelled successfully.');
    }

    public function openExtendForm($id)
    {
        $booking = Booking::where('user_id', Auth::id())
            ->where('id', $id)
            ->whereIn('status', ['approved', 'checked_in'])
            ->firstOrFail();

        $this->extend_booking_id = $booking->id;
        $this->new_check_out_date = $booking->check_out_date?->format('Y-m-d');
        $this->new_check_out_time = $booking->check_out_time ?: '12:00';
        $this->extension_fee_preview = 0;
        $this->resetErrorBag();
    }

    public function updatedNewCheckOutDate()
    {
        $this->calculateExtensionFeePreview();
    }

    public function updatedNewCheckOutTime()
    {
        $this->calculateExtensionFeePreview();
    }

    public function calculateExtensionFeePreview()
    {
        $this->extension_fee_preview = 0;

        if (! $this->extend_booking_id || ! $this->new_check_out_date) {
            return;
        }

        $booking = Booking::with('room.roomType.rates')->find($this->extend_booking_id);
        if (! $booking) {
            return;
        }

        $pricePerNight = optional($booking->room->roomType->rates->first())->price_per_night ?? 0;

        $oldNights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
        $newNights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($this->new_check_out_date));

        if ($newNights > $oldNights) {
            $this->extension_fee_preview = ($newNights - $oldNights) * $pricePerNight;
        }
    }

    public function saveExtension()
    {
        $this->validate([
            'new_check_out_date' => 'required|date',
            'new_check_out_time' => 'required',
        ]);

        $booking = Booking::with('room.roomType.rates')
            ->where('user_id', Auth::id())
            ->where('id', $this->extend_booking_id)
            ->whereIn('status', ['approved', 'checked_in'])
            ->firstOrFail();

        if ($this->new_check_out_date <= $booking->check_out_date->format('Y-m-d')) {
            $this->addError('new_check_out_date', 'New check-out date must be later than the current check-out date.');
            return;
        }

        $hasConflict = Booking::where('room_id', $booking->room_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', ['pending', 'approved', 'checked_in'])
            ->where(function ($query) use ($booking) {
                $query->whereBetween('check_in_date', [$booking->check_in_date, $this->new_check_out_date])
                    ->orWhereBetween('check_out_date', [$booking->check_in_date, $this->new_check_out_date])
                    ->orWhere(function ($q) use ($booking) {
                        $q->where('check_in_date', '<=', $booking->check_in_date)
                          ->where('check_out_date', '>=', $this->new_check_out_date);
                    });
            })
            ->exists();

        if ($hasConflict) {
            $this->addError('new_check_out_date', 'Cannot extend because the room is already booked on the selected extended date.');
            return;
        }

        $pricePerNight = optional($booking->room->roomType->rates->first())->price_per_night ?? 0;
        $oldNights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
        $newNights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($this->new_check_out_date));
        $extensionFee = max(0, ($newNights - $oldNights) * $pricePerNight);

        $booking->update([
            'requested_check_out_date' => $this->new_check_out_date,
            'requested_check_out_time' => $this->new_check_out_time,
            'extension_status' => 'pending',
            'extension_fee' => $extensionFee,
        ]);

        $this->extend_booking_id = null;
        $this->new_check_out_date = '';
        $this->new_check_out_time = '12:00';
        $this->extension_fee_preview = 0;

        $this->loadBookings();

        session()->flash('success', 'Extension request submitted for admin approval.');
    }

    public function cancelExtension()
    {
        $this->extend_booking_id = null;
        $this->new_check_out_date = '';
        $this->new_check_out_time = '12:00';
        $this->extension_fee_preview = 0;
        $this->resetErrorBag();
    }

    public function badgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'checked_in' => 'bg-blue-100 text-blue-700',
            'checked_out' => 'bg-zinc-100 text-zinc-700',
            'rejected' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function extensionBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function paymentLabel($paymentMethod)
    {
        return match ($paymentMethod) {
            'gcash' => 'GCash',
            'card' => 'Card',
            default => 'Cash',
        };
    }

    public function paymentBadgeClass($paymentMethod)
    {
        return match ($paymentMethod) {
            'gcash' => 'bg-blue-100 text-blue-700',
            'card' => 'bg-purple-100 text-purple-700',
            default => 'bg-green-100 text-green-700',
        };
    }

    public function paymentStatusBadgeClass($paymentStatus)
    {
        return match ($paymentStatus) {
            'paid' => 'bg-green-100 text-green-700',
            default => 'bg-red-100 text-red-700',
        };
    }

}; ?>

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">My Bookings</h1>

    <div class="rounded-2xl border bg-white p-5 shadow-sm mb-6">
        <h2 class="text-xl font-semibold mb-3">Account Information</h2>

        <div class="grid md:grid-cols-2 gap-2 text-sm">
            <p><strong>Full Name:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>Role:</strong> {{ ucfirst(auth()->user()->role ?? 'user') }}</p>
            <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($bookings as $booking)
            <div class="border p-5 rounded-2xl bg-white shadow-sm">
                @if($booking->room->image)
                    <img
                        src="{{ asset('storage/' . $booking->room->image) }}"
                        alt="Booked Room Image"
                        class="w-full h-56 object-cover rounded-xl mb-4"
                    >
                @else
                    <div class="w-full h-56 bg-zinc-100 rounded-xl mb-4 flex items-center justify-center text-zinc-500">
                        No Image
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <p><strong>Room:</strong> {{ $booking->room->room_number }}</p>
                        <p><strong>Type:</strong> {{ $booking->room->roomType->name }}</p>
                        <p><strong>Capacity:</strong> {{ $booking->room->roomType->capacity }} People</p>
                        <p><strong>Beds:</strong> {{ $booking->room->roomType->beds }} Bed{{ $booking->room->roomType->beds > 1 ? 's' : '' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p><strong>Check In Date:</strong> {{ $booking->check_in_date ? \Carbon\Carbon::parse($booking->check_in_date)->format('F d, Y') : '' }}</p>
                        <p><strong>Check In Time:</strong> {{ $booking->check_in_time ? \Carbon\Carbon::parse($booking->check_in_time)->format('h:i A') : '' }}</p>
                        <p><strong>Check Out Date:</strong> {{ $booking->check_out_date ? \Carbon\Carbon::parse($booking->check_out_date)->format('F d, Y') : '' }}</p>
                        <p><strong>Check Out Time:</strong> {{ $booking->check_out_time ? \Carbon\Carbon::parse($booking->check_out_time)->format('h:i A') : '' }}</p>
                    </div>
                </div>

                <div class="mt-4 border-t pt-4 space-y-1">
                    <p><strong>Guests:</strong> {{ $booking->guests_count }}</p>
                    <p><strong>Total Amount:</strong> ₱{{ number_format($booking->total_amount, 2) }}</p>
                    <p><strong>Downpayment Required:</strong> ₱{{ number_format($booking->downpayment_amount, 2) }}</p>
                    <p><strong>Remaining Balance:</strong> ₱{{ number_format($booking->remaining_balance, 2) }}</p>

                    <p>
                        <strong>Downpayment Status:</strong>
                        <span class="px-2 py-1 rounded text-sm {{ $booking->downpayment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ strtoupper($booking->downpayment_status) }}
                        </span>
                    </p>

                    @if($booking->downpayment_paid_at)
                        <p><strong>Downpayment Paid At:</strong> {{ $booking->downpayment_paid_at?->format('F d, Y h:i A') }}</p>
                    @endif

                    <p>
                        <strong>Full Payment Status:</strong>
                        <span class="px-2 py-1 rounded text-sm {{ $this->paymentStatusBadgeClass($booking->payment_status) }}">
                            {{ strtoupper($booking->payment_status ?? 'unpaid') }}
                        </span>
                    </p>

                    <p>
                        <strong>Payment Method:</strong>
                        <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium {{ $this->paymentBadgeClass($booking->payment_method) }}">
                            {{ $this->paymentLabel($booking->payment_method) }}
                        </span>
                    </p>

                    @if($booking->payment_proof)
                        <div class="mt-3">
                            <p class="font-medium mb-2">Payment Proof:</p>
                            <img src="{{ asset('storage/' . $booking->payment_proof) }}" class="h-40 rounded-xl object-cover">
                        </div>
                    @endif

                    @if($booking->expires_at && $booking->downpayment_status !== 'paid')
                        <p><strong>Payment Deadline:</strong> {{ $booking->expires_at?->format('F d, Y h:i A') }}</p>
                    @endif

                    @if($booking->extension_fee > 0)
                        <p><strong>Extension Fee:</strong> ₱{{ number_format($booking->extension_fee, 2) }}</p>
                    @endif

                    @if($booking->extension_status)
                        <p>
                            <strong>Extension Status:</strong>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium {{ $this->extensionBadgeClass($booking->extension_status) }}">
                                {{ strtoupper($booking->extension_status) }}
                            </span>
                        </p>
                    @endif

                    @if($booking->requested_check_out_date)
                        <p><strong>Requested New Check Out Date:</strong> {{ \Carbon\Carbon::parse($booking->requested_check_out_date)->format('F d, Y') }}</p>
                    @endif

                    @if($booking->requested_check_out_time)
                        <p><strong>Requested New Check Out Time:</strong> {{ \Carbon\Carbon::parse($booking->requested_check_out_time)->format('h:i A') }}</p>
                    @endif

                    @if($booking->special_requests)
                        <p><strong>Special Requests:</strong> {{ $booking->special_requests }}</p>
                    @endif

                    @if($booking->room->roomType->description)
                        <p><strong>Room Details:</strong> {{ $booking->room->roomType->description }}</p>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $this->badgeClass($booking->status) }}">
                        {{ strtoupper(str_replace('_', ' ', $booking->status)) }}
                    </span>

                    @if(in_array($booking->status, ['approved', 'checked_in']) && $booking->extension_status !== 'pending')
                        <button
                            wire:click="openExtendForm({{ $booking->id }})"
                            class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700 transition"
                        >
                            Extend Room
                        </button>
                    @endif

                    @if($booking->status === 'pending')
                        <button
                            wire:click="cancelBooking({{ $booking->id }})"
                            wire:loading.attr="disabled"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="cancelBooking({{ $booking->id }})">Cancel Booking</span>
                            <span wire:loading wire:target="cancelBooking({{ $booking->id }})">Cancelling...</span>
                        </button>
                    @endif
                </div>

                @if($extend_booking_id === $booking->id)
                    <div class="mt-4 border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">Extend Booking</h3>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 font-medium">New Check Out Date</label>
                                <input type="date" wire:model="new_check_out_date" class="w-full border p-2 rounded">
                                @error('new_check_out_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">New Check Out Time</label>
                                <input type="time" wire:model="new_check_out_time" class="w-full border p-2 rounded">
                                @error('new_check_out_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl bg-blue-50 border p-4">
                            <p><strong>Extension Fee:</strong> ₱{{ number_format($extension_fee_preview, 2) }}</p>
                            <p class="text-sm text-zinc-600 mt-1">This extension request will still need admin approval.</p>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button
                                wire:click="saveExtension"
                                wire:loading.attr="disabled"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="saveExtension">Submit Extension</span>
                                <span wire:loading wire:target="saveExtension">Saving...</span>
                            </button>

                            <button
                                wire:click="cancelExtension"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                <h2 class="text-xl font-semibold mb-2">No bookings found</h2>
                <p class="text-zinc-600">Your room reservations will appear here once you create one.</p>
            </div>
        @endforelse
    </div>
</div>