<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DutyPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
    ];

    protected $casts = [
        'code' => 'string',
        'label' => 'string',
    ];
}
