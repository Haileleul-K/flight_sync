<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mission::create([
            'code' => 'T',
            'label' => 'Training Flight',
        ]);
        
        Mission::create([
            'code' => 'F',
            'label' => 'Maintenance Test Flight',
        ]);
        
        Mission::create([
            'code' => 'C',
            'label' => 'Combat Mission',
        ]);
        
        Mission::create([
            'code' => 'D',
            'label' => 'Imminent Danger',
        ]);
        
        Mission::create([
            'code' => 'S',
            'label' => 'Service Mission',
        ]);
        
        Mission::create([
            'code' => 'A',
            'label' => 'Acceptance Test Flight',
        ]);
        
        Mission::create([
            'code' => 'E',
            'label' => 'Experimental Test Flight',
        ]);
    }
}
