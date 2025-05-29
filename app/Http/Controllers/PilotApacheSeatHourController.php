<?php

namespace App\Http\Controllers;

use App\Models\PilotApacheSeatHour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PilotApacheSeatHourController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        $seatHours = PilotApacheSeatHour::where('user_id', $user->id)->first();

        $data = $seatHours ? [
            'id' => $seatHours->id,
            'user_id' => $seatHours->user_id,
            'aircraft_id' => $seatHours->aircraft_id,
            'fac_level_id' => $seatHours->fac_level_id,
            'aircraft_back_seat_hours' => $seatHours->aircraft_back_seat_hours,
            'aircraft_front_seat_hours' => $seatHours->aircraft_front_seat_hours,
            'simulator_back_seat_hours' => $seatHours->simulator_back_seat_hours,
            'simulator_front_seat_hours' => $seatHours->simulator_front_seat_hours,
            'nvs_hours' => $seatHours->nvs_hours,
            'nvd_hours' => $seatHours->nvd_hours,
        ] : [
            'id' => null,
            'user_id' => $user->id,
            'aircraft_id' => null,
            'fac_level_id' => null,
            'aircraft_back_seat_hours' => 0.0,
            'aircraft_front_seat_hours' => 0.0,
            'simulator_back_seat_hours' => 0.0,
            'simulator_front_seat_hours' => 0.0,
            'nvs_hours' => 0.0,
            'nvd_hours' => 0.0,
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
            'aircraft_back_seat_hours' => 'nullable|numeric|min:0|max:9999.9',
            'aircraft_front_seat_hours' => 'nullable|numeric|min:0|max:9999.9',
            'simulator_back_seat_hours' => 'nullable|numeric|min:0|max:9999.9',
            'simulator_front_seat_hours' => 'nullable|numeric|min:0|max:9999.9',
            'nvs_hours' => 'nullable|numeric|min:0|max:9999.9',
            'nvd_hours' => 'nullable|numeric|min:0|max:9999.9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $seatHours = PilotApacheSeatHour::where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'aircraft_id' => $request->aircraft_id,
            'fac_level_id' => $request->fac_level_id,
            'aircraft_back_seat_hours' => $request->aircraft_back_seat_hours ?? 0.0,
            'aircraft_front_seat_hours' => $request->aircraft_front_seat_hours ?? 0.0,
            'simulator_back_seat_hours' => $request->simulator_back_seat_hours ?? 0.0,
            'simulator_front_seat_hours' => $request->simulator_front_seat_hours ?? 0.0,
            'nvs_hours' => $request->nvs_hours ?? 0.0,
            'nvd_hours' => $request->nvd_hours ?? 0.0,
        ];

        if ($seatHours) {
            $seatHours->update($data);
        } else {
            $seatHours = PilotApacheSeatHour::create($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Seat hours updated successfully',
            'data' => [
//                'id' => $seatHours->id,
//                'user_id' => $seatHours->user_id,
                'aircraft_id' => $seatHours->aircraft_id,
                'fac_level_id' => $seatHours->fac_level_id,
                'aircraft_back_seat_hours' => $seatHours->aircraft_back_seat_hours,
                'aircraft_front_seat_hours' => $seatHours->aircraft_front_seat_hours,
                'simulator_back_seat_hours' => $seatHours->simulator_back_seat_hours,
                'simulator_front_seat_hours' => $seatHours->simulator_front_seat_hours,
                'nvs_hours' => $seatHours->nvs_hours,
                'nvd_hours' => $seatHours->nvd_hours,
                
            ],
        ], 200);
    }
}
