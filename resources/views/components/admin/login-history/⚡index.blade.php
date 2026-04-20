<?php

use Livewire\Component;
use App\Models\LoginHistory;

new class extends Component {

    public $histories = [];

    public function mount()
    {
        $this->histories = LoginHistory::with('user')->latest('logged_in_at')->get();
    }

}; ?>

<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Login History</h1>

    <div class="space-y-4">
        @forelse($histories as $history)
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <p><strong>User:</strong> {{ $history->user->name ?? 'Unknown' }}</p>
                <p><strong>Email:</strong> {{ $history->user->email ?? 'N/A' }}</p>
                <p><strong>IP Address:</strong> {{ $history->ip_address }}</p>
                <p><strong>Login Time:</strong> {{ $history->logged_in_at?->format('F d, Y h:i A') }}</p>
                <p><strong>User Agent:</strong> {{ $history->user_agent }}</p>
            </div>
        @empty
            <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                No login history found.
            </div>
        @endforelse
    </div>
</div>