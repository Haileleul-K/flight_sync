<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreSimulatorRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        $rules = [
            'day' => 'nullable|integer|min:0',
            'night' => 'nullable|integer|min:0',
            'nvs' => 'nullable|integer|min:0',
            'hood' => 'nullable|integer|min:0',
            'weather' => 'nullable|integer|min:0',
            'nvg' => 'nullable|integer|min:0',
            'date' => 'nullable|date_format:Y-m-d',
            'image' => 'nullable|string|max:255',
            'seat' => 'nullable|string|in:Front Seat,Back Seat',
            'duty_position_id' => 'nullable|exists:duty_positions,id',
            'aircraft_models_id' => 'nullable|exists:aircraft_models,id',
            'tags' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Log validation rules for debugging
        Log::debug('Validation rules for StoreSimulatorRequest', $rules);

        return $rules;
    }

    protected function passedValidation()
    {
        // Log validated data after validation
        Log::debug('Validated data after passing validation', $this->validated());
    }
}
