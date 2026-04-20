<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        Room::create([
            'room_type_id' => 1,
            'room_number' => '101',
            'floor' => '1',
            'status' => 'available',
        ]);

        Room::create([
            'room_type_id' => 2,
            'room_number' => '102',
            'floor' => '1',
            'status' => 'available',
        ]);
    }
}