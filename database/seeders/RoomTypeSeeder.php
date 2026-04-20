<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        RoomType::create([
            'name' => 'Standard',
            'capacity' => 2,
            'beds' => 1,
        ]);

        RoomType::create([
            'name' => 'Deluxe',
            'capacity' => 3,
            'beds' => 2,
        ]);
    }
}