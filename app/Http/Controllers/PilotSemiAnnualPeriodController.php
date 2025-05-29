<?php

namespace App\Http\Controllers;

use App\Models\PilotSemiAnnualPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PilotSemiAnnualPeriodController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        $semiAnnualPeriod = PilotSemiAnnualPeriod::where('user_id', $user->id)->first();

        $data = $semiAnnualPeriod ? [
            'id' => $semiAnnualPeriod->id,
            'user_id' => $semiAnnualPeriod->user_id,
            'start_date' => $semiAnnualPeriod->start_date ? $semiAnnualPeriod->start_date->format('Y-m-d') : null,
            'end_date' => $semiAnnualPeriod->end_date ? $semiAnnualPeriod->end_date->format('Y-m-d') : null,
        ] : [
            'id' => null,
            'user_id' => $user->id,
            'start_date' => null,
            'end_date' => null,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $semiAnnualPeriod = PilotSemiAnnualPeriod::where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'start_date' => $request->input('start_date', null),
            'end_date' => $request->input('end_date', null),
        ];

        try {
            if ($semiAnnualPeriod) {
                $semiAnnualPeriod->update($data);
            } else {
                $semiAnnualPeriod = PilotSemiAnnualPeriod::create($data);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Semi-annual period updated successfully',
                'data' => [
                    'start_date' => $semiAnnualPeriod->start_date ? $semiAnnualPeriod->start_date->format('Y-m-d') : null,
                    'end_date' => $semiAnnualPeriod->end_date ? $semiAnnualPeriod->end_date->format('Y-m-d') : null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update semi-annual period: ' . $e->getMessage(),
            ], 500);
        }
    }
}
