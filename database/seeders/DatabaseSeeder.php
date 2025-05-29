<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
           //AircraftModelSeeder::class,
            ////FacLevelSeeder::class,
          // RankSeeder::class,
          // DutyPositionSeeder::class,
          // MissionSeeder::class,
          // FlightTagSeeder::class,
          // SimulatorTagSeeder::class,
          // UserSeeder::class,
          // SimulatorSeeder::class,
          FlightSeeder::class,
        ]);
    }
}
