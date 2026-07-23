<?php

namespace App\Http\Controllers\KepalaUpt;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;
use App\Models\Rayon;
use App\Models\Pendapatan;
use App\Models\PredictionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FastApiService;
use Barryvdh\DomPDF\Facade\Pdf;

class KepalaUptLaporanController extends Controller
{
    private function getReportData(Request $request)
    {
        Carbon::setLocale('id');
        $latestDate = Pendapatan::max('tanggal') ?? Carbon::now()->format('Y-m-d');
        $type = $request->input('type', 'harian');
        
        if ($type === 'bulanan') {
            $defaultStartDate = Carbon::parse($latestDate)->subMonths(5)->startOfMonth()->format('Y-m-d');
        } elseif ($type === 'tahunan') {
            $defaultStartDate = Carbon::parse($latestDate)->subYears(2)->startOfYear()->format('Y-m-d');
        } elseif ($type === 'mingguan') {
            $defaultStartDate = Carbon::parse($latestDate)->subWeeks(7)->startOfWeek()->format('Y-m-d');
        } else {
            $defaultStartDate = Carbon::parse($latestDate)->subDays(7)->format('Y-m-d');
        }
        
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

        $predictionResults = collect([]);
        $lastKnownDate = Pendapatan::max('tanggal') ?? '2025-07-20';
        $hasFutureDates = Carbon::parse($endDate)->gt(Carbon::parse($lastKnownDate));

        if ($latestRun) {
            $query = $latestRun->predictionResults()
                ->whereBetween('tanggal', [$startDate, $endDate]);

            if ($rayonId > 0) {
                $query->where('rayon_id', $rayonId);
            }

            $predictionResults = $query->orderBy('tanggal', 'asc')->get();
        }

        if ($hasFutureDates) {
            $daysToPredict = Carbon::parse($lastKnownDate)->diffInDays(Carbon::parse($endDate));
            if ($daysToPredict > 0) {
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
                
                $typeMapping = [
                    'svr_default' => 'baseline',
                    'svr_grid_search' => 'grid_search',
                    'svr_gwo' => 'gwo'
                ];
                $bestModelRun = ModelRun::where('status', 'success')
                    ->whereIn('model_type', ['svr_default', 'svr_grid_search', 'svr_gwo'])
                    ->join('model_metrics', 'model_runs.id', '=', 'model_metrics.model_run_id')
                    ->where('model_metrics.dataset_type', 'test')
                    ->orderBy('model_metrics.mape', 'asc')
                    ->select('model_runs.model_type')
                    ->first();
                $bestModelType = $bestModelRun ? $bestModelRun->model_type : 'svr_gwo';
                $modelType = $typeMapping[$bestModelType] ?? 'gwo';

                try {
                    $fastApiService = app(FastApiService::class);
                    $forecastRes = $fastApiService->post('api/v1/predict/forecast', [
                        'rayon_id' => $rayonId > 0 ? $rayonId : 0,
                        'horizon_days' => $daysToPredict,
                        'model_type' => $modelType,
                        'seed_data' => $seedData
                    ]);
                    
                    if ($forecastRes && isset($forecastRes['status']) && $forecastRes['status'] === 'Sukses') {
                        $forecastData = $forecastRes['detail_harian'];
                        $futurePredictions = collect([]);
                        foreach ($forecastData as $item) {
                            $itemDate = $item['tanggal'];
                            if (Carbon::parse($itemDate)->between(Carbon::parse($startDate), Carbon::parse($endDate))) {
                                if (Carbon::parse($itemDate)->gt(Carbon::parse($lastKnownDate))) {
                                    $mockPred = new \stdClass();
                                    $mockPred->tanggal = $itemDate;
                                    $mockPred->rayon_id = $item['rayon_id'];
                                    $mockPred->rayon_name = $item['rayon'];
                                    $mockPred->actual_value = 0.0;
                                    $mockPred->predicted_value = $item['prediksi_rp'];
                                    $mockPred->percentage_error = 0.0;
                                    $mockPred->error_value = 0.0;
                                    $futurePredictions->push($mockPred);
                                }
                            }
                        }
                        $predictionResults = $predictionResults->concat($futurePredictions);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch recursive forecast in getReportData: " . $e->getMessage());
                }
            }
        }

        if ($predictionResults->count() > 0) {
            // Group the prediction results in PHP
            $grouped = [];
            foreach ($predictionResults as $pred) {
                $carbonDate = Carbon::parse($pred->tanggal);
                    if ($type === 'mingguan') {
                        $startOfWeek = $carbonDate->copy()->startOfWeek();
                        $endOfWeek = $carbonDate->copy()->endOfWeek();
                        $key = $startOfWeek->format('Y-m-d');
                        $label = $startOfWeek->translatedFormat('d M Y') . ' - ' . $endOfWeek->translatedFormat('d M Y');
                        $chartLabel = $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M Y');
                    } elseif ($type === 'bulanan') {
                        $key = $carbonDate->format('Y-m');
                        $label = $carbonDate->translatedFormat('F Y');
                        $chartLabel = $carbonDate->translatedFormat('M Y');
                    } elseif ($type === 'tahunan') {
                        $key = $carbonDate->format('Y');
                        $label = $carbonDate->translatedFormat('Y');
                        $chartLabel = $carbonDate->translatedFormat('Y');
                    } else {
                        $key = $pred->tanggal;
                        $label = $carbonDate->translatedFormat('d M Y');
                        $chartLabel = $carbonDate->translatedFormat('d M Y');
                    }

                    // Group by period key and rayon ID to keep rayon-specific rows
                    $groupKey = $key . '_' . $pred->rayon_id;

                    if (!isset($grouped[$groupKey])) {
                        $grouped[$groupKey] = [
                            'tanggal_raw' => $key,
                            'tanggal' => $label,
                            'chart_label' => $chartLabel,
                            'rayon' => $pred->rayon_name,
                            'rayon_id' => $pred->rayon_id,
                            'aktual' => 0.0,
                            'prediksi' => 0.0,
                        ];
                    }

                    $grouped[$groupKey]['aktual'] += (double)$pred->actual_value;
                    $grouped[$groupKey]['prediksi'] += (double)$pred->predicted_value;
                }

                // Map to reports array
                $index = 1;
                foreach ($grouped as $gKey => $gData) {
                    $errorVal = $gData['aktual'] - $gData['prediksi'];
                    $pctError = $gData['aktual'] > 0 ? (abs($errorVal) / $gData['aktual']) * 100 : 0;

                    $reports[] = [
                        'no' => $index++,
                        'tanggal' => $gData['tanggal_raw'],
                        'tanggal_formatted' => $gData['tanggal'],
                        'chart_label' => $gData['chart_label'],
                        'rayon' => $gData['rayon'],
                        'rayon_id' => $gData['rayon_id'],
                        'aktual' => $gData['aktual'],
                        'prediksi' => $gData['prediksi'],
                        'error' => $errorVal,
                        'pct_error' => number_format($pctError, 2, ',', '.') . '%'
                    ];

                    $totalActual += $gData['aktual'];
                    $totalPredicted += $gData['prediksi'];
                }

                // Sort reports by raw date ascending
                usort($reports, function($a, $b) {
                    return strcmp($a['tanggal'], $b['tanggal']);
                });

                // Re-number after sorting
                foreach ($reports as $idx => &$rep) {
                    $rep['no'] = $idx + 1;
                }
                unset($rep);

                // Group by period key for chart (across all rayons in that period)
                $chartGrouped = [];
                foreach ($grouped as $gData) {
                    $key = $gData['tanggal_raw'];
                    if (!isset($chartGrouped[$key])) {
                        $chartGrouped[$key] = [
                            'label' => $gData['chart_label'],
                            'aktual' => 0,
                            'prediksi' => 0,
                        ];
                    }
                    $chartGrouped[$key]['aktual'] += $gData['aktual'];
                    $chartGrouped[$key]['prediksi'] += $gData['prediksi'];
                }

                ksort($chartGrouped);

                foreach ($chartGrouped as $key => $data) {
                    $chartLabels[] = $data['label'];
                    $chartActualValues[] = (int) $data['aktual'];
                    $chartPredictValues[] = (int) $data['prediksi'];
                }

                // Calculate overall MAPE of grouped reports
                if (count($reports) > 0) {
                    $avgPctError = collect($reports)->avg(function($r) {
                        return $r['aktual'] > 0 ? (abs($r['error']) / $r['aktual']) * 100 : 0;
                    });
                }
            }

        // Calculate average period deviation
        $avgPeriodDeviation = 0;
        if (count($reports) > 0) {
            $avgPeriodDeviation = collect($reports)->avg(function($r) {
                return abs($r['error']);
            });
        }

        // Rayon stats or details
        $rayonStats = collect([]);
        $bestRayon = null;
        $worstRayon = null;
        $avgDailyDeviation = 0;

        if ($latestRun && $predictionResults->count() > 0) {
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

        $unit = 'Hari';
        if ($type === 'mingguan') {
            $unit = 'Minggu';
        } elseif ($type === 'bulanan') {
            $unit = 'Bulan';
        } elseif ($type === 'tahunan') {
            $unit = 'Tahun';
        }

        $summary = [
            'periode' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'total_data' => count($reports) . ' ' . $unit,
            'total_aktual' => 'Rp ' . number_format($totalActual, 0, ',', '.'),
            'total_aktual_val' => $totalActual,
            'total_prediksi' => 'Rp ' . number_format($totalPredicted, 0, ',', '.'),
            'mape' => number_format($avgPctError, 2, ',', '.') . '%'
        ];

        return compact(
            'summary',
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
            'avgPeriodDeviation',
            'rayonStats',
            'latestRun',
            'type',
            'totalActual',
            'totalPredicted',
            'avgPctError'
        );
    }

    public function index(Request $request)
    {
        $data = $this->getReportData($request);

        $avgDailyActual = 0;
        $avgDailyPredicted = 0;
        $avgDailyDeviation = 0;

        if (count($data['chartActualValues']) > 0) {
            $avgDailyActual = $data['totalActual'] / count($data['chartActualValues']);
            $avgDailyPredicted = $data['totalPredicted'] / count($data['chartPredictValues']);

            $deviations = [];
            foreach ($data['chartActualValues'] as $i => $actVal) {
                $predVal = $data['chartPredictValues'][$i] ?? 0;
                $deviations[] = abs($actVal - $predVal);
            }
            $avgDailyDeviation = array_sum($deviations) / count($deviations);
        }

        $statusAkurasi = 'Cukup Akurat (Perlu Pemantauan)';
        $keteranganAkurasi = 'Model peramalan memiliki tingkat kesalahan sebesar ' . $data['summary']['mape'] . '. Direkomendasikan bagi Operator untuk melakukan pelatihan ulang model agar lebih presisi.';

        if ($data['avgPctError'] < 10) {
            $statusAkurasi = 'Sangat Akurat (Presisi Tinggi)';
            $keteranganAkurasi = 'Model peramalan sangat presisi dengan tingkat kesalahan harian hanya ' . $data['summary']['mape'] . '. Sangat andal digunakan sebagai acuan penetapan target retribusi parkir harian.';
        } elseif ($data['avgPctError'] <= 20) {
            $statusAkurasi = 'Baik (Andal)';
            $keteranganAkurasi = 'Model peramalan memiliki tingkat kesalahan rendah sebesar ' . $data['summary']['mape'] . '. Layak dijadikan panduan resmi target setoran jukir di lapangan.';
        }

        $analysis = [
            'avg_actual' => 'Rp ' . number_format($avgDailyActual, 0, ',', '.'),
            'avg_predict' => 'Rp ' . number_format($avgDailyPredicted, 0, ',', '.'),
            'avg_deviation' => 'Rp ' . number_format($avgDailyDeviation, 0, ',', '.'),
            'status_akurasi' => $statusAkurasi,
            'keterangan_akurasi' => $keteranganAkurasi
        ];

        $totalErrorVal = $data['totalActual'] - $data['totalPredicted'];
        $total_period = [
            'aktual' => 'Rp ' . number_format($data['totalActual'], 0, ',', '.'),
            'prediksi' => 'Rp ' . number_format($data['totalPredicted'], 0, ',', '.'),
            'error' => 'Rp ' . number_format(abs($totalErrorVal), 0, ',', '.'),
            'pct_error' => number_format($data['avgPctError'], 2, ',', '.') . '%'
        ];

        // Fetch metrics from ModelMetric
        $mae = '-';
        $rmse = '-';
        $mape = '-';
        $r2 = '-';

        if ($data['latestRun']) {
            $metric = $data['latestRun']->modelMetrics()->where('dataset_type', 'test')->first();
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

        $futureForecast = null;

        return view('kepala-upt.laporan.index', array_merge($data, [
            'analysis' => $analysis,
            'total_period' => $total_period,
            'metrics' => $metrics,
            'futureForecast' => $futureForecast
        ]));
    }

    public function getForecastData(Request $request)
    {
        $rayonId = (int)$request->input('rayon_id', 0);
        $type = $request->input('type', 'harian');
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

        $mappedType = array_search($modelType, $typeMapping);
        $latestRun = ModelRun::where('model_type', $mappedType)
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latestRun) {
            $latestRun = ModelRun::where('status', 'success')
                ->whereIn('model_type', ['svr_gwo', 'svr_grid_search', 'svr_default'])
                ->orderByRaw("FIELD(model_type, 'svr_gwo', 'svr_grid_search', 'svr_default') ASC")
                ->first();
                
            if ($latestRun) {
                $modelType = $typeMapping[$latestRun->model_type];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Model SVR belum dilatih di server.'
                ]);
            }
        }

        $cacheKey = "laporan_forecast_rayon_{$rayonId}_run_{$latestRun->id}_type_{$type}_model_{$modelType}";
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($latestRun, $rayonId, $type, $modelType, $bestModelTypeParam) {
            return $this->getFutureForecast($latestRun, $rayonId, $type, $modelType, $bestModelTypeParam);
        });

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal berkomunikasi dengan Python API atau model belum dilatih di server.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getFutureForecast(?ModelRun $latestRun, int $rayonId, string $type, string $modelType, string $bestModelTypeParam): ?array
    {
        if (!$latestRun) {
            return null;
        }

        $lastKnownDate = Pendapatan::max('tanggal') ?? '2025-07-20';
        $futureStart = Carbon::parse($lastKnownDate)->addDay()->format('Y-m-d');
        
        $daysToPredict = 7;
        $title = 'PREDIKSI 1 MINGGU KE DEPAN';
        $detailLabel = 'Detail Proyeksi Harian';
        $unitLabel = ' / hari';
        
        if ($type === 'mingguan') {
            $daysToPredict = 28;
            $title = 'PREDIKSI 4 MINGGU KE DEPAN';
            $detailLabel = 'Detail Proyeksi Mingguan';
            $unitLabel = ' / minggu';
        } elseif ($type === 'bulanan') {
            $daysToPredict = 90;
            $title = 'PREDIKSI 3 BULAN KE DEPAN';
            $detailLabel = 'Detail Proyeksi Bulanan';
            $unitLabel = ' / bulan';
        } elseif ($type === 'tahunan') {
            $daysToPredict = 365;
            $title = 'PREDIKSI 1 TAHUN KE DEPAN';
            $detailLabel = 'Detail Proyeksi Tahunan';
            $unitLabel = ' / tahun';
        }
        
        $futureEnd = Carbon::parse($lastKnownDate)->addDays($daysToPredict)->format('Y-m-d');
        
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
            $forecastRes = $fastApiService->post('api/v1/predict/forecast', [
                'rayon_id' => $rayonId > 0 ? $rayonId : 0,
                'horizon_days' => $daysToPredict,
                'model_type' => $modelType,
                'seed_data' => $seedData
            ]);
            
            if ($forecastRes && isset($forecastRes['status']) && $forecastRes['status'] === 'Sukses') {
                $detailHarian = $forecastRes['detail_harian'];
                
                $formattedDetails = [];
                if ($type === 'harian') {
                    foreach ($detailHarian as $day) {
                        $formattedDetails[] = [
                            'tanggal' => $day['tanggal'],
                            'label' => Carbon::parse($day['tanggal'])->translatedFormat('l, d M Y'),
                            'pendapatan' => $day['prediksi_rp'],
                            'source_features' => $day['source_features'] ?? 'recursive',
                            'confidence' => $day['confidence'] ?? 'Sedang',
                            'confidence_note' => $day['confidence_note'] ?? ''
                        ];
                    }
                } elseif ($type === 'mingguan') {
                    $grouped = [];
                    foreach ($detailHarian as $day) {
                        $carbonDate = Carbon::parse($day['tanggal']);
                        $weekYear = $carbonDate->format('o-W');
                        $startOfWeek = $carbonDate->copy()->startOfWeek()->translatedFormat('d M');
                        $endOfWeek = $carbonDate->copy()->endOfWeek()->translatedFormat('d M Y');
                        $label = "Mgg " . $carbonDate->format('W') . " (" . $startOfWeek . " - " . $endOfWeek . ")";
                        
                        if (!isset($grouped[$weekYear])) {
                            $grouped[$weekYear] = [
                                'label' => $label,
                                'pendapatan' => 0
                            ];
                        }
                        $grouped[$weekYear]['pendapatan'] += $day['prediksi_rp'];
                    }
                    ksort($grouped);
                    $formattedDetails = array_values($grouped);
                } elseif ($type === 'bulanan') {
                    $grouped = [];
                    foreach ($detailHarian as $day) {
                        $carbonDate = Carbon::parse($day['tanggal']);
                        $monthYear = $carbonDate->format('Y-m');
                        $label = $carbonDate->translatedFormat('F Y');
                        
                        if (!isset($grouped[$monthYear])) {
                            $grouped[$monthYear] = [
                                'label' => $label,
                                'pendapatan' => 0
                            ];
                        }
                        $grouped[$monthYear]['pendapatan'] += $day['prediksi_rp'];
                    }
                    ksort($grouped);
                    $formattedDetails = array_values($grouped);
                } elseif ($type === 'tahunan') {
                    $grouped = [];
                    foreach ($detailHarian as $day) {
                        $carbonDate = Carbon::parse($day['tanggal']);
                        $year = $carbonDate->format('Y');
                        $label = "Tahun " . $year;
                        
                        if (!isset($grouped[$year])) {
                            $grouped[$year] = [
                                'label' => $label,
                                'pendapatan' => 0
                            ];
                        }
                        $grouped[$year]['pendapatan'] += $day['prediksi_rp'];
                    }
                    ksort($grouped);
                    $formattedDetails = array_values($grouped);
                }
                
                $totalPredVal = array_sum(array_column($formattedDetails, 'pendapatan'));
                $avgPred = count($formattedDetails) > 0 ? $totalPredVal / count($formattedDetails) : 0;
                
                $recommendations = [];
                $avgDaily = $totalPredVal / $daysToPredict;
                
                $hasWeekendPeak = false;
                foreach ($detailHarian as $day) {
                    $dayOfWeek = (int) date('N', strtotime($day['tanggal']));
                    if (gmdate('N', strtotime($day['tanggal'])) >= 6 && $day['prediksi_rp'] > $avgDaily * 1.1) {
                        $hasWeekendPeak = true;
                    }
                }
                
                if ($hasWeekendPeak) {
                    $recommendations[] = "Pola kenaikan pendapatan terdeteksi pada akhir pekan. Tempatkan petugas pengawas ekstra di titik-titik keramaian.";
                } else {
                    $recommendations[] = "Pola pendapatan cenderung merata. Pastikan kepatuhan jukir menyetor retribusi.";
                }
                
                if ($rayonId > 0) {
                    $recommendations[] = "Fokus pemantauan diarahkan khusus pada titik-titik potensial di wilayah Rayon " . $rayonId . ".";
                } else {
                    $recommendations[] = "Lakukan pengawasan silang antar-rayon untuk memperkecil risiko kebocoran.";
                }
                
                $lastConfidenceNote = $detailHarian[count($detailHarian)-1]['confidence_note'] ?? '';
                
                return [
                    'title' => $title,
                    'detail_label' => $detailLabel,
                    'start_date' => Carbon::parse($futureStart)->translatedFormat('d F Y'),
                    'end_date' => Carbon::parse($futureEnd)->translatedFormat('d F Y'),
                    'total_predicted' => 'Rp ' . number_format($totalPredVal, 0, ',', '.'),
                    'avg_predicted' => 'Rp ' . number_format($avgPred, 0, ',', '.') . $unitLabel,
                    'detail_harian' => $formattedDetails,
                    'recommendations' => $recommendations,
                    'model_type' => $modelType,
                    'best_model_type' => $bestModelTypeParam,
                    'confidence_note' => $lastConfidenceNote
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to fetch future prediction: " . $e->getMessage());
        }
        return null;
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        $rayonName = 'Semua Rayon';
        if ($data['rayonId'] > 0) {
            $rayon = Rayon::find($data['rayonId']);
            if ($rayon) {
                $rayonName = $rayon->nama_rayon;
            }
        }

        $totalErrorVal = $data['totalActual'] - $data['totalPredicted'];
        $total_period = [
            'aktual' => 'Rp ' . number_format($data['totalActual'], 0, ',', '.'),
            'prediksi' => 'Rp ' . number_format($data['totalPredicted'], 0, ',', '.'),
            'error' => 'Rp ' . number_format(abs($totalErrorVal), 0, ',', '.'),
            'pct_error' => number_format($data['avgPctError'], 2, ',', '.') . '%'
        ];

        // Fetch metrics from ModelMetric
        $mae = '-';
        $rmse = '-';
        $mape = '-';
        $r2 = '-';

        if ($data['latestRun']) {
            $metric = $data['latestRun']->modelMetrics()->where('dataset_type', 'test')->first();
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

        $pdf = Pdf::loadView('operator.laporan.pdf', [
            'summary' => $data['summary'],
            'reports' => $data['reports'],
            'total_period' => $total_period,
            'metrics' => $metrics,
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'rayonName' => $rayonName,
            'type' => $data['type']
        ]);

        return $pdf->download('laporan-prediksi-' . $data['type'] . '.pdf');
    }
}
