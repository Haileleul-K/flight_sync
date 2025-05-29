<?php

namespace App\Http\Controllers;

use App\Models\FlightTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;


class FlightTagController extends Controller
{
    public function index()
    {
        $flightTags = FlightTag::all();
        return response()->json([
            'status' => 'success',
            'data' => $flightTags,
        ], 200);
    }

    public function show($id)
    {
        $flightTag = FlightTag::find($id);

        if (!$flightTag) {
            return response()->json([
                'status' => 'error',
                'message' => 'Flight tag not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $flightTag,
        ], 200);
    }

    public function destroy($id)
    {
        $flightTag = FlightTag::find($id);

        if (!$flightTag) {
            return response()->json([
                'status' => 'error',
                'message' => 'Flight tag not found',
            ], 404);
        }

        try {
            $flightTag->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Flight tag deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete flight tag; it may be referenced by other records',
            ], 409);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:flight_tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $flightTag = FlightTag::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Flight tag created successfully',
            'data' => $flightTag,
        ], 201);
    }
}
