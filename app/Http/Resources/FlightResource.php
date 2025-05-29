<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'day' => $this->day,
            'night' => $this->night,
            'nvs' => $this->nvs,
            'hood' => $this->hood,
            'weather' => $this->weather,
            'nvg' => $this->nvg,
            'date' => $this->date?->format('Y-m-d'),
            'image' => $this->image,
            'duty_position_id' => $this->duty_position_id,
            'mission_id' => $this->mission_id,
            'aircraft_models_id' => $this->aircraft_models_id,
            'seat' => $this->seat,
            'tail_number' => $this->tail_number,
            'departure_airport' => $this->departure_airport,
            'arrival_airport' => $this->arrival_airport,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'duty_position' => $this->whenLoaded('dutyPosition', fn() => [
                'id' => $this->dutyPosition?->id,
                'code' => $this->dutyPosition?->code,
                'label' => $this->dutyPosition?->label,
            ]),
            'mission' => $this->whenLoaded('mission', fn() => [
                'id' => $this->mission?->id,
                'code' => $this->mission?->code,
                'label' => $this->mission?->label,
            ]),
            'aircraft_model' => $this->whenLoaded('aircraftModel', fn() => [
                'id' => $this->aircraftModel?->id,
                'model' => $this->aircraftModel?->model,
            ]),
        ];
    }
}
