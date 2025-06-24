<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFlightRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'day' => 'required|numeric', // Changed from integer to numeric
            'night' => 'required|numeric', // Changed from integer to numeric
            'nvs' => 'required|numeric', // Changed from integer to numeric
            'hood' => 'required|numeric', // Changed from integer to numeric
            'weather' => 'required|numeric', // No change
            'nvg' => 'required|numeric', // Changed from integer to numeric
            'date' => 'required|date',
            'image' => 'nullable|string',
            'duty_position_id' => 'required|exists:duty_positions,id',
            'mission_id' => 'required|exists:missions,id',
            'aircraft_models_id' => 'required|exists:aircraft_models,id',
            'seat' => 'nullable|string|max:50',
            'tail_number' => 'nullable|string|max:10',
            'departure_airport' => 'nullable|string|max:4',
            'arrival_airport' => 'nullable|string|max:4',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'weather.in' => 'The weather must be one of: VFR, IFR, MVFR.',
            'seat.in' => 'The seat must be one of: left, right, back seat.',
        ];
    }
}


