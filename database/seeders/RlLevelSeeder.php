<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RlLevel;

class RlLevelSeeder extends Seeder
{
   public function run() {
    RlLevel::create([
        'level' => 'RL1',
    ]);
    RlLevel::create([
        'level' => 'RL2',
    ]);
}
}
