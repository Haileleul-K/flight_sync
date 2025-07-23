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
            Log::info('Starting generateFlightReport', ['user_id' => Auth::id(), 'format' => $request->input('format', 'pdf')]);

            $user = Auth::user();
            if (!$user) {
                Log::error('User not authenticated');
                return response()->json(['error' => 'User not authenticated.'], 401);
            }

            $format = $request->input('format', 'pdf');

            Log::info('Fetching flights for user', ['user_id' => $user->id]);
            $flights = \App\Models\Flight::where('user_id', $user->id)
                ->with(['aircraftModel', 'dutyPosition', 'mission']) // Eager load relationships
                ->orderBy('date')
                ->get()
                ->groupBy(function ($flight) {
                    return $flight->date->format('F Y');
                });

            Log::info('Flights fetched', ['count' => $flights->flatten(1)->count()]);

            if ($format === 'json') {
                $data = $this->prepareFlightData($flights, $user->name);
                return response()->json($data);
            }

            $htmlContent = $this->generateHtmlReport($user->id, $flights);

            if ($format === 'pdf') {
                Log::info('Generating PDF report');
                try {
                    $pdf = SnappyPdf::loadHTML($htmlContent)
                        ->setOption('margin-top', '20mm')
                        ->setOption('margin-bottom', '15mm')
                        ->setOption('header-html', '')
                        ->setOption('footer-html', '');
                    Log::info('PDF generated successfully');
                    return $pdf->download('flight_report_' . str_replace(' ', '_', $user->name) . '.pdf');
                } catch (\Exception $e) {
                    Log::warning('wkhtmltopdf failed, falling back to DomPDF', ['error' => $e->getMessage()]);
                    try {
                        $pdf = Pdf::loadHTML($htmlContent)
                            ->setPaper('a4')
                            ->setOptions([
                                'marginTop' => 20,
                                'marginBottom' => 15,
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true,
                            ]);
                        Log::info('DomPDF generated successfully');
                        return $pdf->download('flight_report_' . str_replace(' ', '_', $user->name) . '.pdf');
                    } catch (\Exception $dompdfException) {
                        Log::error('DomPDF generation failed', ['error' => $dompdfException->getMessage(), 'trace' => $dompdfException->getTraceAsString()]);
                        throw new \Exception('Failed to generate PDF with DomPDF.');
                    }
                }
            } elseif ($format === 'excel') {
                Log::info('Generating Excel report');
                return Excel::download(new FlightReportExport($flights, $user->name), 'flight_report_' . str_replace(' ', '_', $user->name) . '.xlsx');
            }

            Log::error('Invalid format specified', ['format' => $format]);
            return response()->json(['error' => 'Invalid format specified. Use "pdf", "excel", or "json".'], 400);
        } catch (\Exception $e) {
            Log::error('Report Generation Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to generate report.'], 500);
        }
    }

    private function generateHtmlReport($userId, $flights)
    {
        try {
            Log::info('Generating HTML report', ['user_id' => $userId]);

            $earliestDate = $flights->isNotEmpty() ? $flights->flatten(1)->min('date')?->format('d M Y') : date('d M Y');
            $latestDate = $flights->isNotEmpty() ? $flights->flatten(1)->max('date')?->format('d M Y') : date('d M Y');
            $user = User::find($userId);
            if (!$user) {
                Log::error('User not found', ['user_id' => $userId]);
                throw new \Exception('User not found.');
            }
            $rank = Rank::find($user->rank_id);
            $pilotRankName = ($rank ? $rank->name : 'N/A') . ' ' . ($user->full_name ?? $user->name);
            $docCreationDate = date('d M Y');

            $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Report</title>
    <style>
        @page { size: A4; margin: 20mm 15mm 15mm 15mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 0; padding: 0; }
        .header { position: fixed; top: 0; left: 0; right: 0; width: 100%; font-size: 8pt; border-bottom: 0.4pt solid black; padding: 5px 0; }
        .header .top { text-align: center; font-weight: bold; margin-bottom: 2px; }
        .header .details { display: flex; justify-content: space-between; font-weight: normal; padding: 0 10px; }
        .title { text-align: center; font-size: 10pt; font-weight: bold; margin-top: 20px; }
        .pilot-info { text-align: left; font-size: 10pt; margin: 10px 0; padding-left: 10px; }
        .pilot-info .name { display: inline-block; width: 300px; font-weight: bold; }
        .pilot-info .period { display: inline-block; font-weight: normal; }
        .content { margin-top: 60px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 20px; page-break-inside: auto; }
        th, td { text-align: center; padding: 4px 6px; border: 1px solid black; }
        th { font-weight: bold; background-color: #f0f0f0; }
        .totals { font-weight: bold; }
        .summary { margin-top: 20px; font-size: 10pt; padding-left: 10px; }
        .summary div { margin: 5px 0; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; font-size: 8pt; text-align: center; border-top: 0.4pt solid black; padding: 5px 0; }
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
    <div class="title">2408-12 PERSONNEL SUMMARY</div>
    <div class="pilot-info">
        <span class="name">Name: $pilotRankName</span>
        <span class="period">Period: $earliestDate to $latestDate</span>
    </div>
    <div class="footer">Generated on $docCreationDate</div>
    <div class="content">
HTML;

            $flightsByModel = $flights->flatten(1)->groupBy(function ($flight) {
                $model = $flight->aircraftModel ? ($flight->aircraftModel->model ?? 'N/A') : 'N/A';
                return $model;
            })->sortKeys();

            $totalOverallHours = 0;
            $totalFrontSeatHours = 0;
            $totalBackSeatHours = 0;

            foreach ($flightsByModel as $model => $modelFlights) {
                $totalModelHours = 0;
                $html .= '<table><thead><tr>';
                $html .= '<th style="width: 15%;">ACFT</th>';
                $html .= '<th style="width: 20%;">DATE FLOWN</th>';
                $html .= '<th style="width: 10%;">DUTY</th>';
                $html .= '<th style="width: 15%;">CONDITION</th>';
                $html .= '<th style="width: 10%;">MISSION</th>';
                $html .= '<th style="width: 15%;">TIME FLOWN</th>';
                $html .= '<th style="width: 15%;">TOTAL</th>';
                $html .= '</tr></thead><tbody>';

                foreach ($modelFlights as $index => $flight) {
                    $aircraftModel = $flight->aircraftModel ? ($flight->aircraftModel->model ?? 'N/A') : 'N/A';
                    $dutySymbol = $flight->dutyPosition ? ($flight->dutyPosition->code ?? 'N/A') : 'N/A';
                    $mission = $flight->mission ? ($flight->mission->code ?? 'N/A') : 'N/A';
                    $symbolMap = ['nvs' => 'Ns', 'night' => 'N', 'day' => 'D', 'weather' => 'W', 'hood' => 'H', 'nvg' => 'NG'];

                    foreach ($symbolMap as $field => $symbol) {
                        $hours = $flight->$field; // No ?? 0 needed due to DECIMAL(5,1) default 0.0
                        if ($hours > 0) {
                            $totalModelHours += $hours;
                            $html .= '<tr>';
                            $html .= '<td>' . htmlspecialchars($aircraftModel) . '</td>';
                            $html .= '<td>' . ($flight->date ? $flight->date->format('d M Y') : 'N/A') . '</td>';
                            $html .= '<td>' . htmlspecialchars($dutySymbol) . '</td>';
                            $html .= '<td>' . $symbol . '</td>';
                            $html .= '<td>' . htmlspecialchars($mission) . '</td>';
                            $html .= '<td>' . number_format($hours, 1) . '</td>';
                            $html .= '<td></td>';
                            $html .= '</tr>';
                        }
                    }

                    if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 &&
                        $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                        $hours = $flight->hours_flown ?? 0.0;
                        if ($hours > 0) {
                            $totalModelHours += $hours;
                            $html .= '<tr>';
                            $html .= '<td>' . htmlspecialchars($aircraftModel) . '</td>';
                            $html .= '<td>' . ($flight->date ? $flight->date->format('d M Y') : 'N/A') . '</td>';
                            $html .= '<td>' . htmlspecialchars($dutySymbol) . '</td>';
                            $html .= '<td>NS</td>';
                            $html .= '<td>' . htmlspecialchars($mission) . '</td>';
                            $html .= '<td>' . number_format($hours, 1) . '</td>';
                            $html .= '<td></td>';
                            $html .= '</tr>';
                        }
                    }

                    if ($index === $modelFlights->count() - 1) {
                        $html .= '<tr class="totals">';
                        $html .= '<td colspan="6"></td>';
                        $html .= '<td>' . number_format($totalModelHours, 1) . '</td>';
                        $html .= '</tr>';

                        if (stripos($model, '(FS)') !== false) {
                            $totalFrontSeatHours += $totalModelHours;
                        } elseif (stripos($model, '(BS)') !== false) {
                            $totalBackSeatHours += $totalModelHours;
                        }
                        $totalOverallHours += $totalModelHours;
                    }
                }

                $html .= '</tbody></table>';
            }

            $html .= '<div class="summary">';
            $html .= '<div>Total Hours: ' . number_format($totalOverallHours, 1) . '</div>';
            $html .= '<div>Total Front Seat Hours: ' . number_format($totalFrontSeatHours, 1) . '</div>';
            $html .= '<div>Total Back Seat Hours: ' . number_format($totalBackSeatHours, 1) . '</div>';
            $html .= '</div>';

            $html .= '</div></body></html>';
            Log::info('HTML report generated successfully');
            return $html;
        } catch (\Exception $e) {
            Log::error('HTML Report Generation Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new \Exception('Failed to generate HTML report.');
        }
    }

    private function prepareFlightData($flights, $userName)
    {
        try {
            Log::info('Preparing JSON flight data', ['user_name' => $userName]);
            $data = [];
            $totalOverallHours = 0;
            $earliestDate = $flights->isNotEmpty() ? $flights->flatten(1)->min('date')?->format('d M Y') : date('d M Y');
            $latestDate = $flights->isNotEmpty() ? $flights->flatten(1)->max('date')?->format('d M Y') : date('d M Y');
            $docCreationDate = date('d M Y');

            foreach ($flights as $monthYear => $monthFlights) {
                $flightsByModel = $monthFlights->groupBy(function ($flight) {
                    return $flight->aircraftModel ? ($flight->aircraftModel->model ?? 'N/A') : 'N/A';
                });

                $monthData = [];
                $totalMonthHours = 0;

                foreach ($flightsByModel as $model => $modelFlights) {
                    $modelRows = [];
                    foreach ($modelFlights as $flight) {
                        $dutySymbol = $flight->dutyPosition ? ($flight->dutyPosition->code ?? 'N/A') : 'N/A';
                        $mission = $flight->mission ? ($flight->mission->code ?? 'N/A') : 'N/A';
                        $symbolMap = ['nvs' => 'Ns', 'night' => 'N', 'day' => 'D', 'weather' => 'W', 'hood' => 'H', 'nvg' => 'NG'];

                        foreach ($symbolMap as $field => $symbol) {
                            $hours = $flight->$field;
                            if ($hours > 0) {
                                $modelRows[] = [
                                    'model' => $model,
                                    'serial_number' => $flight->tail_number ?? 'N/A',
                                    'mission_date' => $flight->date ? $flight->date->format('d M Y') : 'N/A',
                                    'duty_symbol' => $dutySymbol,
                                    'flight_symbol' => $symbol,
                                    'mission' => $mission,
                                    'hours_flown' => number_format($hours, 1),
                                ];
                            }
                        }

                        if ($flight->nvs == 0 && $flight->night == 0 && $flight->day == 0 &&
                            $flight->weather == 0 && $flight->hood == 0 && $flight->nvg == 0) {
                            $hours = $flight->hours_flown ?? 0.0;
                            if ($hours > 0) {
                                $modelRows[] = [
                                    'model' => $model,
                                    'serial_number' => $flight->tail_number ?? 'N/A',
                                    'mission_date' => $flight->date ? $flight->date->format('d M Y') : 'N/A',
                                    'duty_symbol' => $dutySymbol,
                                    'flight_symbol' => 'NS',
                                    'mission' => $mission,
                                    'hours_flown' => number_format($hours, 1),
                                ];
                            }
                        }
                    }

                    $totalHours = $modelFlights->sum(function ($flight) {
                        return $flight->nvs + $flight->night + $flight->day +
                               $flight->weather + $flight->hood + $flight->nvg;
                    });
                    $modelRows[] = [
                        'model' => 'Total Flight Hours by Model: ' . $model,
                        'serial_number' => '',
                        'mission_date' => '',
                        'duty_symbol' => '',
                        'flight_symbol' => '',
                        'mission' => '',
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
                    'mission' => '',
                    'hours_flown' => number_format($totalMonthHours, 1),
                ];
                $data[$monthYear] = $monthData;
                $totalOverallHours += $totalMonthHours;
            }

            Log::info('JSON data prepared successfully');
            return [
                'user' => $userName,
                'date_range' => [$earliestDate, $latestDate],
                'creation_date' => $docCreationDate,
                'data' => $data,
                'total_overall_hours' => number_format($totalOverallHours, 1),
            ];
        } catch (\Exception $e) {
            Log::error('JSON Data Preparation Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new \Exception('Failed to prepare JSON data.');
        }
    }
}