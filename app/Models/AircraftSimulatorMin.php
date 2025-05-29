<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AircraftSimulatorMin extends Model
{
    use HasFactory;

    protected $table = 'aircraft_simulator_min';

    protected $fillable = [
        'user_id',
        'aircraft_id',
        'fac_level_id',
        'aircraft_total_hours',
        'hood_weather',
        'night',
        'nvg',
        'simulator_total_hours',
    ];

    protected $casts = [
        'aircraft_total_hours' => 'decimal:1',
        'simulator_total_hours' => 'decimal:1',
        'hood_weather' => 'integer',
        'night' => 'integer',
        'nvg' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aircraft()
    {
        return $this->belongsTo(AircraftModel::class, 'aircraft_id');
    }

    public function facLevel()
    {
        return $this->belongsTo(FacLevel::class);
    }
}
