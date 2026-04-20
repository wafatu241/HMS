<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;


Route::livewire('/', 'rooms.room-list')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/booking', 'bookings.booking-form')->name('booking.form');
    Route::livewire('/my-bookings', 'bookings.my-bookings')->name('my-bookings');

    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');

    Route::put('/profile', function (Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = auth()->user();
        $user->update($request->only('name', 'email'));

        return back()->with('success', 'Profile updated.');
    })->name('profile.update');

    Route::put('/profile/password', function (Request $request) {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Wrong password',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated.');
    })->name('profile.password');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::livewire('/rooms', 'admin.rooms.index')->name('rooms.index');
    Route::livewire('/room-types', 'admin.room-types.index')->name('room-types.index');
    Route::livewire('/rates', 'admin.rates.index')->name('rates.index');
    Route::livewire('/bookings', 'admin.bookings.index')->name('bookings.index');
    Route::livewire('/login-history', 'admin.login-history.index')->name('login-history.index');
    Route::livewire('/audit-logs', 'admin.audit-logs.index')->name('audit-logs.index');
    Route::livewire('/reports', 'admin.reports.index')->name('reports.index');

    Route::get('/reports/pdf', function (Request $request) {
    $dateFrom = $request->date_from;
    $dateTo = $request->date_to;

    $query = Booking::with(['user', 'room.roomType'])
        ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
        ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));

    $sales = $query->latest()->get();

    $summary = [
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'total_sales' => (clone $query)->where('payment_status', 'paid')->sum('total_amount'),
        'total_downpayments' => (clone $query)->where('downpayment_status', 'paid')->sum('downpayment_amount'),
        'total_remaining_balance' => (clone $query)->where('payment_status', 'unpaid')->sum('remaining_balance'),
        'paid_bookings' => (clone $query)->where('payment_status', 'paid')->count(),
        'sales' => $sales,
    ];

    $pdf = Pdf::loadView('pdf.sales-report', $summary)
        ->setPaper('a4', 'portrait');

    return $pdf->download('sales-report.pdf');
})->name('reports.pdf');
});