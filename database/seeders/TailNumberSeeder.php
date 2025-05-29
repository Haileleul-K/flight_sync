<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TailNumber;

class TailNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creating sample tail numbers
        TailNumber::create([
            'number' => 'N12345',
        ]);

        TailNumber::create([
            'number' => 'N67890',
        ]);

        TailNumber::create([
            'number' => 'N11223',
        ]);

        TailNumber::create([
            'number' => 'N44556',
        ]);

        TailNumber::create([
            'number' => 'N78901',
        ]);
    }
}
