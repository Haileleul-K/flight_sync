<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Simulator;
use App\Models\AircraftSimulatorMin;
use App\Models\PilotApacheSeatHour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function careerReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        // Determine aircraft_id from aircraft_simulator_min or fallback to PilotApacheSeatHour
        $minReq = AircraftSimulatorMin::where('user_id', $user->id)
            ->select(['aircraft_id'])
            ->first();

        $apacheReq = PilotApacheSeatHour::where('user_id', $user->id)
            ->select(['aircraft_id'])
            ->first();

        $aircraftId = $minReq ? $minReq->aircraft_id : ($apacheReq ? $apacheReq->aircraft_id : null);

        // Flight report
        $flightQuery = Flight::where('user_id', $user->id)->with(['dutyPosition', 'mission']);
        $flightStartDate = $flightQuery->min('date');
        $flightEndDate = $flightQuery->max('date');

        $flightCareerTotals = $flightQuery->selectRaw('
            COALESCE(SUM(day), 0) as day,
            COALESCE(SUM(night), 0) as night,
            COALESCE(SUM(nvs), 0) as nvs,
            COALESCE(SUM(hood), 0) as hood,
            COALESCE(SUM(weather), 0) as weather,
            COALESCE(SUM(nvg), 0) as nvg
        ')->first()->toArray();

        // Calculate total_flight_hour for aircraftCareerStat
        $flightTotalFlightHour = array_sum([
            (float) $flightCareerTotals['day'],
            (float) $flightCareerTotals['night'],
            (float) $flightCareerTotals['nvs'],
            (float) $flightCareerTotals['hood'],
            (float) $flightCareerTotals['weather'],
            (float) $flightCareerTotals['nvg']
        ]);

        // Duty totals filtered by aircraft_id
        $flightDutyTotals = $aircraftId
            ? Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->leftJoin('duty_positions', 'flights.duty_position_id', '=', 'duty_positions.id')
                ->groupBy('duty_positions.code')
                ->selectRaw('
                    duty_positions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ')
                ->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray()
            : (object) [];

        // Mission totals filtered by aircraft_id
        $flightMissionTotals = $aircraftId
            ? Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->leftJoin('missions', 'flights.mission_id', '=', 'missions.id')
                ->groupBy('missions.code')
                ->selectRaw('
                    missions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ')
                ->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray()
            : (object) [];

        // Simulator report
        $simulatorQuery = Simulator::where('user_id', $user->id);
        $simulatorCareerTotals = $simulatorQuery->selectRaw('
            COALESCE(SUM(day), 0) as day,
            COALESCE(SUM(night), 0) as night,
            COALESCE(SUM(nvs), 0) as nvs,
            COALESCE(SUM(hood), 0) as hood,
            COALESCE(SUM(weather), 0) as weather,
            COALESCE(SUM(nvg), 0) as nvg
        ')->first()->toArray();

        // Calculate total_flight_hour for simulatorCareerStat
        $simulatorTotalFlightHour = array_sum([
            (float) $simulatorCareerTotals['day'],
            (float) $simulatorCareerTotals['night'],
            (float) $simulatorCareerTotals['nvs'],
            (float) $simulatorCareerTotals['hood'],
            (float) $simulatorCareerTotals['weather'],
            (float) $simulatorCareerTotals['nvg']
        ]);

        $simulatorDutyTotals = $simulatorQuery
            ->leftJoin('duty_positions', 'simulators.duty_position_id', '=', 'duty_positions.id')
            ->groupBy('duty_positions.code')
            ->selectRaw('
                duty_positions.code,
                COALESCE(SUM(simulators.day + simulators.night + simulators.nvs + simulators.hood + simulators.weather + simulators.nvg), 0) as total
            ')
            ->pluck('total', 'code')
            ->toArray();

        // Semi-annual report: Primary Aircraft Hours
        $minReq = AircraftSimulatorMin::where('user_id', $user->id)
            ->select([
                'aircraft_id',
                'aircraft_total_hours',
                'simulator_total_hours',
                'hood_weather',
                'night',
                'nvg'
            ])->first();

        $primaryAircraftHours = [];
        if ($minReq) {
            $aircraftId = $minReq->aircraft_id;

            // Flight hours for this aircraft
            $flightTotals = Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->selectRaw('
                    COALESCE(SUM(day + night + nvs + hood + weather + nvg), 0) as airframe_total,
                    COALESCE(SUM(weather + hood), 0) as weather_hood_total,
                    COALESCE(SUM(night), 0) as night_total,
                    COALESCE(SUM(nvg), 0) as nvg_total
                ')
                ->first()
                ->toArray();

            // Simulator hours for this aircraft
            $simulatorTotals = Simulator::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->selectRaw('
                    COALESCE(SUM(day + night + nvs + hood + weather + nvg), 0) as simulator_total
                ')
                ->first()
                ->toArray();

            $airFrameRequired = (float) $minReq->aircraft_total_hours;
            $airFrameCompleted = (float) $flightTotals['airframe_total'];
            $airFrameRemark = max(0, $airFrameRequired - $airFrameCompleted);

            $simulatorRequired = (float) $minReq->simulator_total_hours;
            $simulatorCompleted = (float) $simulatorTotals['simulator_total'];
            $simulatorRemark = max(0, $simulatorRequired - $simulatorCompleted);

            $weatherHoodRequired = (float) $minReq->hood_weather;
            $weatherHoodCompleted = (float) $flightTotals['weather_hood_total'];
            $weatherHoodRemark = max(0, $weatherHoodRequired - $weatherHoodCompleted);

            $nightRequired = (float) $minReq->night;
            $nightCompleted = (float) $flightTotals['night_total'];
            $nightRemark = max(0, $nightRequired - $nightCompleted);

            $nvgRequired = (float) $minReq->nvg;
            $nvgCompleted = (float) $flightTotals['nvg_total'];
            $nvgRemark = max(0, $nvgRequired - $nvgCompleted);

            $primaryAircraftHours = [
                'airFrameHours' => [
                    'required' => number_format($airFrameRequired, 1, '.', ''),
                    'completed' => number_format($airFrameCompleted, 1, '.', ''),
                    'remark' => number_format($airFrameRemark, 1, '.', ''),
                ],
                'simulatorHour' => [
                    'required' => number_format($simulatorRequired, 1, '.', ''),
                    'completed' => number_format($simulatorCompleted, 1, '.', ''),
                    'remark' => number_format($simulatorRemark, 1, '.', ''),
                ],
                'weather/Hood' => [
                    'required' => number_format($weatherHoodRequired, 1, '.', ''),
                    'completed' => number_format($weatherHoodCompleted, 1, '.', ''),
                    'remark' => number_format($weatherHoodRemark, 1, '.', ''),
                ],
                'night' => [
                    'required' => number_format($nightRequired, 1, '.', ''),
                    'completed' => number_format($nightCompleted, 1, '.', ''),
                    'remark' => number_format($nightRemark, 1, '.', ''),
                ],
                'nvg' => [
                    'required' => number_format($nvgRequired, 1, '.', ''),
                    'completed' => number_format($nvgCompleted, 1, '.', ''),
                    'remark' => number_format($nvgRemark, 1, '.', ''),
                ],
            ];
        }

        // Semi-annual report: Apache Specific Hours
        $apacheReq = PilotApacheSeatHour::where('user_id', $user->id)
            ->select([
                'aircraft_id',
                'aircraft_front_seat_hours',
                'aircraft_back_seat_hours',
                'simulator_front_seat_hours',
                'simulator_back_seat_hours',
                'nvs_hours',
                'nvd_hours'
            ])->first();

        $appacheSpecificHours = [];
        if ($apacheReq) {
            $aircraftId = $apacheReq->aircraft_id;

            // Flight hours for this aircraft
            $flightTotals = Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN seat = \'Front Seat\' THEN day + night + nvs + hood + weather + nvg ELSE 0 END), 0) as front_seat_total,
                    COALESCE(SUM(CASE WHEN seat = \'Back Seat\' THEN day + night + nvs + hood + weather + nvg ELSE 0 END), 0) as back_seat_total,
                    COALESCE(SUM(nvs), 0) as nvs_total,
                    COALESCE(SUM(nvg), 0) as nvd_total
                ')
                ->first()
                ->toArray();

            // Simulator hours for this aircraft
            $simulatorTotals = Simulator::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN seat = \'Front Seat\' THEN day + night + nvs + hood + weather + nvg ELSE 0 END), 0) as front_seat_sim_total,
                    COALESCE(SUM(CASE WHEN seat = \'Back Seat\' THEN day + night + nvs + hood + weather + nvg ELSE 0 END), 0) as back_seat_sim_total
                ')
                ->first()
                ->toArray();

            $frontSeatAircraftRequired = (float) $apacheReq->aircraft_front_seat_hours;
            $frontSeatAircraftCompleted = (float) $flightTotals['front_seat_total'];
            $frontSeatAircraftRemark = max(0, $frontSeatAircraftRequired - $frontSeatAircraftCompleted);

            $backSeatAircraftRequired = (float) $apacheReq->aircraft_back_seat_hours;
            $backSeatAircraftCompleted = (float) $flightTotals['back_seat_total'];
            $backSeatAircraftRemark = max(0, $backSeatAircraftRequired - $backSeatAircraftCompleted);

            $frontSeatSimulatorRequired = (float) $apacheReq->simulator_front_seat_hours;
            $frontSeatSimulatorCompleted = (float) $simulatorTotals['front_seat_sim_total'];
            $frontSeatSimulatorRemark = max(0, $frontSeatSimulatorRequired - $frontSeatSimulatorCompleted);

            $backSeatSimulatorRequired = (float) $apacheReq->simulator_back_seat_hours;
            $backSeatSimulatorCompleted = (float) $simulatorTotals['back_seat_sim_total'];
            $backSeatSimulatorRemark = max(0, $backSeatSimulatorRequired - $backSeatSimulatorCompleted);

            $nvsRequired = (float) $apacheReq->nvs_hours;
            $nvsCompleted = (float) $flightTotals['nvs_total'];
            $nvsRemark = max(0, $nvsRequired - $nvsCompleted);

            $nvdRequired = (float) $apacheReq->nvd_hours;
            $nvdCompleted = (float) $flightTotals['nvd_total'];
            $nvdRemark = max(0, $nvdRequired - $nvdCompleted);

            $appacheSpecificHours = [
                'frontSeatAirCraft' => [
                    'required' => number_format($frontSeatAircraftRequired, 1, '.', ''),
                    'completed' => number_format($frontSeatAircraftCompleted, 1, '.', ''),
                    'remark' => number_format($frontSeatAircraftRemark, 1, '.', ''),
                ],
                'backSeatAirCraft' => [
                    'required' => number_format($backSeatAircraftRequired, 1, '.', ''),
                    'completed' => number_format($backSeatAircraftCompleted, 1, '.', ''),
                    'remark' => number_format($backSeatAircraftRemark, 1, '.', ''),
                ],
                'frontSeatSimulator' => [
                    'required' => number_format($frontSeatSimulatorRequired, 1, '.', ''),
                    'completed' => number_format($frontSeatSimulatorCompleted, 1, '.', ''),
                    'remark' => number_format($frontSeatSimulatorRemark, 1, '.', ''),
                ],
                'backSeatSimulator' => [
                    'required' => number_format($backSeatSimulatorRequired, 1, '.', ''),
                    'completed' => number_format($backSeatSimulatorCompleted, 1, '.', ''),
                    'remark' => number_format($backSeatSimulatorRemark, 1, '.', ''),
                ],
                'nvs' => [
                    'required' => number_format($nvsRequired, 1, '.', ''),
                    'completed' => number_format($nvsCompleted, 1, '.', ''),
                    'remark' => number_format($nvsRemark, 1, '.', ''),
                ],
                'nvd' => [
                    'required' => number_format($nvdRequired, 1, '.', ''),
                    'completed' => number_format($nvdCompleted, 1, '.', ''),
                    'remark' => number_format($nvdRemark, 1, '.', ''),
                ],
            ];
        }

        $response = [
            'aircraftCareerStat' => [
                'start_date' => $flightStartDate ? date('Y-m-d', strtotime($flightStartDate)) : null,
                'end_date' => $flightEndDate ? date('Y-m-d', strtotime($flightEndDate)) : null,
                'careerTotals' => [
                    'day' => (float) $flightCareerTotals['day'],
                    'night' => (float) $flightCareerTotals['night'],
                    'nvs' => (float) $flightCareerTotals['nvs'],
                    'hood' => (float) $flightCareerTotals['hood'],
                    'weather' => (float) $flightCareerTotals['weather'],
                    'nvg' => (float) $flightCareerTotals['nvg'],
                ],
                'total_flight_hour' => number_format($flightTotalFlightHour, 1, '.', ''), // Moved total_flight_hour here
                'dutyTotals' => $flightDutyTotals,
                'missionTotals' => $flightMissionTotals,
            ],
            'simulatorCareerStat' => [
                'careerTotals' => [
                    'day' => (int) $simulatorCareerTotals['day'],
                    'night' => (int) $simulatorCareerTotals['night'],
                    'nvs' => (int) $simulatorCareerTotals['nvs'],
                    'hood' => (int) $simulatorCareerTotals['hood'],
                    'weather' => (int) $simulatorCareerTotals['weather'],
                    'nvg' => (int) $simulatorCareerTotals['nvg'],
                ],
                'total_flight_hour' => number_format($simulatorTotalFlightHour, 1, '.', ''), // Moved total_flight_hour here
                'dutyTotals' => $simulatorDutyTotals ?: (object) [],
            ],
            'semiAnnualReport' => [
                'primaryAircraftHours' => $primaryAircraftHours ?: (object) [],
                'appacheSpecificHours' => $appacheSpecificHours ?: (object) [],
            ],
        ];

        return response()->json([
            'message' => 'Successfully retrieved career report.',
            'data' => $response
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
