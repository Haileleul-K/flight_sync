<?php

namespace App\Http\Controllers;

use App\Models\FacLevel;
use App\Models\AircraftModel;
use App\Models\FlightTag;
use App\Models\Rank;
use App\Models\RlLevel;
use App\Models\SimulatorTag;
use App\Models\Mission;
use App\Models\DutyPosition;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [
            'fac_levels' => FacLevel::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'level' => $item->level,
                ];
            }),
            'aircraft_models' => AircraftModel::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'model' => $item->model,
                    'seats' => $item->seats,
                ];
            }),
            'ranks' => Rank::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'flight_tags' => FlightTag::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'rl_levels' => RlLevel::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'level' => $item->level,
                ];
            }),
            'simulator_tags' => SimulatorTag::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'missions' => Mission::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'label' => $item->label,
                ];
            }),
            'duty_positions' => DutyPosition::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'label' => $item->label,
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }
}
