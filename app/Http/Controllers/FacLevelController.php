<?php

namespace App\Http\Controllers;

use App\Models\FacLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class FacLevelController extends Controller
{
    /**
     * Fetch all FAC levels.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $facLevels = FacLevel::all();
        return response()->json([
            'status' => 'success',
            'data' => $facLevels,
        ], 200);
    }

   /**
    *  @param  int  $id
    * @return \Illuminate\Http\JsonResponse
    */
    public function fetchSingle($id)
    {

        $facLevel = FacLevel::find($id);


        if (!$facLevel) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAC level not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $facLevel,
        ], 200);
    }

    /**
     * Delete a specific FAC level by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $facLevel = FacLevel::find($id);

        if (!$facLevel) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAC level not found',
            ], 404);
        }

        try {

            $deletedFacLevel = FacLevel::find($id)->level;
            $facLevel->delete();
            return response()->json([
                'status' => 'success',
                'message' => ''. $deletedFacLevel . ' level deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete FAC level; it is referenced by aircraft models',
            ], 409);
        }
    }

    /**
     * Insert a new FAC level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|string|max:50|unique:fac_levels,level',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $facLevel = FacLevel::create([
            'level' => $request->level,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FAC level created successfully',
            'data' => $facLevel,
        ], 201);



    }
}
