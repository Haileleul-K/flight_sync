<?php

namespace Database\Seeders;

use App\Models\AircraftModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AircraftModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AircraftModel::create([
            'seats'=> json_encode(['Back Seat']),
            'model' => 'A139',
        ]);

        AircraftModel::create([
            'seats'=> json_encode(['Back Seat', 'Front Seat']),
            'model' => 'AH64',
        ]);

        AircraftModel::create([
            'seats'=> json_encode(['Back Seat', 'Front Seat']),
            'model' => 'BELL 407',
        ]);

        AircraftModel::create([
            'seats'=> json_encode(['Back Seat', 'Front Seat']),

            'model' => 'CH-47',
        ]);

        AircraftModel::create([
            'seats'=> json_encode([ 'Front Seat']),

            'model' => 'Cessna 172',
        ]);

        AircraftModel::create([
            'seats'=> json_encode([]),
            'model' => 'EH-60',
        ]);
    }
}
