<?php

namespace App\Http\Controllers;

use App\Models\SimulatorTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class SimulatorTagController extends Controller
{
    public function index()
    {
        $simulatorTags = SimulatorTag::all();
        return response()->json([
            'status' => 'success',
            'data' => $simulatorTags,
        ], 200);
    }

    public function show($id)
    {
        $simulatorTag = SimulatorTag::find($id);

        if (!$simulatorTag) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simulator tag not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $simulatorTag,
        ], 200);
    }

    public function destroy($id)
    {
        $simulatorTag = SimulatorTag::find($id);

        if (!$simulatorTag) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simulator tag not found',
            ], 404);
        }

        try {
            $simulatorTag->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Simulator tag deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete simulator tag; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:simulator_tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $simulatorTag = SimulatorTag::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Simulator tag created successfully',
            'data' => $simulatorTag,
        ], 201);
    }
}
