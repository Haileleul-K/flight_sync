<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::all();
        return response()->json([
            'status' => 'success',
            'data' => $ranks,
        ], 200);
    }

    public function show($id)
    {
        $rank = Rank::find($id);

        if (!$rank) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rank not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $rank,
        ], 200);
    }

    public function destroy($id)
    {
        $rank = Rank::find($id);

        if (!$rank) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rank not found',
            ], 404);
        }

        try {
            $rank->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Rank deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete rank; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:ranks,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),

            ], 422);
        }

        $rank = Rank::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rank created successfully',
            'data' => $rank,
        ], 201);
    }
}
