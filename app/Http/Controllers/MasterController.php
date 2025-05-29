<?php

namespace App\Http\Controllers;

use App\Http\Resources\AircraftModelsResource;
use App\Http\Resources\DutyPositionResource;
use App\Http\Resources\FacLevelResource;
use App\Http\Resources\FlightTagResource;
use App\Http\Resources\RankResource;
use App\Http\Resources\RlLevelResource;
use App\Http\Resources\SimulatorTagResource;
use App\Models\AircraftModel;
use App\Models\DutyPosition;
use App\Models\FacLevel;
use App\Models\FlightTag;
use App\Models\Rank;
use App\Models\RlLevel;
use App\Models\SimulatorTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function ranks(): JsonResponse
    {
        $ranks = Rank::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => RankResource::collection($ranks)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function facLevels(): JsonResponse
    {
        $fac = FacLevel::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => FacLevelResource::collection($fac)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function rlLevels(): JsonResponse
    {
        $rl = RlLevel::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => RlLevelResource::collection($rl)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function airCraftModels(): JsonResponse
    {
        $aircrafts = AircraftModel::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => AircraftModelsResource::collection($aircrafts)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function dutyPosition(): JsonResponse
    {
        $position = DutyPosition::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => DutyPositionResource::collection($position)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function flightTags(): JsonResponse
    {
        $tags = FlightTag::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => FlightTagResource::collection($tags)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }

    public function simulatorTags(): JsonResponse
    {
        $tags = SimulatorTag::all();
        $data = [
            "message" => "Success retrieve data",
            "data" => SimulatorTagResource::collection($tags)
        ];
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }
}
