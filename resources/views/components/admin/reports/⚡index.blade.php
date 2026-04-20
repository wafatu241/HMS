<?php

use Livewire\Component;
use App\Models\Booking;

new class extends Component {

    public $date_from = '';
    public $date_to = '';

    public $total_sales = 0;
    public $total_downpayments = 0;
    public $total_remaining_balance = 0;
    public $paid_bookings = 0;
    public $monthly_sales = [];
    public $sales_by_payment_method = [];
    public $sales_by_room_type = [];
    public $recent_sales = [];

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');

        $this->loadReport();
    }

    public function updatedDateFrom()
    {
        $this->loadReport();
    }

    public function updatedDateTo()
    {
        $this->loadReport();
    }

    public function loadReport()
    {
        $baseQuery = Booking::with(['user', 'room.roomType'])
            ->when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to));

        $this->total_sales = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $this->total_downpayments = (clone $baseQuery)
            ->where('downpayment_status', 'paid')
            ->sum('downpayment_amount');

        $this->total_remaining_balance = (clone $baseQuery)
            ->where('payment_status', 'unpaid')
            ->sum('remaining_balance');

        $this->paid_bookings = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->count();

        $this->monthly_sales = Booking::when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_sales')
            ->selectRaw('SUM(CASE WHEN downpayment_status = "paid" THEN downpayment_amount ELSE 0 END) as downpayments_collected')
            ->selectRaw('COUNT(*) as bookings_count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $this->sales_by_payment_method = Booking::when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->select('payment_method')
            ->selectRaw('COUNT(*) as bookings_count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('SUM(downpayment_amount) as downpayments')
            ->groupBy('payment_method')
            ->get();

        $this->sales_by_room_type = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->when($this->date_from, fn ($q) => $q->whereDate('bookings.created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('bookings.created_at', '<=', $this->date_to))
            ->select('room_types.name as room_type')
            ->selectRaw('COUNT(bookings.id) as bookings_count')
            ->selectRaw('SUM(bookings.total_amount) as total_amount')
            ->groupBy('room_types.name')
            ->get();

        $this->recent_sales = (clone $baseQuery)
            ->latest()
            ->get();
    }

}; ?>

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Sales Reports</h1>

    <div class="rounded-2xl border bg-white p-5 shadow-sm mb-6">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-1 font-medium">Date From</label>
                <input type="date" wire:model="date_from" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block mb-1 font-medium">Date To</label>
                <input type="date" wire:model="date_to" class="w-full border p-2 rounded">
            </div>

            <div class="flex items-end">
                <a
                    href="{{ route('admin.reports.pdf', ['date_from' => $date_from, 'date_to' => $date_to]) }}"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
                >
                    Save as PDF
                </a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Total Sales</p>
            <h2 class="text-2xl font-bold mt-2">₱{{ number_format($total_sales, 2) }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Downpayments Collected</p>
            <h2 class="text-2xl font-bold mt-2">₱{{ number_format($total_downpayments, 2) }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Unpaid Remaining Balance</p>
            <h2 class="text-2xl font-bold mt-2">₱{{ number_format($total_remaining_balance, 2) }}</h2>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Paid Bookings</p>
            <h2 class="text-2xl font-bold mt-2">{{ $paid_bookings }}</h2>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold mb-4">Sales by Payment Method</h2>
            <div class="space-y-3">
                @forelse($sales_by_payment_method as $item)
                    <div class="flex items-center justify-between border-b pb-2">
                        <span>{{ strtoupper($item->payment_method) }}</span>
                        <span class="font-semibold">₱{{ number_format($item->total_amount, 2) }}</span>
                    </div>
                @empty
                    <p>No payment data.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold mb-4">Sales by Room Type</h2>
            <div class="space-y-3">
                @forelse($sales_by_room_type as $item)
                    <div class="flex items-center justify-between border-b pb-2">
                        <span>{{ $item->room_type }}</span>
                        <span class="font-semibold">₱{{ number_format($item->total_amount, 2) }}</span>
                    </div>
                @empty
                    <p>No room type sales data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm mb-6">
        <h2 class="text-xl font-semibold mb-4">Monthly Sales</h2>

        <div class="space-y-3">
            @forelse($monthly_sales as $sale)
                <div class="flex items-center justify-between border-b pb-2">
                    <div>
                        <p class="font-medium">{{ $sale->month }}</p>
                        <p class="text-sm text-zinc-500">Bookings: {{ $sale->bookings_count }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">Paid Sales: ₱{{ number_format($sale->paid_sales, 2) }}</p>
                        <p class="text-sm text-zinc-500">Downpayments: ₱{{ number_format($sale->downpayments_collected, 2) }}</p>
                    </div>
                </div>
            @empty
                <p>No monthly sales data.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Detailed Booking Sales</h2>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b bg-zinc-50">
                        <th class="text-left p-3">Date</th>
                        <th class="text-left p-3">Guest</th>
                        <th class="text-left p-3">Room</th>
                        <th class="text-left p-3">Type</th>
                        <th class="text-left p-3">Total</th>
                        <th class="text-left p-3">Downpayment</th>
                        <th class="text-left p-3">Remaining</th>
                        <th class="text-left p-3">Payment</th>
                        <th class="text-left p-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_sales as $booking)
                        <tr class="border-b">
                            <td class="p-3">{{ $booking->created_at?->format('Y-m-d') }}</td>
                            <td class="p-3">{{ $booking->user->name ?? '' }}</td>
                            <td class="p-3">{{ $booking->room->room_number ?? '' }}</td>
                            <td class="p-3">{{ $booking->room->roomType->name ?? '' }}</td>
                            <td class="p-3">₱{{ number_format($booking->total_amount, 2) }}</td>
                            <td class="p-3">₱{{ number_format($booking->downpayment_amount, 2) }}</td>
                            <td class="p-3">₱{{ number_format($booking->remaining_balance, 2) }}</td>
                            <td class="p-3">{{ strtoupper($booking->payment_method) }}</td>
                            <td class="p-3">{{ strtoupper($booking->payment_status ?? 'unpaid') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-zinc-500">No sales found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>