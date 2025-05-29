<?php

namespace Database\Seeders;

use App\Models\FacLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacLevel::create([
            'level' => 'FAC 1'
        ]);

        FacLevel::create([
            'level' => 'FAC 2'
        ]);

        FacLevel::create([
            'level' => 'FAC 3'
        ]);

        FacLevel::create([
            'level' => 'FAC 4'
        ]);
    }
}
