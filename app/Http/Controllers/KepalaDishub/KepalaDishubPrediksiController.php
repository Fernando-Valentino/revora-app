<?php

namespace App\Http\Controllers\KepalaDishub;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;
use App\Models\Rayon;
use App\Models\Pendapatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KepalaDishubPrediksiController extends Controller
{
    private function mapeCategory(float $mape): string
    {
        if ($mape < 10)  return 'Sangat Akurat';
        if ($mape <= 20) return 'Baik';
        if ($mape <= 50) return 'Cukup';
        return 'Buruk';
    }

    private function r2Category(float $r2): string
    {
        if ($r2 >= 0.67) return 'Model Kuat';
        if ($r2 >= 0.33) return 'Model Moderat';
        return 'Model Lemah';
    }

    public function index(Request $request)
    {
        // 1. Ambil data run SVR default terakhir jika ada
        $lastRun = ModelRun::where('model_type', 'svr_default')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
             
        $best_params = [
            'c' => '-',
            'epsilon' => '-',
            'gamma' => '-',
            'metode_terbaik' => 'SVR Standar (Default)'
        ];

        $metrics = [
            'mae' => '-',
            'rmse' => '-',
            'mape' => '-',
            'r2' => '-'
        ];

        $chartLabels = [];
        $chartActualValues = [];
        $chartPredictValues = [];
        $predictions = collect([]);

        if ($lastRun) {
            $param = $lastRun->modelParameter;
            if ($param) {
                $formatParam = function ($val, int $maxDecimals = 8): string {
                    if ($val === null || $val === '') return '-';
                    if (!is_numeric($val)) return $val;
                    $formatted = number_format((float)$val, $maxDecimals, ',', '.');
                    if (strpos($formatted, ',') !== false) {
                        $formatted = rtrim($formatted, '0');
                        $formatted = rtrim($formatted, ',');
                    }
                    return $formatted;
                };

                $best_params = [
                    'c' => $formatParam($param->c_value, 6),
                    'epsilon' => $formatParam($param->epsilon_value, 8),
                    'gamma' => $formatParam($param->gamma_value, 6),
                    'metode_terbaik' => 'SVR Standar (Default)'
                ];
            }

            $metricObj = $lastRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($metricObj) {
                $metrics = [
                    'mae' => 'Rp ' . number_format((float)$metricObj->mae, 0, ',', '.'),
                    'rmse' => 'Rp ' . number_format((float)$metricObj->rmse, 0, ',', '.'),
                    'mape' => number_format((float)$metricObj->mape, 2, ',', '.') . '% (' . $this->mapeCategory($metricObj->mape) . ')',
                    'r2' => number_format((float)$metricObj->r2_score, 4, ',', '.') . ' (' . $this->r2Category($metricObj->r2_score) . ')'
                ];
            }

            // Query chart data
            $chartData = collect([]);
            if ($request->filled('rayon_id') && $request->rayon_id > 0) {
                $chartData = $lastRun->predictionResults()
                    ->where('rayon_id', $request->rayon_id)
                    ->orderBy('tanggal', 'asc')
                    ->get();
            } else {
                $chartData = $lastRun->predictionResults()
                    ->select('tanggal', DB::raw('SUM(actual_value) as actual_value'), DB::raw('SUM(predicted_value) as predicted_value'))
                    ->groupBy('tanggal')
                    ->orderBy('tanggal', 'asc')
                    ->get();
            }

            foreach ($chartData as $data) {
                $chartLabels[] = date('d M Y', strtotime($data->tanggal));
                $chartActualValues[] = (int)$data->actual_value;
                $chartPredictValues[] = (int)$data->predicted_value;
            }
                
            // Query paginated predictions for table
            $predictionsQuery = $lastRun->predictionResults()->orderBy('tanggal', 'desc');
            if ($request->filled('rayon_id') && $request->rayon_id > 0) {
                $predictionsQuery->where('rayon_id', $request->rayon_id);
            }
            $predictions = $predictionsQuery->paginate(10)->withQueryString();
        }

        $rayons = Rayon::all();

        return view('kepala-dishub.prediksi.index', compact('best_params', 'metrics', 'predictions', 'chartLabels', 'chartActualValues', 'chartPredictValues', 'rayons', 'lastRun'));
    }
}
