<?php

namespace App\Http\Controllers;

use App\Models\PilotExtraCurrency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PilotExtraCurrencyController extends Controller
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

        $extraCurrency = PilotExtraCurrency::where('user_id', $user->id)->first();

        $data = $extraCurrency ? [
            'id' => $extraCurrency->id,
            'user_id' => $extraCurrency->user_id,
            'cbrn_hours' => $extraCurrency->cbrn_hours,
            'hoist' => $extraCurrency->hoist,
            'extended_fuel_system' => $extraCurrency->extended_fuel_system,
        ] : [
            'id' => null,
            'user_id' => $user->id,
            'cbrn_hours' => null,
            'hoist' => null,
            'extended_fuel_system' => null,
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
            'cbrn_hours' => 'nullable|numeric|min:0|max:9999.9',
            'hoist' => 'nullable|boolean',
            'extended_fuel_system' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $extraCurrency = PilotExtraCurrency::where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'cbrn_hours' => $request->input('cbrn_hours', null),
            'hoist' => $request->has('hoist') ? (bool) $request->input('hoist') : null,
            'extended_fuel_system' => $request->has('extended_fuel_system') ? (bool) $request->input('extended_fuel_system') : null,
        ];

        try {
            if ($extraCurrency) {
                $extraCurrency->update($data);
            } else {
                $extraCurrency = PilotExtraCurrency::create($data);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Extra currency updated successfully',
                'data' => [
                    'cbrn_hours' => $extraCurrency->cbrn_hours ?? 0.0,
                    'hoist' => $extraCurrency->hoist ?? false,
                    'extended_fuel_system' => $extraCurrency->extended_fuel_system ?? false,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update extra currency: ' . $e->getMessage(),
            ], 500);
        }
    }
}
