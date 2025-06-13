<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Rank;
use App\Models\AircraftModel;
use App\Models\DutyPosition;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FlightReportExport;

class FlightDocGenerator extends Controller
{
    public function generateFlightReport(Request $request)
    {
        try {
            $user = Auth::user();
            $format = $request->input('format', 'pdf'); // Default to 'pdf' if not specified

            $flights = \App\Models\Flight::where('user_id', $user->id)
                ->orderBy('date')
                ->get()
                ->groupBy(function ($flight) {
                    return $flight->date->format('F Y');
                });

            if ($format === 'json') {
                $data = $this->prepareFlightData($flights, $user->name);
                return response()->json($data);
            }

            $htmlContent = $this->generateHtmlReport($user->id, $flights);

            if ($format === 'pdf') {
                try {
                    $pdf = SnappyPdf::loadHTML($htmlContent)
                        ->setOption('margin-top', '20mm')
                        ->setOption('margin-bottom', '15mm')
                        ->setOption('header-html', '<div></div>')
                        ->setOption('footer-html', '<div></div>');
                    return $pdf->download('flight_report_' . $user->name . '.pdf');
                } catch (\Exception $e) {
                    Log::warning('wkhtmltopdf failed, falling back to DomPDF: ' . $e->getMessage());
                    $pdf = Pdf::loadHTML($htmlContent)
                        ->setPaper('a4')
                        ->setOptions([
                            'marginTop' => 20,
                            'marginBottom' => 15,
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true
                        ]);
                    return $pdf->download('flight_report_' . $user->name . '.pdf');
                }
            } elseif ($format === 'excel') {
                return Excel::download(new FlightReportExport($flights, $user->name), 'flight_report_' . $user->name . '.xlsx');
            }

            return response()->json(['error' => 'Invalid format specified. Use "pdf", "excel", or "json".'], 400);
        } catch (\Exception $e) {
            Log::error('Report Generation Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate report.'], 500);
        }
    }

    private function generateHtmlReport($userId, $flights)
    {
        $earliestDate = $flights->isNotEmpty() ? $flights->keys()->sort()->first() : date('F Y');
        $latestDate = $flights->isNotEmpty() ? $flights->keys()->sort()->last() : date('F Y');
        $user = User::find($userId);
        $rank = Rank::find($user->rank_id);
        $pilotRankName = '<strong>' . $rank->name . ' ' . $user->full_name . '</strong>';
        $docCreationDate = date('d M Y'); // Updated to 10 Jun 2025

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: A4; margin: 20mm 15mm 15mm 15mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 0; padding: 0; }
        .header { position: fixed; top: 0; left: 0; right: 0; width: 100%; font-size: 8pt; border-bottom: 0.4pt solid black; padding: 5px 0; }
        .header .top { text-align: center; font-weight: bold; margin-bottom: 2px; }
        .header .details { display: flex; justify-content: space-between; font-weight: normal; }
        .rank-name { position: fixed; top: 30px; width: 100%; font-size: 8pt; text-align: center; margin-bottom: 10px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; font-size: 8pt; text-align: center; border-top: 0.4pt solid black; padding: 5px 0; }
        .content { margin-top: 50px; margin-bottom: 30px; }
        .section-title { text-align: center; font-weight: bold; font-size: 10pt; margin: 10px 0 5px 0; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 10px; page-break-inside: avoid; }
        th, td { text-align: center; padding: 2px 4px; border: 1px solid black; }
        th { font-weight: bold; }
        .totals { text-align: left; font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="top">UNCLASSIFIED</div>
        <div class="details">
            <span>Date: $docCreationDate</span>
            <span>$earliestDate to $latestDate</span>
            <span>Page: <span class="page-number"></span></span>
        </div>
    </div>
    <div class="rank-name">$pilotRankName</div>
    <div class="footer">Generated on $docCreationDate</div>
    <div class="content">
HTML;

        foreach ($flights as $monthYear => $monthFlights) {
            $flightsByModel = $monthFlights->groupBy(function ($flight) {
                return AircraftModel::find($flight->aircraft_models_id)->model ?? 'N/A';
            });

            $html .= '<div class="section-title">Individual Flight Hours by MDS and Month for ' . $monthYear . '</div>';
            $totalMonthHours = 0;
            
            foreach ($flightsByModel as $model => $modelFlights) {
                $html .= '<table><thead><tr>';
                $html .= '<th style="width: 15%;">Model</th><th style="width: 20%;">Serial<br>Number</th>';
                $html .= '<th style="width: 15%;">Mission<br>Date</th><th style="width: 15%;">Duty<br>Symbol</th>';
                $html .= '<th style="width: 15%;">Flight<br>Symbol</th><th style="width: 10%;">HoursFlown</th>';
                $html .= '</tr></thead><tbody>';

                foreach ($modelFlights as $flight) {
                    $aircraftModel = AircraftModel::find($flight->aircraft_models_id)->model ?? 'N/A';
                    $dutySymbol = DutyPosition::find($flight->duty_position_id)->code ?? 'N/A';
                    $symbolMap = ['nvs' => 'Ns', 'night' => 'N', 'day' => 'D', 'weather' => 'W', 'hood' => 'H', 'nvg' => 'NG'];

                    foreach ($symbolMap as $field => $symbol) {
                        $hours = $flight->$field;
                        if ($hours > 0) {
                            $html .= '<tr><td>' . $aircraftModel . '</td><td>' . $flight->tail_number . '</td>';
                            $html .= '<td>' . $flight->date->format('d M Y') . '</td><td>' . $dutySymbol . '</td>';
                            $html .= '<td>' . $symbol . '</td><td>' . number_format($hours, 1) . '</td></tr>';
                        }
                    }

                    if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 && 
                        $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                        $html .= '<tr><td>' . $aircraftModel . '</td><td>' . $flight->tail_number . '</td>';
                        $html .= '<td>' . $flight->date->format('d M Y') . '</td><td>' . $dutySymbol . '</td>';
                        $html .= '<td>NS</td><td>' . number_format($flight->hours_flown, 1) . '</td></tr>';
                    }
                }

                $totalHours = $modelFlights->sum(function ($flight) {
                    return $flight->nvs + $flight->night + $flight->day + $flight->weather + $flight->hood + $flight->nvg;
                });
                $html .= '<tr class="totals"><td colspan="4">Total Flight Hours by Model: ' . $model . '</td>';
                $html .= '<td></td><td>' . number_format($totalHours, 1) . '</td></tr>';

                if ($flightsByModel->keys()->last() === $model) {
                    $html .= '<tr class="totals"><td colspan="4">Total Flight Hours for Month of: ' . $monthYear . '</td>';
                    $html .= '<td></td><td>' . number_format($totalMonthHours + $totalHours, 1) . '</td></tr>';
                }
                $html .= '</tbody></table>';

                $totalMonthHours += $totalHours;
            }
        }

        $html .= '</div></body></html>';
        return $html;
    }

    private function prepareFlightData($flights, $userName)
    {
        $data = [];
        $totalOverallHours = 0;

        foreach ($flights as $monthYear => $monthFlights) {
            $flightsByModel = $monthFlights->groupBy(function ($flight) {
                return AircraftModel::find($flight->aircraft_models_id)->model ?? 'N/A';
            });

            $monthData = [];
            $totalMonthHours = 0;

            foreach ($flightsByModel as $model => $modelFlights) {
                $modelRows = [];
                foreach ($modelFlights as $flight) {
                    $dutySymbol = DutyPosition::find($flight->duty_position_id)->code ?? 'N/A';
                    $symbolMap = ['nvs' => 'Ns', 'night' => 'N', 'day' => 'D', 'weather' => 'W', 'hood' => 'H', 'nvg' => 'NG'];

                    foreach ($symbolMap as $field => $symbol) {
                        $hours = $flight->$field;
                        if ($hours > 0) {
                            $modelRows[] = [
                                'model' => $flight->aircraftModel->model ?? 'N/A',
                                'serial_number' => $flight->tail_number,
                                'mission_date' => $flight->date->format('d M Y'),
                                'duty_symbol' => $dutySymbol,
                                'flight_symbol' => $symbol,
                                'hours_flown' => number_format($hours, 1),
                            ];
                        }
                    }

                    if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 && 
                        $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                        $modelRows[] = [
                            'model' => $flight->aircraftModel->model ?? 'N/A',
                            'serial_number' => $flight->tail_number,
                            'mission_date' => $flight->date->format('d M Y'),
                            'duty_symbol' => $dutySymbol,
                            'flight_symbol' => 'NS',
                            'hours_flown' => number_format($flight->hours_flown, 1),
                        ];
                    }
                }

                $totalHours = $modelFlights->sum(function ($flight) {
                    return $flight->nvs + $flight->night + $flight->day + $flight->weather + $flight->hood + $flight->nvg;
                });
                $modelRows[] = [
                    'model' => 'Total Flight Hours by Model: ' . $model,
                    'serial_number' => '',
                    'mission_date' => '',
                    'duty_symbol' => '',
                    'flight_symbol' => '',
                    'hours_flown' => number_format($totalHours, 1),
                ];
                $monthData = array_merge($monthData, $modelRows);
                $totalMonthHours += $totalHours;
            }

            $monthData[] = [
                'model' => 'Total Flight Hours for Month of: ' . $monthYear,
                'serial_number' => '',
                'mission_date' => '',
                'duty_symbol' => '',
                'flight_symbol' => '',
                'hours_flown' => number_format($totalMonthHours, 1),
            ];
            $data[$monthYear] = $monthData;
            $totalOverallHours += $totalMonthHours;
        }

        return [
            'user' => $userName,
            'date_range' => [$earliestDate, $latestDate],
            'creation_date' => $docCreationDate,
            'data' => $data,
            'total_overall_hours' => number_format($totalOverallHours, 1),
        ];
    }
}