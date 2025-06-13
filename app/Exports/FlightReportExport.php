<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FlightReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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
        $data = [];

        foreach ($this->flights as $monthYear => $monthFlights) {
            $flightsByModel = $monthFlights->groupBy(function ($flight) {
                return $flight->aircraftModel->model ?? 'N/A';
            });

            $totalMonthHours = 0;

            foreach ($flightsByModel as $model => $modelFlights) {
                foreach ($modelFlights as $flight) {
                    $symbolMap = [
                        'nvs' => 'Ns',
                        'night' => 'N',
                        'day' => 'D',
                        'weather' => 'W',
                        'hood' => 'H',
                        'nvg' => 'NG'
                    ];

                    foreach ($symbolMap as $field => $symbol) {
                        $hours = $flight->$field;
                        if ($hours > 0) {
                            $data[] = [
                                'Month' => $monthYear,
                                'Model' => $flight->aircraftModel->model ?? 'N/A',
                                'Serial Number' => $flight->tail_number,
                                'Mission Date' => $flight->date->format('d M Y'),
                                'Duty Symbol' => $flight->dutyPosition->code ?? 'N/A',
                                'Flight Symbol' => $symbol,
                                'Hours Flown' => number_format($hours, 1),
                            ];
                        }
                    }

                    if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 && 
                        $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                        $data[] = [
                            'Month' => $monthYear,
                            'Model' => $flight->aircraftModel->model ?? 'N/A',
                            'Serial Number' => $flight->tail_number,
                            'Mission Date' => $flight->date->format('d M Y'),
                            'Duty Symbol' => $flight->dutyPosition->code ?? 'N/A',
                            'Flight Symbol' => 'NS',
                            'Hours Flown' => number_format($flight->hours_flown, 1),
                        ];
                    }
                }

                $totalHours = $modelFlights->sum(function ($flight) {
                    return $flight->nvs + $flight->night + $flight->day + $flight->weather + $flight->hood + $flight->nvg;
                });
                $data[] = [
                    'Month' => $monthYear,
                    'Model' => 'Total Flight Hours by Model: ' . $model,
                    'Serial Number' => '',
                    'Mission Date' => '',
                    'Duty Symbol' => '',
                    'Flight Symbol' => '',
                    'Hours Flown' => number_format($totalHours, 1),
                ];
                $totalMonthHours += $totalHours;
            }

            $data[] = [
                'Month' => $monthYear,
                'Model' => 'Total Flight Hours for Month of: ' . $monthYear,
                'Serial Number' => '',
                'Mission Date' => '',
                'Duty Symbol' => '',
                'Flight Symbol' => '',
                'Hours Flown' => number_format($totalMonthHours, 1),
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Month',
            'Model',
            'Serial Number',
            'Mission Date',
            'Duty Symbol',
            'Flight Symbol',
            'Hours Flown',
        ];
    }

    public function title(): string
    {
        return 'Flight Report - ' . $this->userName;
    }
}