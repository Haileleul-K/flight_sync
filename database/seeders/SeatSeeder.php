<?php

namespace Database\Seeders;

use App\Models\Seat;
use Illuminate\Database\Seeder;
use App\Models\AircraftModel;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aircraft1 = AircraftModel::where('model', 'A139')->first();
        $aircraft2 = AircraftModel::where('model', 'AH64')->first();

        if ($aircraft1) {
            Seat::create([
                'name' => json_encode(['Back Seat', 'Front Seat']),
                'aircraft_id' => $aircraft1->id
            ]);
        }

        if ($aircraft2) {
            Seat::create([
                'name' => json_encode(['Back Seat']),
                'aircraft_id' => $aircraft2->id
            ]);
        }
    }
}
