<?php

namespace Database\Seeders;

use App\Models\SimulatorTag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SimulatorTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SimulatorTag::create([
            'name' => 'AMC',
        ]);

        SimulatorTag::create([
            'name' => 'Assault',
        ]);

        SimulatorTag::create([
            'name' => 'ATM Flight',
        ]);

        SimulatorTag::create([
            'name' => 'Bambi Bucket',
        ]);

        SimulatorTag::create([
            'name' => 'CMF',
        ]);

        SimulatorTag::create([
            'name' => 'Cross Country',
        ]);

        SimulatorTag::create([
            'name' => 'Double Bag',
        ]);

        SimulatorTag::create([
            'name' => 'Extended Fuel System (CFES)',
        ]);

        SimulatorTag::create([
            'name' => 'FADEC',
        ]);
    }
}
