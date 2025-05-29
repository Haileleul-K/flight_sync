<?php

namespace App\Http\Controllers;

use App\Models\AircraftSimulatorMin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AircraftSimulatorMinController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        $simulatorMin = AircraftSimulatorMin::where('user_id', $user->id)->first();

        $data = $simulatorMin ? [
            'id' => $simulatorMin->id,
            'user_id' => $simulatorMin->user_id,
            'aircraft_id' => $simulatorMin->aircraft_id,
            'fac_level_id' => $simulatorMin->fac_level_id,
            'aircraft_total_hours' => $simulatorMin->aircraft_total_hours,
            'hood_weather' => $simulatorMin->hood_weather,
            'night' => $simulatorMin->night,
            'nvg' => $simulatorMin->nvg,
            'simulator_total_hours' => $simulatorMin->simulator_total_hours,
        ] : [
            'id' => null,
            'user_id' => $user->id,
            'aircraft_id' => null,
            'fac_level_id' => null,
            'aircraft_total_hours' => 0.0,
            'hood_weather' => 0,
            'night' => 0,
            'nvg' => 0,
            'simulator_total_hours' => 0.0,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'aircraft_id' => 'required|exists:aircraft_models,id',
            'fac_level_id' => 'required|exists:fac_levels,id',
            'aircraft_total_hours' => 'nullable|numeric|min:0|max:9999999.9',
            'hood_weather' => 'nullable|integer|min:0',
            'night' => 'nullable|integer|min:0',
            'nvg' => 'nullable|integer|min:0',
            'simulator_total_hours' => 'nullable|numeric|min:0|max:9999999.9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $simulatorMin = AircraftSimulatorMin::where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'aircraft_id' => $request->aircraft_id,
            'fac_level_id' => $request->fac_level_id,
            'aircraft_total_hours' => $request->aircraft_total_hours ?? 0.0,
            'hood_weather' => $request->hood_weather ?? 0,
            'night' => $request->night ?? 0,
            'nvg' => $request->nvg ?? 0,
            'simulator_total_hours' => $request->simulator_total_hours ?? 0.0,
        ];

        if ($simulatorMin) {
            $simulatorMin->update($data);
        } else {
            $simulatorMin = AircraftSimulatorMin::create($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Simulator min updated successfully',
            'data' => [
//                'id' => $simulatorMin->id,
//                'user_id' => $simulatorMin->user_id,
                'aircraft_id' => $simulatorMin->aircraft_id,
                'fac_level_id' => $simulatorMin->fac_level_id,
                'aircraft_total_hours' => $simulatorMin->aircraft_total_hours,
                'hood_weather' => $simulatorMin->hood_weather,
                'night' => $simulatorMin->night,
                'nvg' => $simulatorMin->nvg,
                'simulator_total_hours' => $simulatorMin->simulator_total_hours,
            ],
        ], 200);
    }
}
