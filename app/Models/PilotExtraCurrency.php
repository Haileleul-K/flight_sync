<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotExtraCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cbrn_hours',
        'hoist',
        'extended_fuel_system',
    ];

    protected $casts = [
        'cbrn_hours' => 'decimal:1',
        'hoist' => 'boolean',
        'extended_fuel_system' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
