<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    protected $fillable = [
        'room_type_id',
        'price_per_night',
        'extra_guest_fee',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}