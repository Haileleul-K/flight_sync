<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Simulator;
use App\Models\AircraftSimulatorMin;
use App\Models\PilotApacheSeatHour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function careerReport(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            Log::error('Unauthorized access attempt to careerReport');
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Log::info('Fetching career report for user ID: ' . $user->id);

        // Determine aircraft_models_id from flights table (use the most frequent or latest)
        $flightQuery = Flight::where('user_id', $user->id);
        $aircraftModelsIds = $flightQuery->pluck('aircraft_models_id')->toArray();

        $aircraftId = null;
        if (!empty($aircraftModelsIds)) {
            // Use the most frequent aircraft_models_id (mode)
            $aircraftId = array_search(max(array_count_values($aircraftModelsIds)), array_count_values($aircraftModelsIds));
            Log::debug('Determined aircraft_models_id (most frequent): ' . $aircraftId);
        } else {
            Log::warning('No flights found for user, aircraft_models_id will be null.');
        }

        // If no frequent ID or no flights, use the latest aircraft_models_id as a fallback
        if (!$aircraftId && $flightQuery->exists()) {
            $latestFlight = $flightQuery->latest('date')->first();
            $aircraftId = $latestFlight ? $latestFlight->aircraft_models_id : null;
            Log::debug('Fallback to latest aircraft_models_id: ' . $aircraftId);
        }

        // Flight report
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

        // Duty totals (aggregate by duty_positions.code, joined via duty_position_id)
        $flightDutyTotals = [];
        if ($aircraftId) {
            $dutyTotalsQuery = Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->leftJoin('duty_positions', 'flights.duty_position_id', '=', 'duty_positions.id')
                ->groupBy('duty_positions.code')
                ->selectRaw('
                    duty_positions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ');
            $dutyTotals = $dutyTotalsQuery->get();
            Log::debug('Duty totals query result: ', $dutyTotals->toArray());
            $flightDutyTotals = $dutyTotals->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray();
        } else {
            // If no aircraft_id, aggregate all flights' duty totals
            $dutyTotalsQuery = Flight::where('user_id', $user->id)
                ->leftJoin('duty_positions', 'flights.duty_position_id', '=', 'duty_positions.id')
                ->groupBy('duty_positions.code')
                ->selectRaw('
                    duty_positions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ');
            $dutyTotals = $dutyTotalsQuery->get();
            Log::debug('Duty totals query result (no aircraft_id): ', $dutyTotals->toArray());
            $flightDutyTotals = $dutyTotals->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray();
            Log::warning('No valid aircraft_models_id found, using all flights for duty totals.');
        }

        // Mission totals (aggregate by missions.code, joined via mission_id)
        $flightMissionTotals = [];
        if ($aircraftId) {
            $missionTotalsQuery = Flight::where('user_id', $user->id)
                ->where('aircraft_models_id', $aircraftId)
                ->leftJoin('missions', 'flights.mission_id', '=', 'missions.id')
                ->groupBy('missions.code')
                ->selectRaw('
                    missions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ');
            $missionTotals = $missionTotalsQuery->get();
            Log::debug('Mission totals query result: ', $missionTotals->toArray());
            $flightMissionTotals = $missionTotals->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray();
        } else {
            // If no aircraft_id, aggregate all flights' mission totals
            $missionTotalsQuery = Flight::where('user_id', $user->id)
                ->leftJoin('missions', 'flights.mission_id', '=', 'missions.id')
                ->groupBy('missions.code')
                ->selectRaw('
                    missions.code,
                    COALESCE(SUM(flights.day + flights.night + flights.nvs + flights.hood + flights.weather + flights.nvg), 0) as total
                ');
            $missionTotals = $missionTotalsQuery->get();
            Log::debug('Mission totals query result (no aircraft_id): ', $missionTotals->toArray());
            $flightMissionTotals = $missionTotals->pluck('total', 'code')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => number_format((float) $value, 1, '.', '')];
                })
                ->toArray();
            Log::warning('No valid aircraft_models_id found, using all flights for mission totals.');
        }

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
                'total_flight_hour' => number_format($flightTotalFlightHour, 1, '.', ''),
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
                'total_flight_hour' => number_format($simulatorTotalFlightHour, 1, '.', ''),
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

    public function dashboardReport(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            Log::error('Unauthorized access attempt to dashboardReport');
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Log::info('Fetching dashboard report for user ID: ' . $user->id);

        // Total registered users
        $totalUsers = User::count();

        // Calculate subscribed users for current and previous month
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentMonthUsers = User::where('created_at', '>=', $currentMonth)
            ->where('created_at', '<', $currentMonth->copy()->endOfMonth())
            ->count();

        $previousMonthUsers = User::where('created_at', '>=', $previousMonth)
            ->where('created_at', '<', $previousMonth->copy()->endOfMonth())
            ->count();

        $percentIncrease = $previousMonthUsers > 0
            ? (($currentMonthUsers - $previousMonthUsers) / $previousMonthUsers) * 100
            : ($currentMonthUsers > 0 ? 100 : 0);

        // Total flights logged
        $totalFlights = Flight::count();

        // Total aircraft hours
        $flightTotals = Flight::selectRaw('
            COALESCE(SUM(day), 0) as day,
            COALESCE(SUM(night), 0) as night,
            COALESCE(SUM(nvs), 0) as nvs,
            COALESCE(SUM(hood), 0) as hood,
            COALESCE(SUM(weather), 0) as weather,
            COALESCE(SUM(nvg), 0) as nvg
        ')->first()->toArray();

        $totalAircraftHours = array_sum([
            (float) $flightTotals['day'],
            (float) $flightTotals['night'],
            (float) $flightTotals['nvs'],
            (float) $flightTotals['hood'],
            (float) $flightTotals['weather'],
            (float) $flightTotals['nvg']
        ]);

        // Total simulator hours
        $simulatorTotals = Simulator::selectRaw('
            COALESCE(SUM(day), 0) as day,
            COALESCE(SUM(night), 0) as night,
            COALESCE(SUM(nvs), 0) as nvs,
            COALESCE(SUM(hood), 0) as hood,
            COALESCE(SUM(weather), 0) as weather,
            COALESCE(SUM(nvg), 0) as nvg
        ')->first()->toArray();

        $totalSimulatorHours = array_sum([
            (float) $simulatorTotals['day'],
            (float) $simulatorTotals['night'],
            (float) $simulatorTotals['nvs'],
            (float) $simulatorTotals['hood'],
            (float) $simulatorTotals['weather'],
            (float) $simulatorTotals['nvg']
        ]);

        // Aircraft usage by model
        $aircraftModels = DB::table('aircraft_models')->select('id', 'model')->get();
        $flightHours = Flight::select('aircraft_models_id', DB::raw('COALESCE(SUM(day + night + nvs + hood + weather + nvg), 0) as total_hours'))
            ->groupBy('aircraft_models_id')
            ->pluck('total_hours', 'aircraft_models_id')
            ->toArray();

        $aircraftUsage = [];
        foreach ($aircraftModels as $model) {
            $totalHours = isset($flightHours[$model->id]) ? (float) $flightHours[$model->id] : 0.0;
            $aircraftUsage[$model->model] = ['hours' => number_format($totalHours, 1, '.', '')];
        }

        $response = [
            'total_users' => $totalUsers,
            'subscribed_users_increase_percent' => number_format($percentIncrease, 2) . '%',
            'total_flights_logged' => $totalFlights,
            'total_aircraft_hours' => number_format($totalAircraftHours, 1, '.', ''),
            'total_simulator_hours' => number_format($totalSimulatorHours, 1, '.', ''),
           'AircraftUsage' => $aircraftUsage,
        ];

        return response()->json([
            'message' => 'Successfully retrieved dashboard report.',
            'data' => $response,
            'generated_at' => date('Y-m-d H:i:s')
        ], 200, [], JSON_PRETTY_PRINT);
    }
}