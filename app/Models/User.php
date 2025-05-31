<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'birth_month',
        'rank_id',
        'fac_level_id',
        'rl_level_id',
        'aircraft_model_id',
        'token',
    ];

    protected $hidden = [
        'password',
    ];



    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function facLevel()
    {
        return $this->belongsTo(FacLevel::class, 'fac_level_id');
    }

    public function rlLevel()
    {
        return $this->belongsTo(RlLevel::class, 'rl_level_id');
    }

    public function aircraftModel()
    {
        return $this->belongsTo(AircraftModel::class, 'aircraft_model_id');
    }

    public function pilotSemiAnnualPeriod()
    {
        return $this->hasOne(PilotSemiAnnualPeriod::class);
    }

    public function aircraftSimulatorMin()
    {
        return $this->hasOne(AircraftSimulatorMin::class);
    }

    public function pilotExtraCurrency()
    {
        return $this->hasOne(PilotExtraCurrency::class);
    }

    public function pilotApacheSeatHour()
    {
        return $this->hasOne(PilotApacheSeatHour::class);
    }
}
