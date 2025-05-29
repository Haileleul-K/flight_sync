<?php

namespace App\Http\Controllers;

use App\Models\RlLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class RlLevelController extends Controller
{
    public function index()
    {
        $rlLevels = RlLevel::all();
        return response()->json([
            'status' => 'success',
            'data' => $rlLevels,
        ], 200);
    }

    public function show($id)
    {
        $rlLevel = RlLevel::find($id);

        if (!$rlLevel) {
            return response()->json([
                'status' => 'error',
                'message' => 'RL level not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rlLevel,
        ], 200);
    }

    public function destroy($id)
    {
        $rlLevel = RlLevel::find($id);

        if (!$rlLevel) {
            return response()->json([
                'status' => 'error',
                'message' => 'RL level not found',
            ], 404);
        }

        try {
            $rlLevel->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'RL level deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete RL level; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|string|max:50|unique:rl_levels,level',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' =>  $validator->errors()->first(),

            ], 422);
        }

        $rlLevel = RlLevel::create([
            'level' => $request->level,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'RL level created successfully',
            'data' => $rlLevel,
        ], 201);
    }
}
