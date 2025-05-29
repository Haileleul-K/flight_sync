<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotApacheSeatHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'aircraft_id',
        'fac_level_id',
        'aircraft_back_seat_hours',
        'aircraft_front_seat_hours',
        'simulator_back_seat_hours',
        'simulator_front_seat_hours',
        'nvs_hours',
        'nvd_hours',
    ];

    protected $casts = [
        'aircraft_back_seat_hours' => 'decimal:1',
        'aircraft_front_seat_hours' => 'decimal:1',
        'simulator_back_seat_hours' => 'decimal:1',
        'simulator_front_seat_hours' => 'decimal:1',
        'nvs_hours' => 'decimal:1',
        'nvd_hours' => 'decimal:1',
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
