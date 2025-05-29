<?php

use App\Http\Controllers\AircraftSimulatorMinController;
use App\Http\Controllers\DutyPositionController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\FlightTagController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PilotApacheSeatHourController;
use App\Http\Controllers\PilotExtraCurrencyController;
use App\Http\Controllers\PilotSemiAnnualPeriodController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RlLevelController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\SimulatorTagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Controllers\FacLevelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AircraftModelController;


Route::get('/fac-levels', [FacLevelController::class, 'index']);  // Fetch all FAC levels
Route::get('/fac-levels/{id}', [FacLevelController::class, 'fetchSingle']);   // Fetch a specific FAC level
Route::delete('/fac-levels/{id}', [FacLevelController::class, 'destroy']);  // Delete a specific FAC level
Route::post('/fac-levels', [FacLevelController::class, 'store']);  // Create a new FAC level
Route::get('/aircraft-models', [AircraftModelController::class, 'index']);  // Fetch all aircraft models
Route::get('/aircraft-models/{id}', [AircraftModelController::class, 'show']);  // Fetch a specific aircraft model
Route::delete('/aircraft-models/{id}', [AircraftModelController::class, 'destroy']);  // Delete a specific aircraft model
Route::post('/aircraft-models', [AircraftModelController::class, 'store']);  // Create a new aircraft model
Route::get('/flight-tags', [FlightTagController::class, 'index']);  // Fetch all flight tags
Route::get('/flight-tags/{id}', [FlightTagController::class, 'show']); // Fetch a specific flight tag
Route::delete('/flight-tags/{id}', [FlightTagController::class, 'destroy']); // Delete a specific flight tag
Route::post('/flight-tags', [FlightTagController::class, 'store']); // Create a new flight tag
Route::get('/missions', [MissionController::class, 'index']); // Fetch all missions
Route::get('/missions/{id}', [MissionController::class, 'show']); // Fetch a specific mission
Route::delete('/missions/{id}', [MissionController::class, 'destroy']); // Delete a specific mission
Route::post('/missions', [MissionController::class, 'store']); // Create a new mission
Route::get('/ranks', [RankController::class, 'index']); // Fetch all ranks
Route::get('/ranks/{id}', [RankController::class, 'show']); // Fetch a specific rank
Route::delete('/ranks/{id}', [RankController::class, 'destroy']); // Delete a specific rank
Route::post('/ranks', [RankController::class, 'store']); // Create a new rank
Route::get('/rl-levels', [RlLevelController::class, 'index']); // Fetch all RL levels
Route::get('/rl-levels/{id}', [RlLevelController::class, 'show']); // Fetch a specific RL level
Route::delete('/rl-levels/{id}', [RlLevelController::class, 'destroy']); // Delete a specific RL level
Route::post('/rl-levels', [RlLevelController::class, 'store']); //  Create a new RL level
Route::get('/simulator-tags', [SimulatorTagController::class, 'index']); // Fetch all simulator tags
Route::get('/simulator-tags/{id}', [SimulatorTagController::class, 'show']); // Fetch a specific simulator tag
Route::delete('/simulator-tags/{id}', [SimulatorTagController::class, 'destroy']); // Delete a specific simulator tag
Route::post('/simulator-tags', [SimulatorTagController::class, 'store']); // Create a new simulator tag
// Public routes for duty positions (no authentication)
Route::get('/duty-positions', [DutyPositionController::class, 'index']);  // Fetch all duty positions
Route::get('/duty-positions/{id}', [DutyPositionController::class, 'show']); // Fetch a specific duty position
Route::post('/duty-positions', [DutyPositionController::class, 'store']);  // Create a new duty position
Route::delete('/duty-positions/{id}', [DutyPositionController::class, 'destroy']);  // Delete a specific duty position
Route::get('/lookups', [LookupController::class, 'index']);

Route::post('/login', [UserController::class, "login"]);
Route::post('/register', [UserController::class, "register"]);

Route::get('/ranks', [MasterController::class, "ranks"]);
Route::get('/rl_levels', [MasterController::class, "rlLevels"]);
Route::get('/aircraft_models', [MasterController::class, "airCraftModels"]);
Route::get('/duty_positions', [MasterController::class, "dutyPosition"]);
Route::get('/flight_tags', [MasterController::class, "flightTags"]);


// Protected routes
Route::middleware([ApiAuthMiddleware::class])->group(function () {
    Route::put('/profile', [UserController::class, 'updateProfile']); // Update user profile
    Route::get('/flights', [FlightController::class, 'list']); // Fetch all flights
    Route::post('/flights', [FlightController::class, 'store']);// Create a new flight
    Route::get('/flights/{flight}', [FlightController::class, 'show']); // Fetch a specific flight
    Route::put('/flights/{flight}', [FlightController::class, 'update']);// Update a specific flight
    Route::get('/pilot-apache-seat-hours', [PilotApacheSeatHourController::class, 'show']); // Fetch pilot Apache seat hours
    Route::patch('/pilot-apache-seat-hours', [PilotApacheSeatHourController::class, 'update']); // Update pilot Apache seat hours
    Route::get('/pilot-extra-currencies', [PilotExtraCurrencyController::class, 'show']); // Fetch pilot extra currencies
    Route::patch('/pilot-extra-currencies', [PilotExtraCurrencyController::class, 'update']); // Update pilot extra currencies
    Route::get('/aircraft-simulator-min', [AircraftSimulatorMinController::class, 'show']);
    Route::patch('/aircraft-simulator-min', [AircraftSimulatorMinController::class, 'update']);
    Route::get('/pilot-semi-annual-periods', [PilotSemiAnnualPeriodController::class, 'show']);
    Route::patch('/pilot-semi-annual-periods', [PilotSemiAnnualPeriodController::class, 'update']);

    Route::get('/reports', [ReportController::class, 'careerReport']);


    Route::get('/simulators', [SimulatorController::class, 'list']);
    Route::post('/simulators', [SimulatorController::class, 'store']);
    Route::get('/simulators/{simulator}', [SimulatorController::class, 'show']);
    Route::put('/simulators/{simulator}', [SimulatorController::class, 'update']);


});
