<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\Mission;
use App\Models\User;
use App\Models\DutyPosition;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\AircraftModel;
use App\Models\TailNumber;
use Illuminate\Support\Facades\Log;


class FlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dutyPositionTestFlight = DutyPosition::where('code', 'PL')->first();
        $missionCombatMission = Mission::where('code', 'C')->first();
        $airCraftModelTest1 = AircraftModel::where('model','AH64')->first();
        Log::debug('aircraft model', [
            'aircraft_model' => $airCraftModelTest1->seats[0]
        ]);

//        $seatName = $airCraftModelTest1?->seats->first()->name ?? 'No seats';
        $seats = $airCraftModelTest1?->seats;
        $seatName = $seats[0] ?? 'No seat';

        $user1 = User::where('email', 'riyadh@mail.com')->first();
        $user2 = User::where('email', 'usama@mail.com')->first();

        if (!$dutyPositionTestFlight || !$missionCombatMission || !$user1 || !$user2) {

            $this->command->error('Missing required records: user, mission, or duty position.');
            return;
        }

        Flight::create([
            'user_id' => $user1->id,
            'night' => 2,
            'day' => 2,
            'hood' => 3,
            'nvg' => 4,
            'nvs' => 5,
            'weather' => 6,
            'date' => Carbon::parse('2024-12-31'),
            'duty_position_id' => $dutyPositionTestFlight->id,
            'mission_id' => $missionCombatMission->id,
            'aircraft_models_id' => $airCraftModelTest1->id,
            'seat' => $seatName,
            'tail_number' => '356',
            'departure_airport' => 'JED',
            'arrival_airport' => 'RUH',
            'tags' => '[AMC, FADEC]',
            'notes' => 'flight notes',
        ]);

        Flight::create([
            'user_id' => $user2->id,
            'night' => 2,
            'day' => 2,
            'hood' => 3,
            'nvg' => 4,
            'nvs' => 5,
            'weather' => 6,
            'date' => Carbon::parse('2024-12-31'),
            'duty_position_id' => $dutyPositionTestFlight->id,
            'mission_id' => $missionCombatMission->id,
            'aircraft_models_id' => $airCraftModelTest1->id,
            'seat' => $seatName,
            'tail_number' => '345',
            'departure_airport' => 'JED',
            'arrival_airport' => 'RUH',
            'tags' => '[AMC, FADEC]',
            'notes' => 'flight notes',
        ]);
    }
}
