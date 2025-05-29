<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AircraftModel extends Model
{
    protected $fillable = ['model', 'seats'];

    protected $casts = [
        'seats' => 'array',
    ];
}