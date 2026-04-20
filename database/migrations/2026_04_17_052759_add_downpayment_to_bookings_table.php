<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('downpayment_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('downpayment_status')->default('unpaid')->after('downpayment_amount'); // unpaid / paid
            $table->timestamp('downpayment_paid_at')->nullable()->after('downpayment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'downpayment_amount',
                'downpayment_status',
                'downpayment_paid_at',
            ]);
        });
    }
};