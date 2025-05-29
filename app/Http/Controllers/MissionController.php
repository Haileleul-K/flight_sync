<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class MissionController extends Controller
{
    public function index()
    {
        $missions = Mission::all();
        return response()->json([
            'status' => 'success',
            'data' => $missions,
        ], 200);
    }

    public function show($id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mission not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mission,
        ], 200);
    }

    public function destroy($id)
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mission not found',
            ], 404);
        }

        try {
            $mission->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Mission deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete mission; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:missions,code',
            'label' => 'required|string|max:100|unique:missions,label',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' =>  $validator->errors()->first(),
            ], 422);
        }

        $mission = Mission::create([
            'code' => $request->code,
            'label' => $request->label,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mission created successfully',
            'data' => $mission,
        ], 201);
    }
}
