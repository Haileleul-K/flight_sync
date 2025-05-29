<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'duty_position_id',
        'aircraft_models_id',
        'day',
        'night',
        'nvs',
        'hood',
        'weather',
        'nvg',
        'date',
        'image',
        'tags',
        'notes',
        'seat'
    ];

    protected $casts = [
        'day' => 'integer',
        'night' => 'integer',
        'nvs' => 'integer',
        'hood' => 'integer',
        'weather' => 'integer',
        'nvg' => 'integer',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dutyPosition()
    {
        return $this->belongsTo(DutyPosition::class);
    }

    public function aircraftModel()
    {
        return $this->belongsTo(AircraftModel::class, 'aircraft_models_id');
    }
}
