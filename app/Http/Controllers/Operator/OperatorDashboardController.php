<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Pendapatan;
use App\Models\Rayon;
use App\Models\ModelPrediksi;
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

        return view('operator.dashboard', compact('metrics', 'incomes', 'chartLabels', 'chartActualValues', 'chartPredictGwoValues', 'chartPredictGsValues', 'rayons', 'performanceMetrics'));
    }

    public function getForecast(Request $request)
    {
        $days = (int) $request->input('days', 7);
        if (!in_array($days, [7, 14, 30])) {
            $days = 7;
        }

        // Check if there is an active trained model (GWO, Grid Search, or Default)
        $latestModel = ModelRun::where('status', 'success')
            ->whereIn('model_type', ['svr_gwo', 'svr_grid_search', 'svr_default'])
            ->orderByRaw("FIELD(model_type, 'svr_gwo', 'svr_grid_search', 'svr_default') ASC")
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestModel) {
            return response()->json([
                'success' => false,
                'message' => 'Model SVR belum dilatih. Harap lakukan training atau optimasi model terlebih dahulu untuk memproyeksikan estimasi pendapatan mendatang.'
            ], 422);
        }

        $latestDate = Pendapatan::max('tanggal');
        if (!$latestDate) {
            return response()->json([
                'success' => false,
                'message' => 'Dataset pendapatan masih kosong.'
            ], 422);
        }

        $startDate = Carbon::parse($latestDate)->addDay()->format('Y-m-d');
        $endDate = Carbon::parse($latestDate)->addDays($days)->format('Y-m-d');

        // Fetch national holidays in this range
        $holidays = HariLibur::where('tipe', 'Libur Nasional')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();

        try {
            $fastApiService = app(FastApiService::class);
            $response = $fastApiService->post('predict', [
                'tanggal_mulai' => $startDate,
                'tanggal_akhir' => $endDate,
                'daftar_libur_nasional' => $holidays,
                'rayon_id' => 0
            ]);

            if ($response === null || !isset($response['detail_harian'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal berkomunikasi dengan Python API atau model belum dilatih di server.'
                ], 500);
            }

            $labels = [];
            $values = [];
            foreach ($response['detail_harian'] as $item) {
                $labels[] = date('d M Y', strtotime($item['tanggal']));
                $values[] = (int) $item['pendapatan'];
            }

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'values' => $values,
                'total_forecast' => (int) $response['estimasi_total_pendapatan']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproyeksikan data: ' . $e->getMessage()
            ], 500);
        }
    }
}
