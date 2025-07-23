<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;


class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            Log::debug('Register request received', [
                'input' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email',
                'password' => 'required|string|min:4',
            ]);

            if ($validator->fails()) {
                Log::warning('Register validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::register',
                ]);
            }

            $user = new User([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->save();

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $request->email,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Account registered, please login',
            ], 201, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::register',
            ]);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'headers' => $request->headers->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register user: ' . $e->getMessage(),
            ], 500, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::register',
            ]);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            Log::debug('Login request received', [
                'input' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:100',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('Login validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::login',
                ]);
            }

            $user = User::where('email', $request->email)
                ->with([
                    'rank',
                    'facLevel',
                    'rlLevel',
                    'aircraftModel',
                    'pilotSemiAnnualPeriod',
                    'aircraftSimulatorMin',
                    'pilotExtraCurrency',
                    'pilotApacheSeatHour'
                ])
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Login failed: Incorrect credentials', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect email or password',
                ], 401, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::login',
                ]);
            }

            // Issue a Sanctum token instead of a custom UUID
            $token = $user->createToken('FlightSyncToken')->plainTextToken;

            $data = [
                'token' => $token,
                'users' => [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'rank' => $user->rank ? $user->rank->name : null,
                    'birth_month' => $user->birth_month,
                    'fac_level' => $user->facLevel ? $user->facLevel->level : null,
                    'rl_level' => $user->rlLevel ? $user->rlLevel->level : null,
                    'primary_aircraft' => $user->aircraftModel ? $user->aircraftModel->model : null,
                ],
                'pilot_semi_annual_periods' => $user->pilotSemiAnnualPeriod ? [
                    'start_date' => $user->pilotSemiAnnualPeriod->start_date ? $user->pilotSemiAnnualPeriod->start_date->format('Y-m-d') : null,
                    'end_date' => $user->pilotSemiAnnualPeriod->end_date ? $user->pilotSemiAnnualPeriod->end_date->format('Y-m-d') : null,
                ] : [
                    'start_date' => null,
                    'end_date' => null,
                ],
                'aircraft_simulator_min' => $user->aircraftSimulatorMin ? [
                    'aircraft_id' => $user->aircraftSimulatorMin->aircraft_id,
                    'fac_level_id' => $user->aircraftSimulatorMin->fac_level_id,
                    'aircraft_total_hours' => $user->aircraftSimulatorMin->aircraft_total_hours,
                    'hood_weather' => $user->aircraftSimulatorMin->hood_weather,
                    'night' => $user->aircraftSimulatorMin->night,
                    'nvg' => $user->aircraftSimulatorMin->nvg,
                    'simulator_total_hours' => $user->aircraftSimulatorMin->simulator_total_hours,
                ] : [
                    'aircraft_id' => null,
                    'fac_level_id' => null,
                    'aircraft_total_hours' => 0.0,
                    'hood_weather' => 0,
                    'night' => 0,
                    'nvg' => 0,
                    'simulator_total_hours' => 0.0,
                ],
                'pilot_extra_currencies' => $user->pilotExtraCurrency ? [
                    'cbrn_hours' => $user->pilotExtraCurrency->cbrn_hours ?? 0.00,
                    'hoist' => $user->pilotExtraCurrency->hoist ?? false,
                    'extended_fuel_system' => $user->pilotExtraCurrency->extended_fuel_system ?? false,
                ] : [
                    'cbrn_hours' => 0.00,
                    'hoist' => false,
                    'extended_fuel_system' => false,
                ],
                'pilot_apache_seat_hours' => $user->pilotApacheSeatHour ? [
                    'aircraft_id' => $user->pilotApacheSeatHour->aircraft_id,
                    'fac_level_id' => $user->pilotApacheSeatHour->fac_level_id,
                    'aircraft_back_seat_hours' => $user->pilotApacheSeatHour->aircraft_back_seat_hours,
                    'aircraft_front_seat_hours' => $user->pilotApacheSeatHour->aircraft_front_seat_hours,
                    'simulator_back_seat_hours' => $user->pilotApacheSeatHour->simulator_back_seat_hours,
                    'simulator_front_seat_hours' => $user->pilotApacheSeatHour->simulator_front_seat_hours,
                    'nvs_hours' => $user->pilotApacheSeatHour->nvs_hours,
                    'nvd_hours' => $user->pilotApacheSeatHour->nvd_hours,
                ] : [
                    'aircraft_id' => null,
                    'fac_level_id' => null,
                    'aircraft_back_seat_hours' => 0.0,
                    'aircraft_front_seat_hours' => 0.0,
                    'simulator_back_seat_hours' => 0.0,
                    'simulator_front_seat_hours' => 0.0,
                    'nvs_hours' => 0.0,
                    'nvd_hours' => 0.0,
                ],
            ];

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $data,
            ], 200, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::login',
            ]);
        } catch (\Exception $e) {
            Log::error('User login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'headers' => $request->headers->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to login: ' . $e->getMessage(),
            ], 500, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::login',
            ]);
        }
    }




    public function adminLogin(Request $request): JsonResponse
{
    try {
        Log::debug('Admin login request received', [
            'input' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Admin login validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            Log::warning('Admin login failed: User not found', ['email' => $request->email]);
            return response()->json([
                'status' => 'error',
                'message' => 'User not registered',
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Admin login failed: Incorrect password', ['email' => $request->email]);
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password',
            ], 401);
        }

        // Check if user is admin
        if (!$user->is_admin) {
            Log::warning('Admin login failed: User is not admin', ['user_id' => $user->id]);
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied: Administrator privileges required',
            ], 403);
        }

        // Issue a Sanctum token
        $token = $user->createToken('FlightSyncAdminToken')->plainTextToken;

        // Prepare response data
        $responseData = [
            'token' => $token,
            'is_admin' => $user->is_admin,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email
            ]
        ];

        Log::info('Admin logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin login successful',
            'data' => $responseData,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Admin login failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to login: ' . $e->getMessage(),
        ], 500);
    }
}

    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('Profile access failed: No authenticated user');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: User not authenticated',
                ], 401, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::profile',
                ]);
            }

            $user->load(['rank', 'facLevel', 'rlLevel', 'aircraftModel']);

            Log::info('User profile fetched successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'birth_month' => $user->birth_month,
                    'rank' => $user->rank ? ['id' => $user->rank->id, 'name' => $user->rank->name] : null,
                    'fac_level' => $user->facLevel ? ['id' => $user->facLevel->id, 'level' => $user->facLevel->level] : null,
                    'rl_level' => $user->rlLevel ? ['id' => $user->rlLevel->id, 'level' => $user->rlLevel->level] : null,
                    'aircraft_model' => $user->aircraftModel ? ['id' => $user->aircraftModel->id, 'model' => $user->aircraftModel->model] : null,
                ],
            ], 200, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::profile',
            ]);
        } catch (\Exception $e) {
            Log::error('User profile fetch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile: ' . $e->getMessage(),
            ], 500, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::profile',
            ]);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('Profile update failed: No authenticated user');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: User not authenticated',
                ], 401, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::updateProfile',
                ]);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'nullable|string|max:100',
                'birth_month' => 'nullable|string|max:15',
                'rank_id' => 'nullable|exists:ranks,id',
                'fac_level_id' => 'nullable|exists:fac_levels,id',
                'rl_level_id' => 'nullable|exists:rl_levels,id',
                'aircraft_model_id' => 'nullable|exists:aircraft_models,id',
            ]);

            if ($validator->fails()) {
                Log::warning('Profile update validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422, [
                    'Content-Type' => 'application/json',
                    'X-Debug-Source' => 'UserController::updateProfile',
                ]);
            }

            $user->update([
                'full_name' => $request->full_name ?? $user->full_name,
                'birth_month' => $request->birth_month ?? $user->birth_month,
                'rank_id' => $request->rank_id !== null ? $request->rank_id : $user->rank_id,
                'fac_level_id' => $request->fac_level_id !== null ? $request->fac_level_id : $user->fac_level_id,
                'rl_level_id' => $request->rl_level_id !== null ? $request->rl_level_id : $user->rl_level_id,
                'aircraft_model_id' => $request->aircraft_model_id !== null ? $request->aircraft_model_id : $user->aircraft_model_id,
            ]);

            $user->refresh()->load(['rank', 'facLevel', 'rlLevel', 'aircraftModel']);

            Log::info('User profile updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'rank' => $user->rank ? $user->rank->name : null,
                    'birth_month' => $user->birth_month,
                    'fac_level' => $user->facLevel ? $user->facLevel->level : null,
                    'rl_level' => $user->rlLevel ? $user->rlLevel->level : null,
                    'primary_aircraft' => $user->aircraftModel ? $user->aircraftModel->model : null,
                ],
            ], 200, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::updateProfile',
            ]);
        } catch (\Exception $e) {
            Log::error('User profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500, [
                'Content-Type' => 'application/json',
                'X-Debug-Source' => 'UserController::updateProfile',
            ]);
        }
    }
}
