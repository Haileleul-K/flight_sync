<?php

namespace App\Http\Controllers;

use App\Models\Simulator;
use App\Models\DutyPosition;
use App\Models\AircraftModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSimulatorRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SimulatorController extends Controller
{
    protected function formatSimulator($simulator)
    {
        return [
            'id' => $simulator->id,
            'user_id' => $simulator->user_id,
            'day' => $simulator->day,
            'night' => $simulator->night,
            'nvs' => $simulator->nvs,
            'hood' => $simulator->hood,
            'weather' => $simulator->weather,
            'nvg' => $simulator->nvg,
            'date' => $simulator->date ? $simulator->date->format('Y-m-d') : null,
            'image' => $simulator->image,
            'seat' => $simulator->seat,
            'duty_position' => $simulator->dutyPosition ? [
                'id' => $simulator->dutyPosition->id,
                'code' => $simulator->dutyPosition->code,
                'label' => $simulator->dutyPosition->label,
            ] : null,
            'aircraft_model' => $simulator->aircraftModel ? [
                'id' => $simulator->aircraftModel->id,
                'model' => $simulator->aircraftModel->model,
            ] : null,
            'tags' => $simulator->tags,
            'notes' => $simulator->notes,
            'created_at' => $simulator->created_at ? $simulator->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $simulator->updated_at ? $simulator->updated_at->format('Y-m-d H:i:s') : null,
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

        $simulators = Simulator::where('user_id', $user->id)
            ->with(['dutyPosition', 'aircraftModel'])
            ->get();

        $data = $simulators->map(function ($simulator) {
            return $this->formatSimulator($simulator);
        });

        return response()->json([
            'message' => 'Successfully retrieved simulators.',
            'data' => $data
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(StoreSimulatorRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        // Log raw request data
        Log::debug('Raw request data for simulator creation', $request->all());

        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        // Log validated data
        Log::debug('Validated data for simulator creation', $validated);

        try {
            // Explicitly map validated data to model
            $simulator = new Simulator();
            $simulator->user_id = $user->id;
            $simulator->day = $validated['day'] ?? null;
            $simulator->night = $validated['night'] ?? null;
            $simulator->nvs = $validated['nvs'] ?? null;
            $simulator->hood = $validated['hood'] ?? null;
            $simulator->weather = $validated['weather'] ?? null;
            $simulator->nvg = $validated['nvg'] ?? null;
            $simulator->date = $validated['date'] ?? null;
            $simulator->image = $validated['image'] ?? null;
            $simulator->seat = $validated['seat'] ?? null;
            $simulator->duty_position_id = $validated['duty_position_id'] ?? null;
            $simulator->aircraft_models_id = $validated['aircraft_models_id'] ?? null;
            $simulator->tags = $validated['tags'] ?? null;
            $simulator->notes = $validated['notes'] ?? null;
            $simulator->save();

            $simulator->load(['dutyPosition', 'aircraftModel']);

            // Log created simulator data
            Log::debug('Created simulator data', $simulator->toArray());
        } catch (\Exception $e) {
            Log::error('Failed to create simulator', [
                'error' => $e->getMessage(),
                'validated_data' => $validated,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create simulator: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Simulator created successfully.',
            'data' => $this->formatSimulator($simulator)
        ], 201);
    }

    public function show(Simulator $simulator): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        Log::debug("user id and simulator user id", [$simulator->user_id, $user->id]);
        if ($simulator->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $simulator->load(['dutyPosition', 'aircraftModel']);

        return response()->json([
            'message' => 'Simulator retrieved successfully.',
            'data' => $this->formatSimulator($simulator)
        ]);
    }

    public function update(StoreSimulatorRequest $request, Simulator $simulator): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        if ($simulator->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Log::debug('Validated data for simulator update', $request->validated());

        try {
            $simulator->update($request->validated());
            $simulator->load(['dutyPosition', 'aircraftModel']);
        } catch (\Exception $e) {
            Log::error('Failed to update simulator', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update simulator: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Simulator updated successfully.',
            'data' => $this->formatSimulator($simulator)
        ]);
    }

    public function updateRelatedData(Request $request, Simulator $simulator): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User not authenticated',
            ], 401);
        }

        if ($simulator->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'duty_position' => 'nullable|array',
            'duty_position.code' => 'nullable|string|max:50',
            'duty_position.label' => 'nullable|string|max:100',
            'aircraft_model' => 'nullable|array',
            'aircraft_model.model' => 'nullable|string|max:100',
            'seat' => 'nullable|string|in:Front Seat,Back Seat'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            if ($request->has('duty_position') && $simulator->duty_position_id) {
                DutyPosition::where('id', $simulator->duty_position_id)->update([
                    'code' => $request->input('duty_position.code', DutyPosition::find($simulator->duty_position_id)->code),
                    'label' => $request->input('duty_position.label', DutyPosition::find($simulator->duty_position_id)->label),
                ]);
            }

            if ($request->has('aircraft_model') && $simulator->aircraft_models_id) {
                AircraftModel::where('id', $simulator->aircraft_models_id)->update([
                    'model' => $request->input('aircraft_model.model', AircraftModel::find($simulator->aircraft_models_id)->model),
                ]);
            }

            if ($request->has('seat')) {
                $simulator->seat = $request->input('seat');
                $simulator->save();
                $simulator->refresh();
                Log::debug('Updated seat value', ['seat' => $simulator->seat]);
            }

            $simulator->load(['dutyPosition', 'aircraftModel']);
        } catch (\Exception $e) {
            Log::error('Failed to update related data', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update related data: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Related data updated successfully',
            'data' => $this->formatSimulator($simulator),
        ], 200);
    }
}
