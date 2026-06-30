<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;
use App\Models\Rayon;
use App\Models\Pendapatan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OperatorLaporanController extends Controller
{
    public function index(Request $request)
    {
        $latestDate = Pendapatan::max('tanggal') ?? Carbon::now()->format('Y-m-d');
        $defaultStartDate = Carbon::parse($latestDate)->subDays(7)->format('Y-m-d');
        
        $startDate = $request->input('start_date', $defaultStartDate);
        $endDate = $request->input('end_date', $latestDate);
        $rayonId = (int)$request->input('rayon_id', 0);

        // Fetch rayons for filter
        $rayons = Rayon::all();

        // Find best run (SVR GWO) or standard SVR run
        $latestRun = ModelRun::where('model_type', 'svr_gwo')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latestRun) {
            $latestRun = ModelRun::where('model_type', 'svr_default')
                ->where('status', 'success')
                ->orderBy('id', 'desc')
                ->first();
        }

        $reports = [];
        $totalActual = 0;
        $totalPredicted = 0;
        $avgPctError = 0;
        
        $chartLabels = [];
        $chartActualValues = [];
        $chartPredictValues = [];

        if ($latestRun) {
            $query = $latestRun->predictionResults()
                ->whereBetween('tanggal', [$startDate, $endDate]);

            if ($rayonId > 0) {
                $query->where('rayon_id', $rayonId);
            }

            $predictionResults = $query->orderBy('tanggal', 'asc')->get();

            foreach ($predictionResults as $index => $pred) {
                $errorVal = $pred->actual_value - $pred->predicted_value;
                
                $reports[] = [
                    'tanggal' => $pred->tanggal,
                    'rayon' => $pred->rayon_name,
                    'aktual' => (double)$pred->actual_value,
                    'prediksi' => (double)$pred->predicted_value,
                    'error' => (double)$errorVal
                ];
                
                $totalActual += $pred->actual_value;
                $totalPredicted += $pred->predicted_value;
            }

            if ($predictionResults->count() > 0) {
                $avgPctError = $predictionResults->avg(function($p) {
                    $err = $p->actual_value - $p->predicted_value;
                    return $p->actual_value > 0 ? (abs($err) / $p->actual_value) * 100 : 0;
                });
                
                // Group by date for chart
                $groupedByDate = $predictionResults->groupBy('tanggal')->sortKeys();
                foreach ($groupedByDate as $date => $group) {
                    $chartLabels[] = date('d M Y', strtotime($date));
                    $chartActualValues[] = (int) $group->sum('actual_value');
                    $chartPredictValues[] = (int) $group->sum('predicted_value');
                }
            }
        }

        // Rayon Analysis (best, worst, avg daily deviation)
        $rayonStats = collect([]);
        $bestRayon = null;
        $worstRayon = null;
        $avgDailyDeviation = 0;

        if ($latestRun && count($reports) > 0) {
            $rayonStats = $latestRun->predictionResults()
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->when($rayonId > 0, fn($q) => $q->where('rayon_id', $rayonId))
                ->select(
                    'rayon_name',
                    DB::raw('AVG(percentage_error) as avg_mape'),
                    DB::raw('AVG(error_value) as avg_error'),
                    DB::raw('SUM(actual_value) as total_actual'),
                    DB::raw('SUM(predicted_value) as total_predicted')
                )
                ->groupBy('rayon_name')
                ->get();

            $bestRayon  = $rayonStats->sortBy('avg_mape')->first();
            $worstRayon = $rayonStats->sortByDesc('avg_mape')->first();

            $avgDailyDeviation = $latestRun->predictionResults()
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->when($rayonId > 0, fn($q) => $q->where('rayon_id', $rayonId))
                ->avg('error_value') ?? 0;
        }

        // Summary
        $summary = [
            'periode' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'total_data' => count($reports) . ' Hari',
            'total_aktual' => 'Rp ' . number_format($totalActual, 0, ',', '.'),
            'total_prediksi' => 'Rp ' . number_format($totalPredicted, 0, ',', '.')
        ];

        // Fetch metrics from ModelMetric
        $mae = '-';
        $rmse = '-';
        $mape = '-';
        $r2 = '-';

        if ($latestRun) {
            $metric = $latestRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($metric) {
                $mae = 'Rp ' . number_format($metric->mae, 0, ',', '.');
                $rmse = 'Rp ' . number_format($metric->rmse, 0, ',', '.');
                
                $statusAkurasi = 'Cukup Akurat';
                if ($metric->mape < 10) {
                    $statusAkurasi = 'Sangat Akurat';
                } elseif ($metric->mape <= 20) {
                    $statusAkurasi = 'Baik';
                }
                $mape = number_format($metric->mape, 2, ',', '.') . '% (' . $statusAkurasi . ')';
                
                $r2Status = $metric->r2_score >= 0.7 ? 'Model Kuat' : ($metric->r2_score >= 0.4 ? 'Model Cukup' : 'Model Lemah');
                $r2 = number_format($metric->r2_score, 2, ',', '.') . ' (' . $r2Status . ')';
            }
        }

        $metrics = [
            'mae' => $mae,
            'rmse' => $rmse,
            'mape' => $mape,
            'r2' => $r2
        ];

        return view('operator.laporan.index', compact(
            'summary', 
            'metrics', 
            'reports', 
            'rayons', 
            'startDate', 
            'endDate', 
            'rayonId',
            'chartLabels',
            'chartActualValues',
            'chartPredictValues',
            'bestRayon',
            'worstRayon',
            'avgDailyDeviation',
            'rayonStats'
        ));
    }

    public function exportPdf(Request $request)
    {
        $latestDate = Pendapatan::max('tanggal') ?? Carbon::now()->format('Y-m-d');
        $defaultStartDate = Carbon::parse($latestDate)->subDays(7)->format('Y-m-d');
        
        $startDate = $request->input('start_date', $defaultStartDate);
        $endDate = $request->input('end_date', $latestDate);
        $rayonId = (int)$request->input('rayon_id', 0);

        $rayonName = 'Semua Rayon';
        if ($rayonId > 0) {
            $rayon = Rayon::find($rayonId);
            if ($rayon) {
                $rayonName = $rayon->nama_rayon;
            }
        }

        $latestRun = ModelRun::where('model_type', 'svr_gwo')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latestRun) {
            $latestRun = ModelRun::where('model_type', 'svr_default')
                ->where('status', 'success')
                ->orderBy('id', 'desc')
                ->first();
        }

        $reports = [];
        $totalActual = 0;
        $totalPredicted = 0;
        $avgPctError = 0;

        if ($latestRun) {
            $query = $latestRun->predictionResults()
                ->whereBetween('tanggal', [$startDate, $endDate]);

            if ($rayonId > 0) {
                $query->where('rayon_id', $rayonId);
            }

            $predictionResults = $query->orderBy('tanggal', 'asc')->get();

            foreach ($predictionResults as $index => $pred) {
                $errorVal = $pred->actual_value - $pred->predicted_value;
                $pctError = $pred->actual_value > 0 ? (abs($errorVal) / $pred->actual_value) * 100 : 0;
                
                $reports[] = [
                    'no' => $index + 1,
                    'tanggal' => date('d-m-Y', strtotime($pred->tanggal)),
                    'rayon' => $pred->rayon_name,
                    'aktual' => (double)$pred->actual_value,
                    'prediksi' => (double)$pred->predicted_value,
                    'error' => (double)$errorVal,
                    'pct_error' => number_format($pctError, 2, ',', '.') . '%'
                ];
                
                $totalActual += $pred->actual_value;
                $totalPredicted += $pred->predicted_value;
            }

            if ($predictionResults->count() > 0) {
                $avgPctError = $predictionResults->avg(function($p) {
                    $err = $p->actual_value - $p->predicted_value;
                    return $p->actual_value > 0 ? (abs($err) / $p->actual_value) * 100 : 0;
                });
            }
        }

        $summary = [
            'periode' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'total_data' => count($reports) . ' Hari',
            'total_aktual' => 'Rp ' . number_format($totalActual, 0, ',', '.'),
            'total_prediksi' => 'Rp ' . number_format($totalPredicted, 0, ',', '.'),
            'mape' => number_format($avgPctError, 2, ',', '.') . '%'
        ];
        
        $totalErrorVal = $totalActual - $totalPredicted;
        $total_period = [
            'aktual' => 'Rp ' . number_format($totalActual, 0, ',', '.'),
            'prediksi' => 'Rp ' . number_format($totalPredicted, 0, ',', '.'),
            'error' => 'Rp ' . number_format(abs($totalErrorVal), 0, ',', '.'),
            'pct_error' => number_format($avgPctError, 2, ',', '.') . '%'
        ];

        // Fetch metrics from ModelMetric
        $mae = '-';
        $rmse = '-';
        $mape = '-';
        $r2 = '-';

        if ($latestRun) {
            $metric = $latestRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($metric) {
                $mae = 'Rp ' . number_format($metric->mae, 0, ',', '.');
                $rmse = 'Rp ' . number_format($metric->rmse, 0, ',', '.');
                
                $statusAkurasi = 'Cukup Akurat';
                if ($metric->mape < 10) {
                    $statusAkurasi = 'Sangat Akurat';
                } elseif ($metric->mape <= 20) {
                    $statusAkurasi = 'Baik';
                }
                $mape = number_format($metric->mape, 2, ',', '.') . '% (' . $statusAkurasi . ')';
                
                $r2Status = $metric->r2_score >= 0.7 ? 'Model Kuat' : ($metric->r2_score >= 0.4 ? 'Model Cukup' : 'Model Lemah');
                $r2 = number_format($metric->r2_score, 2, ',', '.') . ' (' . $r2Status . ')';
            }
        }

        $metrics = [
            'mae' => $mae,
            'rmse' => $rmse,
            'mape' => $mape,
            'r2' => $r2
        ];

        $pdf = Pdf::loadView('operator.laporan.pdf', compact(
            'summary', 
            'reports', 
            'total_period', 
            'metrics', 
            'startDate', 
            'endDate', 
            'rayonName'
        ));

        return $pdf->download('laporan-prediksi.pdf');
    }

    public function exportExcel()
    {
        return response()->streamDownload(function () {
            echo "Excel Laporan Prediksi Retribusi Parkir Dishub Cirebon (MOCK)";
        }, 'laporan-prediksi.xlsx');
    }
}

