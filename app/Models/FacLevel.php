<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacLevel extends Model
{
    protected $fillable = ['level'];

    public function aircraftModels()
    {
        return $this->hasMany(AircraftModel::class);
    }
}