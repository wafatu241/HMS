<?php

use Livewire\Component;
use App\Models\AuditLog;

new class extends Component {

    public $logs = [];

    public function mount()
    {
        $this->logs = AuditLog::with('user')->latest()->get();
    }

}; ?>

<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Audit Logs</h1>

    <div class="space-y-4">
        @forelse($logs as $log)
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <p><strong>User:</strong> {{ $log->user->name ?? 'System' }}</p>
                <p><strong>Action:</strong> {{ $log->action }}</p>
                <p><strong>Module:</strong> {{ $log->module }}</p>
                <p><strong>Description:</strong> {{ $log->description }}</p>
                <p><strong>Record ID:</strong> {{ $log->record_id }}</p>
                <p><strong>Date:</strong> {{ $log->created_at?->format('F d, Y h:i A') }}</p>
            </div>
        @empty
            <div class="rounded-2xl border bg-white p-8 text-center shadow-sm">
                No audit logs found.
            </div>
        @endforelse
    </div>
</div>