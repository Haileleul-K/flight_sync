<?php

namespace App\Http\Controllers;

use App\Models\AircraftModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class AircraftModelController extends Controller
{
    public function index()
    {
        $aircraftModels = AircraftModel::all();
        return response()->json([
            'status' => 'success',
            'data' => $aircraftModels,
        ], 200);
    }

    public function show($id)
    {
        $aircraftModel = AircraftModel::find($id);

        if (!$aircraftModel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aircraft model not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $aircraftModel,
        ], 200);
    }

    public function destroy($id)
    {
        $aircraftModel = AircraftModel::find($id);

        if (!$aircraftModel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aircraft model not found',
            ],404);
        }

        try {
            $aircraftModel->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Aircraft model deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete aircraft model; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model' => 'required|string|max:50|unique:aircraft_models,model',
            'seats' => 'required|array',
            'seats.*' => 'string|max:50',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' =>  $validator->errors()->first(),

            ], 422);
        }

        $aircraftModel = AircraftModel::create([
            'model' => $request->model,
            'seats' => $request->seats,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Aircraft model created successfully',
            'data' => $aircraftModel,
        ], 201);
    }
}
