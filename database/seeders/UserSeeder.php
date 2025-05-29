<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FacLevel; // Importing the FacLevel model
use App\Models\Rank; // Importing the Rank model
use App\Models\RlLevel; // Importing the RlLevel model
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // You may want to retrieve actual values from the database to assign to these fields
        // $facLevel1 = FacLevel::where('level', 'FAC 1')->first()->id;
        // $rankWO1 = Rank::where('name', 'WO1')->first()->id;
        // $rlLevel1 = RlLevel::where('level', 'RL1')->first()->id;


        // $facLevel2 = FacLevel::where('level', 'FAC 2')->first()->id;
        // $rankWO2 = Rank::where('name', 'WO2')->first()->id;
        // $rlLevel2 = RlLevel::where('level', 'RL2')->first()->id;

        // Creating users with the relationships
        User::create([
            'full_name' => 'Riyadh',
            'email' => 'riyadh@mail.com',
            'password' => Hash::make('12345'),
            // 'fac_level_id' => $facLevel1, // Referencing the fac_level from the FacLevel table
            // 'rank_id' => $rankWO1, // Referencing the rank from the Rank table
            // 'rl_level_id' => $rlLevel1, // Referencing the rl_level from the RlLevel table
            'token' => Str::random(100),
        ]);


        User::create([
            'full_name' => 'Usama Akram',
            'email' => 'usama@mail.com',
            'password' => Hash::make('12345'),
            // 'fac_level_id' => $facLevel2, // Referencing the fac_level from the FacLevel table
            // 'rank_id' => $rankWO2, // Referencing the rank from the Rank table
            // 'rl_level_id' => $rlLevel2, // Referencing the rl_level from the RlLevel table
            'token' => Str::random(100),
        ]);
    }
}
