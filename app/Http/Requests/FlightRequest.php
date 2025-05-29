<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users for now — restrict as needed
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'duty_position_id' => 'required|exists:duty_positions,id',
            'mission_id' => 'required|exists:missions,id',
            'aircraft_models_id' => 'required|exists:aircraft_models,id',
            'tail_number_id' => 'required|exists:tail_numbers,id',
            'seat' => 'required|string|max:255',

            // Flight time/conditions — optional but numeric
            'night' => 'nullable|integer|min:0',
            'day' => 'nullable|integer|min:0',
            'hood' => 'nullable|integer|min:0',
            'nvg' => 'nullable|integer|min:0',
            'nvs' => 'nullable|integer|min:0',
            'weather' => 'nullable|integer|min:0',

            // Optional text fields
            'departure_airport' => 'nullable|string|max:255',
            'arrival_airport' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
