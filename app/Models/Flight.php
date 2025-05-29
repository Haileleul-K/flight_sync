<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'night',
        'nvs',
        'hood',
        'weather',
        'nvg',
        'date',
        'image',
        'duty_position_id',
        'mission_id',
        'aircraft_models_id',
        'seat',
        'tail_number',
        'departure_airport',
        'arrival_airport',
        'tags',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dutyPosition()
    {
        return $this->belongsTo(DutyPosition::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function aircraftModel()
    {
        return $this->belongsTo(AircraftModel::class, 'aircraft_models_id');
    }
}
