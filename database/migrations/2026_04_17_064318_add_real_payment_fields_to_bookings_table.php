<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_method');
            $table->decimal('remaining_balance', 10, 2)->default(0)->after('downpayment_amount');
            $table->timestamp('expires_at')->nullable()->after('downpayment_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof',
                'remaining_balance',
                'expires_at',
            ]);
        });
    }
};