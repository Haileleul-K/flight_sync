<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rank::create([
            'name' => 'WO1',
        ]);

        Rank::create([
            'name' => 'WO2',
        ]);

        Rank::create([
            'name' => 'CW1',
        ]);

        Rank::create([
            'name' => 'CW2',
        ]);

        Rank::create([
            'name' => 'CW3',
        ]);

        Rank::create([
            'name' => 'CW4',
        ]);

        Rank::create([
            'name' => '1LT',
        ]);

        Rank::create([
            'name' => '2LT',
        ]);
    }
}
