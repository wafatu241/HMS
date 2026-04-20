<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('requested_check_out_date')->nullable()->after('check_out_date');
            $table->time('requested_check_out_time')->nullable()->after('check_out_time');
            $table->string('extension_status')->nullable()->after('payment_status'); // pending, approved, rejected
            $table->decimal('extension_fee', 10, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'requested_check_out_date',
                'requested_check_out_time',
                'extension_status',
                'extension_fee',
            ]);
        });
    }
};