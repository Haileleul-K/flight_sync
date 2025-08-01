<?php

// RunAllMigrations.php
// This script will run all migration 'up' methods in the migrations directory.

use Illuminate\Database\Capsule\Manager as Capsule;

// Bootstrap Laravel's Eloquent if needed (optional, for DB connection)
// require_once __DIR__ . '/../../vendor/autoload.php';
// require_once __DIR__ . '/../../bootstrap/app.php';

// List all migration files
$migrationFiles = [
    '2025_02_08_043518_create_fac_levels_table.php',
    '2025_06_14_113041_add_is_admin_column_to_users_table.php',
    '2025_05_31_072218_create_personal_access_tokens_table.php',
    '2025_05_27_201044_create_simulator_updated.php',
    '2025_05_26_123132_create_simulator.php',
    '2025_02_08_043614_create_simulators_table.php',
    '2025_05_20_131038_pilot_semi_annual_periods.php',
    '2025_05_16_081741_create_aircraft_models.php',
    '2025_05_16_085055_create_users_table.php',
    '2025_05_16_080409_create_flight_table.php',
    '2025_05_15_084652_pilot_apache_seat_hours.php',
    '2025_05_15_084819_pilot_extra_currencies.php',
    '2025_02_08_043432_create_ranks_table.php',
    '2025_02_08_043531_create_rl_levels_table.php',
    '2025_02_08_043723_create_flight_tags_table.php',
    '2025_02_08_043731_create_simulator_tags_table.php',
    '2025_02_08_091556_create_personal_access_tokens_table.php',
    '2025_02_09_075541_create_sessions_table.php',
];

// Named migration classes
$namedClasses = [
    '2025_06_14_113041_add_is_admin_column_to_users_table.php' => 'AddIsAdminColumnToUsersTable',
];

foreach ($migrationFiles as $file) {
    echo "Running migration: $file ... ";
    $path = __DIR__ . "/$file";
    if (!file_exists($path)) {
        echo "File not found!\n";
        continue;
    }
    if (isset($namedClasses[$file])) {
        require_once $path;
        $class = $namedClasses[$file];
        $migration = new $class();
    } else {
        // Anonymous class: the file returns the migration object
        $migration = require $path;
    }
    if (method_exists($migration, 'up')) {
        $migration->up();
        echo "Done.\n";
    } else {
        echo "No up() method found!\n";
    }
}

echo "All migrations executed.\n"; 