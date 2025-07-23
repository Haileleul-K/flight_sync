<?php

namespace App\Exports;

use App\Models\AircraftModel;
use App\Models\DutyPosition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class FlightReportExport implements FromCollection, WithHeadings, WithTitle
{
    protected $flights;
    protected $userName;

    public function __construct($flights, $userName)
    {
        $this->flights = $flights;
        $this->userName = $userName;
    }

    public function collection()
    {
        $data = new Collection();

        foreach ($this->flights as $monthYear => $monthFlights) {
            $flightsByModel = $monthFlights->groupBy(function ($flight) {
                return AircraftModel::find($flight->aircraft_models_id)->model ?? 'N/A';
            });

            foreach ($flightsByModel as $model => $modelFlights) {
                foreach ($modelFlights as $flight) {
                    $aircraftModel = AircraftModel::find($flight->aircraft_models_id)->model ?? 'N/A';
                    $dutySymbol = DutyPosition::find($flight->duty_position_id)->code ?? 'N/A';
                    $symbolMap = ['nvs' => 'Ns', 'night' => 'N', 'day' => 'D', 'weather' => 'W', 'hood' => 'H', 'nvg' => 'NG'];

                    foreach ($symbolMap as $field => $symbol) {
                        $hours = $flight->$field;
                        if ($hours > 0) {
                            $data->push([
                                'ACFT' => $aircraftModel,
                                'DATE FLOWN' => $flight->date->format('d/m/Y'), // Changed to DD/MM/YYYY
                                'DUTY' => $dutySymbol,
                                'CONDITION' => $symbol,
                                'MISSION' => 'T', // Assuming 'T' as per your sample
                                'TIME FLOWN' => number_format($hours, 1),
                                'TOTAL' => 0, // Total column as per your sample
                            ]);
                        }
                    }

                    if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 && 
                        $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                        $data->push([
                            'ACFT' => $aircraftModel,
                            'DATE FLOWN' => $flight->date->format('d/m/Y'), // Changed to DD/MM/YYYY
                            'DUTY' => $dutySymbol,
                            'CONDITION' => 'NS',
                            'MISSION' => 'T', // Assuming 'T' as per your sample
                            'TIME FLOWN' => number_format($flight->hours_flown, 1),
                            'TOTAL' => 0, // Total column as per your sample
                        ]);
                    }
                }

                $totalHours = $modelFlights->sum(function ($flight) {
                    return $flight->nvs + $flight->night + $flight->day + $flight->weather + $flight->hood + $flight->nvg;
                });
                $data->push([
                    'ACFT' => 'Total Flight Hours by Model: ' . $model,
                    'DATE FLOWN' => '',
                    'DUTY' => '',
                    'CONDITION' => '',
                    'MISSION' => '',
                    'TIME FLOWN' => number_format($totalHours, 1),
                    'TOTAL' => '', // Leave blank or calculate if needed
                ]);

                if ($flightsByModel->keys()->last() === $model) {
                    $totalMonthHours = $flightsByModel->sum(function ($flights) {
                        return $flights->sum(function ($flight) {
                            return $flight->nvs + $flight->night + $flight->day + $flight->weather + $flight->hood + $flight->nvg;
                        });
                    });
                    $data->push([
                        'ACFT' => 'Total Flight Hours for Month of: ' . $monthYear,
                        'DATE FLOWN' => '',
                        'DUTY' => '',
                        'CONDITION' => '',
                        'MISSION' => '',
                        'TIME FLOWN' => number_format($totalMonthHours, 1),
                        'TOTAL' => '', // Leave blank or calculate if needed
                    ]);
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'ACFT',
            'DATE FLOWN',
            'DUTY',
            'CONDITION',
            'MISSION',
            'TIME FLOWN',
            'TOTAL',
        ];
    }

    public function title(): string
    {
        return 'Flight Report - ' . $this->userName;
    }
}