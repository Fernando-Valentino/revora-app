<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Pendapatan;
use App\Models\Rayon;
use App\Models\ModelRun;
use App\Models\PredictionResult;
use App\Models\HariLibur;
use App\Services\FastApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperatorDashboardController extends Controller
{
    public function index()
    {
        // 1. Get latest date of pendapatan
        $latestDate = Pendapatan::max('tanggal');
        
        $totalPendapatanHarianVal = 0;
        $formattedLatestDate = '-';
        if ($latestDate) {
            $totalPendapatanHarianVal = Pendapatan::where('tanggal', $latestDate)->sum('jumlah');
            $formattedLatestDate = date('d-m-Y', strtotime($latestDate));
        }

        // 2. Count total records and average
        $totalData = Pendapatan::count();
        $averagePendapatan = Pendapatan::avg('jumlah') ?? 0;

        // 3. Model info (check if trained)
        $latestGwo = ModelRun::where('model_type', 'svr_gwo')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
        $latestGs = ModelRun::where('model_type', 'svr_grid_search')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
            
        $mapeGwo = 'Belum Dilatih';
        if ($latestGwo) {
            $gwoMetric = $latestGwo->modelMetrics()->where('dataset_type', 'test')->first();
            if ($gwoMetric) {
                $mape = $gwoMetric->mape;
                $category = 'Cukup Akurat';
                if ($mape < 10) {
                    $category = 'Sangat Akurat';
                } elseif ($mape <= 20) {
                    $category = 'Baik';
                } elseif ($mape <= 50) {
                    $category = 'Cukup Akurat';
                } else {
                    $category = 'Kurang Akurat';
                }
                $mapeGwo = number_format($mape, 2, ',', '.') . '% (' . $category . ')';
            }
        }
        
        $mapeGs = 'Belum Dilatih';
        if ($latestGs) {
            $gsMetric = $latestGs->modelMetrics()->where('dataset_type', 'test')->first();
            if ($gsMetric) {
                $mape = $gsMetric->mape;
                $category = 'Cukup Akurat';
                if ($mape < 10) {
                    $category = 'Sangat Akurat';
                } elseif ($mape <= 20) {
                    $category = 'Baik';
                } elseif ($mape <= 50) {
                    $category = 'Cukup Akurat';
                } else {
                    $category = 'Kurang Akurat';
                }
                $mapeGs = number_format($mape, 2, ',', '.') . '% (' . $category . ')';
            }
        }

        $metrics = [
            'total_pendapatan_harian' => 'Rp ' . number_format($totalPendapatanHarianVal, 0, ',', '.'),
            'tanggal_terkini' => $formattedLatestDate,
            'total_data' => number_format($totalData, 0, ',', '.') . ' Entri',
            'rata_rata' => 'Rp ' . number_format($averagePendapatan, 0, ',', '.'),
            'mape_gwo' => $mapeGwo,
            'mape_gs' => $mapeGs,
        ];

        // 4. Latest 5 Incomes from DB
        $incomes = Pendapatan::with(['rayon', 'juruParkir'])
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get();

        // 5. Chart Data (Last 10 days of record dates)
        $chartDataRaw = Pendapatan::selectRaw('tanggal, SUM(jumlah) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get()
            ->reverse();

        $chartLabels = [];
        $chartActualValues = [];
        $chartPredictGwoValues = [];
        $chartPredictGsValues = [];

        // Fetch actual GWO prediction results for these dates
        $gwoPredictions = [];
        if ($latestGwo) {
            $gwoPredictions = PredictionResult::where('model_run_id', $latestGwo->id)
                ->selectRaw('tanggal, SUM(predicted_value) as total_predicted')
                ->groupBy('tanggal')
                ->pluck('total_predicted', 'tanggal')
                ->toArray();
        }

        // Fetch actual Grid Search prediction results for these dates
        $gsPredictions = [];
        if ($latestGs) {
            $gsPredictions = PredictionResult::where('model_run_id', $latestGs->id)
                ->selectRaw('tanggal, SUM(predicted_value) as total_predicted')
                ->groupBy('tanggal')
                ->pluck('total_predicted', 'tanggal')
                ->toArray();
        }

        foreach ($chartDataRaw as $data) {
            $chartLabels[] = date('d M Y', strtotime($data->tanggal));
            
            $actual = (int)$data->total;
            $chartActualValues[] = $actual;
            
            $dateKey = $data->tanggal;
            
            // GWO prediction
            if ($latestGwo && isset($gwoPredictions[$dateKey])) {
                $chartPredictGwoValues[] = (int)$gwoPredictions[$dateKey];
            } else {
                $chartPredictGwoValues[] = null;
            }
            
            // Grid Search prediction
            if ($latestGs && isset($gsPredictions[$dateKey])) {
                $chartPredictGsValues[] = (int)$gsPredictions[$dateKey];
            } else {
                $chartPredictGsValues[] = null;
            }
        }

        // 6. Rayons for sidebar/summary panel
        $rayons = Rayon::withCount('pendapatans')->get();

        // 7. Model Performance Metrics (MAPE & R2) for Default, Grid Search, and GWO
        $latestDefault = ModelRun::where('model_type', 'svr_default')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
        
        $mapeDefaultVal = null;
        $r2DefaultVal = null;
        if ($latestDefault) {
            $defaultMetric = $latestDefault->modelMetrics()->where('dataset_type', 'test')->first();
            if ($defaultMetric) {
                $mapeDefaultVal = (float)$defaultMetric->mape;
                $r2DefaultVal = (float)$defaultMetric->r2_score;
            }
        }

        $mapeGsVal = null;
        $r2GsVal = null;
        if ($latestGs) {
            $gsMetric = $latestGs->modelMetrics()->where('dataset_type', 'test')->first();
            if ($gsMetric) {
                $mapeGsVal = (float)$gsMetric->mape;
                $r2GsVal = (float)$gsMetric->r2_score;
            }
        }

        $mapeGwoVal = null;
        $r2GwoVal = null;
        if ($latestGwo) {
            $gwoMetric = $latestGwo->modelMetrics()->where('dataset_type', 'test')->first();
            if ($gwoMetric) {
                $mapeGwoVal = (float)$gwoMetric->mape;
                $r2GwoVal = (float)$gwoMetric->r2_score;
            }
        }

        $performanceMetrics = [
            'mape_default' => $mapeDefaultVal,
            'r2_default'   => $r2DefaultVal,
            'mape_gs'      => $mapeGsVal,
            'r2_gs'        => $r2GsVal,
            'mape_gwo'     => $mapeGwoVal,
            'r2_gwo'       => $r2GwoVal,
        ];

        // Query the best model run (lowest MAPE)
        $bestModelRun = ModelRun::where('status', 'success')
            ->whereIn('model_type', ['svr_default', 'svr_grid_search', 'svr_gwo'])
            ->join('model_metrics', 'model_runs.id', '=', 'model_metrics.model_run_id')
            ->where('model_metrics.dataset_type', 'test')
            ->orderBy('model_metrics.mape', 'asc')
            ->select('model_runs.model_type', 'model_metrics.mape')
            ->first();
            
        $bestModelType = $bestModelRun ? $bestModelRun->model_type : 'svr_gwo';
        
        $typeMapping = [
            'svr_default' => 'baseline',
            'svr_grid_search' => 'grid_search',
            'svr_gwo' => 'gwo'
        ];
        
        $bestModelTypeParam = $typeMapping[$bestModelType] ?? 'gwo';

        return view('operator.dashboard', compact('metrics', 'incomes', 'chartLabels', 'chartActualValues', 'chartPredictGwoValues', 'chartPredictGsValues', 'rayons', 'performanceMetrics', 'bestModelTypeParam'));
    }

    public function getForecast(Request $request)
    {
        $days = (int) $request->input('days', 7);
        if (!in_array($days, [7, 30, 90, 365])) {
            $days = 7;
        }

        $modelTypeInput = $request->input('model_type');

        // Query the best model run (lowest MAPE)
        $bestModelRun = ModelRun::where('status', 'success')
            ->whereIn('model_type', ['svr_default', 'svr_grid_search', 'svr_gwo'])
            ->join('model_metrics', 'model_runs.id', '=', 'model_metrics.model_run_id')
            ->where('model_metrics.dataset_type', 'test')
            ->orderBy('model_metrics.mape', 'asc')
            ->select('model_runs.model_type', 'model_metrics.mape')
            ->first();
            
        $bestModelType = $bestModelRun ? $bestModelRun->model_type : 'svr_gwo';
        
        $typeMapping = [
            'svr_default' => 'baseline',
            'svr_grid_search' => 'grid_search',
            'svr_gwo' => 'gwo'
        ];
        
        $bestModelTypeParam = $typeMapping[$bestModelType] ?? 'gwo';
        $modelType = $modelTypeInput ?: $bestModelTypeParam;

        if (!in_array($modelType, ['baseline', 'grid_search', 'gwo'])) {
            $modelType = $bestModelTypeParam;
        }

        // Verify if the requested model has been trained, otherwise fallback to any trained model
        $mappedType = array_search($modelType, $typeMapping);
        $trainedModel = ModelRun::where('status', 'success')
            ->where('model_type', $mappedType)
            ->first();
            
        if (!$trainedModel) {
            $trainedModel = ModelRun::where('status', 'success')
                ->whereIn('model_type', ['svr_gwo', 'svr_grid_search', 'svr_default'])
                ->orderByRaw("FIELD(model_type, 'svr_gwo', 'svr_grid_search', 'svr_default') ASC")
                ->first();
                
            if ($trainedModel) {
                $modelType = $typeMapping[$trainedModel->model_type];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Model SVR belum dilatih. Harap lakukan training atau optimasi model terlebih dahulu.'
                ], 422);
            }
        }

        $latestDate = Pendapatan::max('tanggal');
        if (!$latestDate) {
            return response()->json([
                'success' => false,
                'message' => 'Dataset pendapatan masih kosong.'
            ], 422);
        }

        // Ambil 30 hari data pendapatan terakhir sebagai seed window
        $recentDates = Pendapatan::select('tanggal')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->take(30)
            ->pluck('tanggal')
            ->toArray();
            
        if (count($recentDates) < 30) {
            $seedDataQuery = Pendapatan::with(['rayon', 'juruParkir'])->orderBy('tanggal', 'asc');
        } else {
            $minDate = end($recentDates);
            $seedDataQuery = Pendapatan::with(['rayon', 'juruParkir'])
                ->where('tanggal', '>=', $minDate)
                ->orderBy('tanggal', 'asc');
        }
        
        $seedData = $seedDataQuery->get()->map(function($item) {
            return [
                'Tanggal' => $item->tanggal,
                'Rayon' => (int)$item->rayon_id,
                'Total_Pendapatan' => (double)$item->jumlah,
                'Jumlah_Jukir' => $item->juruParkir->jumlah_juru_parkir ?? ($item->rayon->jumlah_juru_parkir ?? 80),
            ];
        })->toArray();

        try {
            $fastApiService = app(FastApiService::class);
            $response = $fastApiService->post('api/v1/predict/forecast', [
                'rayon_id' => 0,
                'horizon_days' => $days,
                'model_type' => $modelType,
                'seed_data' => $seedData
            ]);

            if ($response === null || !isset($response['detail_harian'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal berkomunikasi dengan Python API atau model belum dilatih di server.'
                ], 500);
            }

            $dateSum = [];
            $confidenceNote = "";
            foreach ($response['detail_harian'] as $item) {
                $tgl = $item['tanggal'];
                $pred = $item['prediksi_rp'];
                if (!isset($dateSum[$tgl])) {
                    $dateSum[$tgl] = 0;
                }
                $dateSum[$tgl] += $pred;
                $confidenceNote = $item['confidence_note'];
            }

            $labels = [];
            $values = [];
            foreach ($dateSum as $tgl => $total) {
                $labels[] = date('d M Y', strtotime($tgl));
                $values[] = (int) $total;
            }

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'values' => $values,
                'total_forecast' => array_sum($values),
                'model_type' => $modelType,
                'confidence_note' => $confidenceNote,
                'best_model_type' => $bestModelTypeParam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproyeksikan data: ' . $e->getMessage()
            ], 500);
        }
    }
}
