<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomRate;

class RoomRateSeeder extends Seeder
{
    public function run(): void
    {
        RoomRate::create([
            'room_type_id' => 1,
            'price_per_night' => 1500,
        ]);

        RoomRate::create([
            'room_type_id' => 2,
            'price_per_night' => 2500,
        ]);
    }
}