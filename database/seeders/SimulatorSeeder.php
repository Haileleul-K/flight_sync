<?php

namespace Database\Seeders;

use App\Models\AircraftModel;
use App\Models\DutyPosition;
use App\Models\Flight;
use App\Models\Mission;
use App\Models\Simulator;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;

class SimulatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dutyPositionTestFlight = DutyPosition::where('code', 'PL')->first();
        $airCraftModelTest1 = AircraftModel::where('model', 'AH64')->first();

        Log::debug('aircraft model', [
            'aircraft_model' => $airCraftModelTest1->seats[0]
        ]);


        $seats = $airCraftModelTest1?->seats;
        $seatName = $seats[0] ?? 'No seat';
        $user1 = User::where('email', 'ab@ab.com')->first();

        if (!$dutyPositionTestFlight || !$user1 ) {

            $this->command->error('Missing required records: user, mission, or duty position.');
            return;
        }

        Simulator::create([
            'user_id' => $user1->id,
            'night' => 2,
            'day' => 2,
            'hood' => 3,
            'nvg' => 4,
            'nvs' => 5,
            'weather' => 6,
            'date' => Carbon::parse('2024-12-31'),
            'duty_position_id' => $dutyPositionTestFlight->id,
            'aircraft_models_id' => $airCraftModelTest1->id,
            'tags' => '[AMC, FADEC]',
            'notes' => 'flight notes for simulator',
            'seat' => $seatName
        ]);
    }
}
