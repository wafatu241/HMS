<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
 protected $fillable = [
    'user_id',
    'room_id',
    'check_in_date',
    'check_in_time',
    'check_out_date',
    'check_out_time',
    'requested_check_out_date',
    'requested_check_out_time',
    'guests_count',
    'total_amount',
    'downpayment_amount',
    'downpayment_status',
    'downpayment_paid_at',
    'remaining_balance',
    'payment_method',
    'payment_proof',
    'payment_status',
    'paid_at',
    'expires_at',
    'extension_status',
    'extension_fee',
    'status',
    'special_requests',
];
   protected $casts = [
    'check_in_date' => 'date',
    'check_out_date' => 'date',
    'requested_check_out_date' => 'date',
    'paid_at' => 'datetime',
    'downpayment_paid_at' => 'datetime',
    'expires_at' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}