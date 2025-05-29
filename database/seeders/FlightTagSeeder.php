<?php

namespace Database\Seeders;

use App\Models\FlightTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FlightTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FlightTag::create([
            //e' => 'AMC',
            'name' => 'AMC',
        ]);

        FlightTag::create([
            //e' => 'Assault',
            'name' => 'Assault',
        ]);

        FlightTag::create([
            //e' => 'ATM_Flight',
            'name' => 'ATM Flight',
        ]);

        FlightTag::create([
            //e' => 'Bambi_Bucket',
            'name' => 'Bambi Bucket',
        ]);

        FlightTag::create([
            //e' => 'CMF',
            'name' => 'CMF',
        ]);

        FlightTag::create([
            //e' => 'Cross_Country',
            'name' => 'Cross Country',
        ]);

        FlightTag::create([
            //e' => 'Double_Bag',
            'name' => 'Double Bag',
        ]);

        FlightTag::create([
            //e' => 'Extended_Fuel_System',
            'name' => 'Extended Fuel System (CFES)',
        ]);

        FlightTag::create([
            //e' => 'FADEC',
            'name' => 'FADEC',
        ]);
    }
}
