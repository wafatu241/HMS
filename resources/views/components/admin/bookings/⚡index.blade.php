<?php

use Livewire\Component;
use App\Models\Booking;
use Carbon\Carbon;

new class extends Component {

    public $bookings;

    public function earlyCheckOut($id)
{
    $booking = Booking::findOrFail($id);

    if ($booking->payment_status !== 'paid') {
        session()->flash('error', 'Cannot early check out. Booking is still unpaid.');
        return;
    }

    $booking->update([
        'status' => 'checked_out',
    ]);

    $booking->room->update([
        'status' => 'available',
    ]);

    session()->flash('success', 'Guest checked out early successfully.');
    $this->loadBookings();
}

    public function markDownpaymentPaid($id)
{
    $booking = Booking::findOrFail($id);

    $booking->update([
        'downpayment_status' => 'paid',
        'downpayment_paid_at' => now(),
    ]);

    session()->flash('success', 'Downpayment marked as paid.');
    $this->loadBookings();
}

    public function mount()
    {
        $this->loadBookings();
    }

    public function loadBookings()
    {
        $this->bookings = Booking::with(['user', 'room.roomType'])
            ->latest()
            ->get();
    }

    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'approved']);
        $this->loadBookings();
    }

    public function reject($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'rejected']);
        $this->loadBookings();
    }

    public function markPaid($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        session()->flash('success', 'Payment marked as paid.');
        $this->loadBookings();
    }

    public function checkIn($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'checked_in']);
        $booking->room->update(['status' => 'occupied']);
        $this->loadBookings();
    }

    public function checkOut($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->payment_status !== 'paid') {
            session()->flash('error', 'Cannot check out. Booking is still unpaid.');
            return;
        }

        $booking->update(['status' => 'checked_out']);
        $booking->room->update(['status' => 'available']);

        $this->loadBookings();
    }

    public function approveExtension($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->requested_check_out_date) {
            $booking->update([
                'check_out_date' => $booking->requested_check_out_date,
                'check_out_time' => $booking->requested_check_out_time,
                'total_amount' => $booking->total_amount + $booking->extension_fee,
                'extension_status' => 'approved',
                'requested_check_out_date' => null,
                'requested_check_out_time' => null,
            ]);
        }

        session()->flash('success', 'Extension approved successfully.');
        $this->loadBookings();
    }

    public function rejectExtension($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            'extension_status' => 'rejected',
            'requested_check_out_date' => null,
            'requested_check_out_time' => null,
            'extension_fee' => 0,
        ]);

        session()->flash('success', 'Extension rejected.');
        $this->loadBookings();
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

    public function extensionBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

}; ?>

<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Manage Bookings</h1>

    @if(session()->has('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-100 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($bookings as $booking)
            <div class="rounded-2xl border bg-white p-5 shadow-sm">

                @if($booking->room->image)
                    <img
                        src="{{ asset('storage/' . $booking->room->image) }}"
                        alt="Room Image"
                        class="w-full h-56 object-cover rounded-xl mb-4"
                    >
                @else
                    <div class="w-full h-56 bg-zinc-100 rounded-xl mb-4 flex items-center justify-center text-zinc-500">
                        No Image
                    </div>
                @endif

                <div class="rounded-xl border bg-zinc-50 p-4 mb-4">
                    <h2 class="text-lg font-semibold mb-3">Booker Information</h2>

                    <div class="grid md:grid-cols-2 gap-2 text-sm">
                        <p><strong>Full Name:</strong> {{ $booking->user->name }}</p>
                        <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                        <p><strong>Role:</strong> {{ ucfirst($booking->user->role ?? 'user') }}</p>
                        <p><strong>User ID:</strong> {{ $booking->user->id }}</p>
                        <p><strong>Booked At:</strong> {{ $booking->created_at?->format('F d, Y h:i A') }}</p>

                        <p>
                            <strong>Payment Method:</strong>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium {{ $this->paymentBadgeClass($booking->payment_method) }}">
                                {{ $this->paymentLabel($booking->payment_method) }}
                            </span>
                        </p>

                        <p>
                            <strong>Payment Status:</strong>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium {{ $this->paymentStatusBadgeClass($booking->payment_status) }}">
                                {{ strtoupper($booking->payment_status ?? 'unpaid') }}
                            </span>
                        </p>

                        @if($booking->paid_at)
                            <p><strong>Paid At:</strong> {{ $booking->paid_at?->format('F d, Y h:i A') }}</p>
                        @endif
                    </div>
                </div>

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
                        <p><strong>Guests:</strong> {{ $booking->guests_count }}</p>
                        <p><strong>Total Amount:</strong> ₱{{ number_format($booking->total_amount, 2) }}</p>

                        {{-- ✅ ADD HERE --}}


                        <p>
                            <strong>Downpayment Status:</strong>
                            <span class="px-2 py-1 rounded text-sm
                                {{ $booking->downpayment_status === 'paid'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700' }}">
                                {{ strtoupper($booking->downpayment_status) }}
                            </span>
                        </p>
                    </div>
                </div>

                @if($booking->room->roomType->description || $booking->special_requests)
                    <div class="mt-4 border-t pt-4 space-y-2">
                        @if($booking->room->roomType->description)
                            <p><strong>Room Details:</strong> {{ $booking->room->roomType->description }}</p>
                        @endif

                        @if($booking->special_requests)
                            <p><strong>Special Requests:</strong> {{ $booking->special_requests }}</p>
                        @endif
                    </div>
                @endif

                @if($booking->extension_status)
                    <div class="mt-4 border-t pt-4 space-y-2">
                        <p>
                            <strong>Extension Status:</strong>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium {{ $this->extensionBadgeClass($booking->extension_status) }}">
                                {{ strtoupper($booking->extension_status) }}
                            </span>
                        </p>

                        @if($booking->extension_fee > 0)
                            <p><strong>Extension Fee:</strong> ₱{{ number_format($booking->extension_fee, 2) }}</p>
                        @endif
                    </div>
                @endif

                @if($booking->extension_status === 'pending')
                    <div class="mt-4 border-t pt-4">
                        <h3 class="text-lg font-semibold mb-2">Extension Request</h3>

                        <p><strong>Requested New Check Out Date:</strong>
                            {{ $booking->requested_check_out_date ? \Carbon\Carbon::parse($booking->requested_check_out_date)->format('F d, Y') : '' }}
                        </p>

                        <p><strong>Requested New Check Out Time:</strong>
                            {{ $booking->requested_check_out_time ? \Carbon\Carbon::parse($booking->requested_check_out_time)->format('h:i A') : '' }}
                        </p>

                        <p><strong>Extension Fee:</strong>
                            ₱{{ number_format($booking->extension_fee, 2) }}
                        </p>

                        <div class="mt-3 flex gap-2">
                            <button
                                wire:click="approveExtension({{ $booking->id }})"
                                class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 transition"
                            >
                                Approve Extension
                            </button>

                            <button
                                wire:click="rejectExtension({{ $booking->id }})"
                                class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition"
                            >
                                Reject Extension
                            </button>
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $this->badgeClass($booking->status) }}">
                        {{ strtoupper(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>


                    <div class="mt-4 flex flex-wrap gap-2">

                            {{-- ✅ ADD THIS FIRST --}}
                            @if($booking->downpayment_status !== 'paid')
                                <button
                                    wire:click="markDownpaymentPaid({{ $booking->id }})"
                                    class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700 transition"
                                >
                                    Mark Downpayment Paid
                                </button>
                            @endif


                        </div>
                        @if ($booking->status === 'pending')
                        <button
                            wire:click="approve({{ $booking->id }})"
                            wire:loading.attr="disabled"
                            class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 transition disabled:opacity-50"
                        >
                            Approve
                        </button>

                        <button
                            wire:click="reject({{ $booking->id }})"
                            wire:loading.attr="disabled"
                            class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition disabled:opacity-50"
                        >
                            Reject
                        </button>
                    @endif

                    @if (in_array($booking->status, ['approved', 'checked_in']) && $booking->payment_status !== 'paid')
                        <button
                            wire:click="markPaid({{ $booking->id }})"
                            wire:loading.attr="disabled"
                            class="bg-emerald-600 text-white px-3 py-2 rounded hover:bg-emerald-700 transition disabled:opacity-50"
                        >
                            Mark as Paid
                        </button>
                    @endif

                    @if ($booking->status === 'checked_in')
                    <button
                        wire:click="checkOut({{ $booking->id }})"
                        wire:loading.attr="disabled"
                        class="bg-purple-600 text-white px-3 py-2 rounded hover:bg-purple-700 transition disabled:opacity-50"
                    >
                        Check Out
                    </button>

                    <button
                        wire:click="earlyCheckOut({{ $booking->id }})"
                        wire:loading.attr="disabled"
                        class="bg-orange-600 text-white px-3 py-2 rounded hover:bg-orange-700 transition disabled:opacity-50"
                    >
                        Early Check Out
                    </button>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                <h2 class="text-xl font-semibold mb-2">No bookings found</h2>
                <p class="text-zinc-600">Bookings will appear here once users reserve a room.</p>
            </div>
        @endforelse
    </div>
</div>