<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'day' => 'nullable|integer|min:0',
            'night' => 'nullable|integer|min:0',
            'nvs' => 'nullable|integer|min:0',
            'hood' => 'nullable|integer|min:0',
            'weather' => 'nullable|integer|min:0',
            'nvg' => 'nullable|integer|min:0',
            'date' => 'nullable|date',
            'image' => 'nullable|string|max:255',
            'duty_position_id' => 'nullable|exists:duty_positions,id',
            'mission_id' => 'nullable|exists:missions,id',
            'aircraft_models_id' => 'nullable|exists:aircraft_models,id',
            'seat' => 'nullable|string|max:50',
            'tail_number' => 'nullable|string|max:50',
            'departure_airport' => 'nullable|string|max:10',
            'arrival_airport' => 'nullable|string|max:10',
            'tags' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
