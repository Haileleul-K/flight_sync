<?php

namespace App\Http\Controllers;

use App\Models\DutyPosition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DutyPositionController extends Controller
{
    public function index(): JsonResponse
    {
        $dutyPositions = DutyPosition::all();

        return response()->json([
            'status' => 'success',
            'data' => $dutyPositions->map(function ($position) {
                return [
                    'id' => $position->id,
                    'code' => $position->code,
                    'label' => $position->label,
                ];
            }),
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $dutyPosition = DutyPosition::find($id);

        if (!$dutyPosition) {
            return response()->json([
                'status' => 'error',
                'message' => 'Duty position not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $dutyPosition->id,
                'code' => $dutyPosition->code,
                'label' => $dutyPosition->label,
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:duty_positions',
            'label' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dutyPosition = DutyPosition::create([
            'code' => $request->code,
            'label' => $request->label,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Duty position created successfully',
            'data' => [
                'id' => $dutyPosition->id,
                'code' => $dutyPosition->code,
                'label' => $dutyPosition->label,
            ],
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $dutyPosition = DutyPosition::find($id);

        if (!$dutyPosition) {
            return response()->json([
                'status' => 'error',
                'message' => 'Duty position not found',
            ], 404);
        }

        $dutyPosition->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Duty position deleted successfully',
        ], 200);
    }
}
