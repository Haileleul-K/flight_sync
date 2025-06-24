<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\DutyPosition;
use App\Models\Mission;
use App\Models\AircraftModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFlightRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FlightController extends Controller
{
    protected function formatFlight($flight)
    {
        return [
            'id' => $flight->id,
            'user_id' => $flight->user_id,
            'day' => $flight->day,
            'night' => $flight->night,
            'nvs' => $flight->nvs,
            'hood' => $flight->hood,
            'weather' => $flight->weather,
            'nvg' => $flight->nvg,
            'date' => $flight->date ? $flight->date->format('Y-m-d') : null,
            'image' => $flight->image,
            'duty_position' => $flight->dutyPosition ? [
                'id' => $flight->dutyPosition->id,
                'code' => $flight->dutyPosition->code,
                'label' => $flight->dutyPosition->label,
            ] : null,
            'mission' => $flight->mission ? [
                'id' => $flight->mission->id,
                'code' => $flight->mission->code,
                'label' => $flight->mission->label,
            ] : null,
            'aircraft_model' => $flight->aircraftModel ? [
                'id' => $flight->aircraftModel->id,
                'model' => $flight->aircraftModel->model,
            ] : null,
            'seat' => $flight->seat,
            'tail_number' => $flight->tail_number,
            'departure_airport' => $flight->departure_airport,
            'arrival_airport' => $flight->arrival_airport,
            'tags' => $flight->tags,
            'notes' => $flight->notes,
            'created_at' => $flight->created_at ? $flight->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $flight->updated_at ? $flight->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function list(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        $flights = Flight::where('user_id', $user->id)
            ->with(['dutyPosition', 'mission', 'aircraftModel'])
            ->get();

        $data = $flights->map(function ($flight) {
            return $this->formatFlight($flight);
        });

        return response()->json([
            'message' => 'Successfully retrieved flights.',
            'data' => $data
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(StoreFlightRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        $flight = Flight::create($validated);
        $flight->load(['dutyPosition', 'mission', 'aircraftModel']);

        return response()->json([
            'message' => 'Flight created successfully.',
            'data' => $this->formatFlight($flight)
        ], 201);
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        $flightsData = $request->input('flights', []);

        if (!is_array($flightsData) || empty($flightsData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No flight data provided or invalid format',
            ], 400);
        }

        // Validate each flight against StoreFlightRequest rules
        $validator = Validator::make(['flights' => $flightsData], [
            'flights' => 'required|array',
            'flights.*' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Custom validation for each flight using StoreFlightRequest logic
        $validatedFlights = [];
        foreach ($flightsData as $index => $flightData) {
            $flightValidator = Validator::make($flightData, (new StoreFlightRequest())->rules());
            if ($flightValidator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Validation failed for flight at index $index: " . $flightValidator->errors()->first(),
                ], 422);
            }
            $validatedFlights[] = array_merge($flightData, ['user_id' => $user->id]);
        }

        $createdFlights = [];

        try {
            DB::beginTransaction();

            foreach ($validatedFlights as $flightData) {
                // Convert string numbers to float/int where applicable
                $flightData = array_map(function ($value) {
                    if (is_numeric($value)) {
                        return is_float($value + 0) ? (float)$value : (int)$value;
                    }
                    return $value;
                }, $flightData);

                $flight = Flight::create($flightData);
                $flight->load(['dutyPosition', 'mission', 'aircraftModel']);
                $createdFlights[] = $this->formatFlight($flight);
            }

            DB::commit();

            return response()->json([
                'message' => 'Flights created successfully.',
                'data' => $createdFlights
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create multiple flights', ['error' => $e->getMessage(), 'data' => $flightsData]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create flights',
            ], 500);
        }
    }

    public function show(Flight $flight): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        Log::debug("user id and flight user id", [$flight->user_id, $user->id]);
        if ($flight->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $flight->load(['dutyPosition', 'mission', 'aircraftModel']);

        return response()->json([
            'message' => 'Flight retrieved successfully.',
            'data' => $this->formatFlight($flight)
        ]);
    }

    public function update(StoreFlightRequest $request, Flight $flight): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        if ($flight->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $flight->update($request->validated());
        $flight->load(['dutyPosition', 'mission', 'aircraftModel']);

        return response()->json([
            'message' => 'Flight updated successfully.',
            'data' => $this->formatFlight($flight)
        ]);
    }

    public function updateRelatedData(Request $request, Flight $flight): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        if ($flight->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'duty_position' => 'nullable|array',
            'duty_position.code' => 'nullable|string|max:50',
            'duty_position.label' => 'nullable|string|max:100',
            'mission' => 'nullable|array',
            'mission.code' => 'nullable|string|max:50',
            'mission.label' => 'nullable|string|max:100',
            'aircraft_model' => 'nullable|array',
            'aircraft_model.model' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($request->has('duty_position') && $flight->duty_position_id) {
            DutyPosition::where('id', $flight->duty_position_id)->update([
                'code' => $request->input('duty_position.code', DutyPosition::find($flight->duty_position_id)->code),
                'label' => $request->input('duty_position.label', DutyPosition::find($flight->duty_position_id)->label),
            ]);
        }

        if ($request->has('mission') && $flight->mission_id) {
            Mission::where('id', $flight->mission_id)->update([
                'code' => $request->input('mission.code', Mission::find($flight->mission_id)->code),
                'label' => $request->input('mission.label', Mission::find($flight->mission_id)->label),
            ]);
        }

        if ($request->has('aircraft_model') && $flight->aircraft_models_id) {
            AircraftModel::where('id', $flight->aircraft_models_id)->update([
                'model' => $request->input('aircraft_model.model', AircraftModel::find($flight->aircraft_models_id)->model),
            ]);
        }

        $flight->load(['dutyPosition', 'mission', 'aircraftModel']);

        return response()->json([
            'status' => 'success',
            'message' => 'Related data updated successfully',
            'data' => $this->formatFlight($flight),
        ], 200);
    }
}