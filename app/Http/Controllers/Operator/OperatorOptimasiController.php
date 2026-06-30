<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;
use App\Models\ModelParameter;
use App\Models\ModelMetric;
use App\Models\PredictionResult;
use App\Models\Pendapatan;
use App\Models\HariLibur;
use App\Services\FastApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperatorOptimasiController extends Controller
{
    /**
     * Ambil model run terbaru berdasarkan model_type.
     * Fallback ke null jika belum ada.
     */
    private function getLatestRun(string $modelType): ?ModelRun
    {
        return ModelRun::where('model_type', $modelType)
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Ambil model run terbaik berdasarkan MAPE terkecil di dataset test.
     */
    private function getBestRun(string $modelType): ?ModelRun
    {
        return ModelRun::where('model_runs.model_type', $modelType)
            ->where('model_runs.status', 'success')
            ->join('model_metrics', 'model_runs.id', '=', 'model_metrics.model_run_id')
            ->where('model_metrics.dataset_type', 'test')
            ->orderBy('model_metrics.mape', 'asc')
            ->select('model_runs.*')
            ->first();
    }

    /**
     * Ambil data transaksi pendapatan untuk training SVR
     */
    private function getDataset(): array
    {
        $pendapatanData = Pendapatan::with(['rayon', 'juruParkir'])->orderBy('tanggal', 'asc')->get();
        $periodeAwal = Pendapatan::min('tanggal');
        $periodeAkhir = Pendapatan::max('tanggal');
        $minYear = $periodeAwal ? Carbon::parse($periodeAwal)->year : 2023;
        $maxYear = $periodeAkhir ? Carbon::parse($periodeAkhir)->year : 2025;
        $startDate = Carbon::create($minYear, 1, 1)->format('Y-m-d');
        $endDate = Carbon::create($maxYear, 12, 31)->format('Y-m-d');

        $liburNasionalDates = HariLibur::where('tipe', 'Libur Nasional')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();
            
        $weekendDates = HariLibur::where('tipe', 'Weekend')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d'))
            ->toArray();
        
        $dataset = [];
        foreach ($pendapatanData as $p) {
            $tgl = Carbon::parse($p->tanggal)->format('Y-m-d');
            $isLibur = in_array($tgl, $liburNasionalDates) ? 1 : 0;
            
            $dayOfWeek = (int) date('N', strtotime($tgl));
            $isWeekend = (in_array($tgl, $weekendDates) || $dayOfWeek >= 6) ? 1 : 0;
            
            $jukirCount = $p->juruParkir->jumlah_juru_parkir ?? ($p->rayon->jumlah_juru_parkir ?? 80);
            
            $dataset[] = [
                'Tanggal' => $tgl,
                'Rayon' => (int) $p->rayon_id,
                'Weekend' => $isWeekend,
                'Jumlah Jukir' => (int) $jukirCount,
                'Total_Pendapatan' => (double) $p->jumlah,
                'Libur_Nasional' => $isLibur
            ];
        }
        return $dataset;
    }

    public function index(Request $request)
    {
        // 1. Cek SVR Standar sebagai prasyarat
        $lastRun = $this->getLatestRun('svr_default');

        // Parameter formatter helper to trim trailing zeros of decimals and support precise representation
        $formatParam = function ($val, int $maxDecimals = 8): string {
            if ($val === null || $val === '') {
                return '-';
            }
            if (!is_numeric($val)) {
                return $val;
            }
            $formatted = number_format((float)$val, $maxDecimals, ',', '.');
            if (strpos($formatted, ',') !== false) {
                $formatted = rtrim($formatted, '0');
                $formatted = rtrim($formatted, ',');
            }
            return $formatted;
        };

        // 2. Nilai-nilai dari skripsi sebagai fallback konstan
        $SKRIPSI_GS = [
            'c' => '-',   'epsilon' => '-', 'gamma' => '-',
            'mae' => '-', 'rmse' => '-',
            'mape_raw' => null, 'r2_raw' => null,
        ];
        $SKRIPSI_GWO = [
            'c' => '-', 'epsilon' => '-', 'gamma' => '-',
            'mae' => '-', 'rmse' => '-',
            'mape_raw' => null, 'r2_raw' => null,
        ];

        // 3. Bangun data SVR Standar
        $mapeVal = null;
        $r2Val   = null;
        if ($lastRun) {
            $metric = $lastRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($metric) {
                $mapeVal = (float) $metric->mape;
                $r2Val   = (float) $metric->r2_score;
            }
        }

        $mapeCategory = $mapeVal !== null ? $this->mapeCategory($mapeVal) : '-';
        $r2Category   = $r2Val !== null ? $this->r2Category($r2Val) : '-';

        // 4. Cari model Grid Search & GWO di DB (jika ada, gunakan nilainya)
        $gsRun  = $this->getBestRun('svr_grid_search');
        $gwoRun = $this->getBestRun('svr_gwo');

        // Helper builder row komparasi
        $buildRow = function (string $metode, ?ModelRun $run, array $fallback) use ($formatParam) {
            if ($run) {
                $param  = $run->modelParameter;
                $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                $mapeR  = $metric ? (float)$metric->mape     : null;
                $r2R    = $metric ? (float)$metric->r2_score : null;
                $cVal   = $param  ? $formatParam($param->c_value, 6) : '-';
                $epsVal = $param  ? $formatParam($param->epsilon_value, 8) : '-';
                $gamVal = $param  ? $formatParam($param->gamma_value, 6) : '-';
                $maeStr  = $metric ? 'Rp ' . number_format((float)$metric->mae,  0, ',', '.') : '-';
                $rmseStr = $metric ? 'Rp ' . number_format((float)$metric->rmse, 0, ',', '.') : '-';

                return [
                    'metode'   => $metode,
                    'c'        => $cVal,
                    'epsilon'  => $epsVal,
                    'gamma'    => $gamVal,
                    'mae'      => $maeStr,
                    'rmse'     => $rmseStr,
                    'mape'     => $mapeR !== null ? number_format($mapeR, 2, ',', '.') . '% (' . $this->mapeCategory($mapeR) . ')' : '-',
                    'akurasi'  => $mapeR !== null ? number_format(max(0, 100 - $mapeR), 2, ',', '.') . '%' : '-',
                    'r2'       => $r2R !== null ? number_format($r2R, 2, ',', '.') . ' (' . $this->r2Category($r2R) . ')' : '-',
                ];
            } else {
                return [
                    'metode'   => $metode,
                    'c'        => '-',
                    'epsilon'  => '-',
                    'gamma'    => '-',
                    'mae'      => '-',
                    'rmse'     => '-',
                    'mape'     => '-',
                    'akurasi'  => '-',
                    'r2'       => '-',
                ];
            }
        };

        $comparisons = [
            [
                'metode'   => 'SVR Standar (Default)',
                'c'        => $lastRun ? $formatParam($lastRun->modelParameter?->c_value ?? 1.0, 6) : '-',
                'epsilon'  => $lastRun ? $formatParam($lastRun->modelParameter?->epsilon_value ?? 0.1, 8) : '-',
                'gamma'    => $lastRun ? $formatParam($lastRun->modelParameter?->gamma_value ?? 'scale', 6) : '-',
                'mae'      => $lastRun && $lastRun->modelMetrics()->where('dataset_type','test')->first()?->mae !== null
                    ? 'Rp ' . number_format((float)$lastRun->modelMetrics()->where('dataset_type','test')->first()->mae, 0, ',', '.')
                    : '-',
                'rmse'     => $lastRun && $lastRun->modelMetrics()->where('dataset_type','test')->first()?->rmse !== null
                    ? 'Rp ' . number_format((float)$lastRun->modelMetrics()->where('dataset_type','test')->first()->rmse, 0, ',', '.')
                    : '-',
                'mape'     => $mapeVal !== null ? number_format($mapeVal, 2, ',', '.') . '% (' . $mapeCategory . ')' : '-',
                'akurasi'  => $mapeVal !== null ? number_format(max(0, 100 - $mapeVal), 2, ',', '.') . '%' : '-',
                'r2'       => $r2Val !== null ? number_format($r2Val, 2, ',', '.') . ' (' . $r2Category . ')' : '-',
            ],
            $buildRow('SVR + Grid Search', $gsRun, $SKRIPSI_GS),
            $buildRow('SVR + GWO (Grey Wolf)', $gwoRun, $SKRIPSI_GWO),
        ];

        // Nilai numerik untuk Chart.js (dikirim langsung ke view, bukan di-parse dari string)
        $gsMetric  = $gsRun  ? $gsRun->modelMetrics()->where('dataset_type', 'test')->first()  : null;
        $gwoMetric = $gwoRun ? $gwoRun->modelMetrics()->where('dataset_type', 'test')->first() : null;

        $chartMetrics = [
            'mape_default' => $mapeVal,
            'r2_default'   => $r2Val,
            'mape_gs'      => $gsMetric  ? (float)$gsMetric->mape      : $SKRIPSI_GS['mape_raw'],
            'r2_gs'        => $gsMetric  ? (float)$gsMetric->r2_score  : $SKRIPSI_GS['r2_raw'],
            'mape_gwo'     => $gwoMetric ? (float)$gwoMetric->mape     : $SKRIPSI_GWO['mape_raw'],
            'r2_gwo'       => $gwoMetric ? (float)$gwoMetric->r2_score : $SKRIPSI_GWO['r2_raw'],
        ];

        // Ambil riwayat komplit untuk tabel riwayat
        $historyRuns = ModelRun::whereIn('model_type', ['svr_grid_search', 'svr_gwo'])
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->get();

        $bestGsId  = $gsRun ? $gsRun->id : null;
        $bestGwoId = $gwoRun ? $gwoRun->id : null;

        // --- DATA EVALUASI UNTUK MASING-MASING MODEL OPTIMASI ---
        $rayons = \App\Models\Rayon::all();
        $rayonId = $request->input('rayon_id', 0);

        // 1. Grid Search Evaluation Data
        $gsChartData = collect([]);
        $gsPredictions = collect([]);
        $gsMetricsObj = null;
        if ($gsRun) {
            $gsMetricsObj = $gsRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($rayonId > 0) {
                $gsChartData = $gsRun->predictionResults()
                    ->where('rayon_id', $rayonId)
                    ->orderBy('tanggal', 'asc')
                    ->get();
            } else {
                $gsChartData = $gsRun->predictionResults()
                    ->select('tanggal', DB::raw('SUM(actual_value) as actual_value'), DB::raw('SUM(predicted_value) as predicted_value'))
                    ->groupBy('tanggal')
                    ->orderBy('tanggal', 'asc')
                    ->get();
            }
            $gsPredictionsQuery = $gsRun->predictionResults()->orderBy('tanggal', 'desc');
            if ($rayonId > 0) {
                $gsPredictionsQuery->where('rayon_id', $rayonId);
            }
            $gsPredictions = $gsPredictionsQuery->paginate(10, ['*'], 'page_gs')->withQueryString();
        }

        // 2. GWO Evaluation Data
        $gwoChartData = collect([]);
        $gwoPredictions = collect([]);
        $gwoMetricsObj = null;
        if ($gwoRun) {
            $gwoMetricsObj = $gwoRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($rayonId > 0) {
                $gwoChartData = $gwoRun->predictionResults()
                    ->where('rayon_id', $rayonId)
                    ->orderBy('tanggal', 'asc')
                    ->get();
            } else {
                $gwoChartData = $gwoRun->predictionResults()
                    ->select('tanggal', DB::raw('SUM(actual_value) as actual_value'), DB::raw('SUM(predicted_value) as predicted_value'))
                    ->groupBy('tanggal')
                    ->orderBy('tanggal', 'asc')
                    ->get();
            }
            $gwoPredictionsQuery = $gwoRun->predictionResults()->orderBy('tanggal', 'desc');
            if ($rayonId > 0) {
                $gwoPredictionsQuery->where('rayon_id', $rayonId);
            }
            $gwoPredictions = $gwoPredictionsQuery->paginate(10, ['*'], 'page_gwo')->withQueryString();
        }

        return view('operator.optimasi.index', compact(
            'comparisons', 
            'lastRun', 
            'chartMetrics', 
            'historyRuns', 
            'bestGsId', 
            'bestGwoId',
            'gsRun',
            'gwoRun',
            'rayons',
            'rayonId',
            'gsChartData',
            'gsPredictions',
            'gsMetricsObj',
            'gwoChartData',
            'gwoPredictions',
            'gwoMetricsObj'
        ));
    }

    // ── Grid Search ──────────────────────────────────────────────────────────

    public function runGridSearch(Request $request)
    {
        set_time_limit(600);
        $lastRun = ModelRun::where('model_type', 'svr_default')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastRun) {
            $msg = 'Optimasi Grid Search tidak dapat dijalankan karena model SVR Standar belum dilatih.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $gridC       = $this->parseArrayInput($request->input('grid_c'), [10, 50, 100, 150, 200]);
        $gridEpsilon = $this->parseArrayInput($request->input('grid_epsilon'), [0.001, 0.005, 0.01, 0.05]);
        $gridGamma   = $this->parseArrayInput($request->input('grid_gamma'), ['scale', 0.001, 0.01, 0.05]);

        // Load dataset from DB
        $dataset = $this->getDataset();

        // Send to FastAPI
        try {
            $fastApiService = app(FastApiService::class);
            $response = $fastApiService->post('train/grid-search', [
                'dataset'      => $dataset,
                'grid_c'       => $gridC,
                'grid_epsilon' => $gridEpsilon,
                'grid_gamma'   => $gridGamma
            ]);

            if ($response === null || !isset($response['status']) || $response['status'] !== 'success') {
                $err = 'Gagal berkomunikasi dengan Python API atau terjadi kesalahan saat training.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $err], 500);
                }
                return redirect()->back()->with('error', $err);
            }

            $BEST_C     = (float)$response['parameters']['C'];
            $BEST_EPS   = (float)$response['parameters']['epsilon'];
            $BEST_GAMMA = $response['parameters']['gamma'];
            $NEW_MAE    = (float)$response['metrics']['mae'];
            $NEW_RMSE   = (float)$response['metrics']['rmse'];
            $NEW_MAPE   = (float)$response['metrics']['mape'];
            $NEW_R2     = (float)$response['metrics']['r2'];

            // Cari model Grid Search terbaik sebelumnya di DB
            $bestGsPrev = $this->getBestRun('svr_grid_search');
            if ($bestGsPrev) {
                $oldMetric = $bestGsPrev->modelMetrics()->where('dataset_type', 'test')->first();
                $oldParam  = $bestGsPrev->modelParameter;
                $oldMape   = $oldMetric ? (float)$oldMetric->mape : 9999.0;
                $oldR2     = $oldMetric ? (float)$oldMetric->r2_score : 0.0;
                $oldC      = $oldParam ? $oldParam->c_value : '1.0';
                $oldEps    = $oldParam ? $oldParam->epsilon_value : '0.1';
                $oldGamma  = $oldParam ? $oldParam->gamma_value : 'scale';
            } else {
                $oldMetric = $lastRun->modelMetrics()->where('dataset_type', 'test')->first();
                $oldParam  = $lastRun->modelParameter;
                $oldMape   = $oldMetric ? (float)$oldMetric->mape : 9999.0;
                $oldR2     = $oldMetric ? (float)$oldMetric->r2_score : 0.0;
                $oldC      = $oldParam ? $oldParam->c_value : '1.0';
                $oldEps    = $oldParam ? $oldParam->epsilon_value : '0.1';
                $oldGamma  = $oldParam ? $oldParam->gamma_value : 'scale';
            }

            $isBetter = $NEW_MAPE < $oldMape;

            // Selalu simpan ke DB agar riwayat tersimpan komplit
            DB::beginTransaction();
            try {
                $newRun = ModelRun::create([
                    'model_name'   => 'SVR + Grid Search',
                    'model_type'   => 'svr_grid_search',
                    'status'       => 'success',
                    'started_at'   => now(),
                    'finished_at'  => now(),
                    'total_rows'   => $response['dataset']['total_rows'],
                    'train_rows'   => $response['dataset']['train_rows'],
                    'test_rows'    => $response['dataset']['test_rows'],
                    'train_period' => $response['dataset']['train_period'],
                    'test_period'  => $response['dataset']['test_period'],
                    'created_by'   => auth()->user()->username ?? 'operator',
                ]);

                ModelParameter::create([
                    'model_run_id'  => $newRun->id,
                    'kernel'        => 'rbf',
                    'c_value'       => (string)$BEST_C,
                    'epsilon_value' => (string)$BEST_EPS,
                    'gamma_value'   => (string)$BEST_GAMMA,
                ]);

                ModelMetric::create([
                    'model_run_id' => $newRun->id,
                    'mae'          => $NEW_MAE,
                    'rmse'         => $NEW_RMSE,
                    'mape'         => $NEW_MAPE,
                    'r2_score'     => $NEW_R2,
                    'accuracy'     => max(0, 100 - $NEW_MAPE),
                    'dataset_type' => 'test',
                ]);

                // Simpan Hasil Prediksi
                $predictionsData = [];
                foreach ($response['predictions'] as $pred) {
                    $predictionsData[] = [
                        'model_run_id'     => $newRun->id,
                        'tanggal'          => $pred['tanggal'],
                        'rayon_id'         => $pred['rayon_id'],
                        'rayon_name'       => $pred['rayon'],
                        'actual_value'     => $pred['actual'],
                        'predicted_value'  => $pred['predicted'],
                        'error_value'      => $pred['error'],
                        'percentage_error' => $pred['percentage_error'],
                        'created_at'       => now(),
                        'updated_at'       => now()
                    ];
                }

                foreach (array_chunk($predictionsData, 500) as $chunk) {
                    PredictionResult::insert($chunk);
                }

                DB::commit();
            } catch (\Exception $dbEx) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $dbEx->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Gagal menyimpan: ' . $dbEx->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'is_better'  => $isBetter,
                    'old_params' => ['c' => $oldC, 'epsilon' => $oldEps, 'gamma' => $oldGamma, 'mape' => $oldMape, 'r2' => $oldR2],
                    'new_params' => ['c' => $BEST_C, 'epsilon' => $BEST_EPS, 'gamma' => $BEST_GAMMA, 'mape' => $NEW_MAPE, 'r2' => $NEW_R2],
                    'message'    => $isBetter
                        ? 'Optimasi Grid Search selesai. Parameter baru berhasil disimpan sebagai model aktif.'
                        : 'Optimasi Grid Search selesai. Parameter baru tidak lebih baik; model tetap menggunakan parameter sebelumnya.',
                ]);
            }

            $sessionKey = $isBetter ? 'success' : 'info';
            return redirect()->back()->with($sessionKey,
                $isBetter
                    ? 'Optimasi Grid Search selesai. Model diperbarui dengan parameter baru (MAPE ' . $NEW_MAPE . '%).'
                    : 'Optimasi Grid Search selesai. Parameter tidak lebih baik; model tetap.'
            );

        } catch (\Exception $apiEx) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan koneksi ke Python API: ' . $apiEx->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Koneksi gagal: ' . $apiEx->getMessage());
        }
    }

    // ── Grey Wolf Optimizer (GWO) ────────────────────────────────────────────

    public function runGwo(Request $request)
    {
        set_time_limit(600);
        $lastRun = ModelRun::where('model_type', 'svr_default')
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastRun) {
            $msg = 'Optimasi Grey Wolf Optimizer (GWO) tidak dapat dijalankan karena model SVR Standar belum dilatih.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $wolves      = (int)$request->input('wolves', 12);
        $iterations  = (int)$request->input('iterations', 20);
        $cMin        = (float)$request->input('c_min', 10.0);
        $cMax        = (float)$request->input('c_max', 300.0);
        $epsilonMin  = (float)$request->input('epsilon_min', 0.0001);
        $epsilonMax  = (float)$request->input('epsilon_max', 0.05);
        $gammaMin    = (float)$request->input('gamma_min', 0.0005);
        $gammaMax    = (float)$request->input('gamma_max', 0.1);

        // Cari model GWO terbaik sebelumnya di DB untuk warm start
        $bestGwoPrev = $this->getBestRun('svr_gwo');
        $bestC = null;
        $bestEpsilon = null;
        $bestGamma = null;
        if ($bestGwoPrev) {
            $bestParam = $bestGwoPrev->modelParameter;
            if ($bestParam) {
                $bestC = $bestParam->c_value;
                $bestEpsilon = $bestParam->epsilon_value;
                $bestGamma = $bestParam->gamma_value;
            }
        }

        // Load dataset from DB
        $dataset = $this->getDataset();

        // Send to FastAPI
        try {
            $fastApiService = app(FastApiService::class);
            $response = $fastApiService->post('train/gwo', [
                'dataset'      => $dataset,
                'wolves'       => $wolves,
                'iterations'   => $iterations,
                'c_min'        => $cMin,
                'c_max'        => $cMax,
                'epsilon_min'  => $epsilonMin,
                'epsilon_max'  => $epsilonMax,
                'gamma_min'    => $gammaMin,
                'gamma_max'    => $gammaMax,
                'best_c'       => $bestC,
                'best_epsilon' => $bestEpsilon,
                'best_gamma'   => $bestGamma
            ]);

            if ($response === null || !isset($response['status']) || $response['status'] !== 'success') {
                $err = 'Gagal berkomunikasi dengan Python API atau terjadi kesalahan saat training GWO.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $err], 500);
                }
                return redirect()->back()->with('error', $err);
            }

            $BEST_C     = (float)$response['parameters']['C'];
            $BEST_EPS   = (float)$response['parameters']['epsilon'];
            $BEST_GAMMA = (float)$response['parameters']['gamma'];
            $NEW_MAE    = (float)$response['metrics']['mae'];
            $NEW_RMSE   = (float)$response['metrics']['rmse'];
            $NEW_MAPE   = (float)$response['metrics']['mape'];
            $NEW_R2     = (float)$response['metrics']['r2'];

            // Cari model GWO terbaik sebelumnya di DB
            $bestGwoPrev = $this->getBestRun('svr_gwo');
            if ($bestGwoPrev) {
                $oldMetric = $bestGwoPrev->modelMetrics()->where('dataset_type', 'test')->first();
                $oldParam  = $bestGwoPrev->modelParameter;
                $oldMape   = $oldMetric ? (float)$oldMetric->mape : 9999.0;
                $oldR2     = $oldMetric ? (float)$oldMetric->r2_score : 0.0;
                $oldC      = $oldParam ? $oldParam->c_value : '1.0';
                $oldEps    = $oldParam ? $oldParam->epsilon_value : '0.1';
                $oldGamma  = $oldParam ? $oldParam->gamma_value : 'scale';
            } else {
                $oldMetric = $lastRun->modelMetrics()->where('dataset_type', 'test')->first();
                $oldParam  = $lastRun->modelParameter;
                $oldMape   = $oldMetric ? (float)$oldMetric->mape : 9999.0;
                $oldR2     = $oldMetric ? (float)$oldMetric->r2_score : 0.0;
                $oldC      = $oldParam ? $oldParam->c_value : '1.0';
                $oldEps    = $oldParam ? $oldParam->epsilon_value : '0.1';
                $oldGamma  = $oldParam ? $oldParam->gamma_value : 'scale';
            }

            $isBetter = $NEW_MAPE < $oldMape;

            // Selalu simpan ke DB agar riwayat tersimpan komplit
            DB::beginTransaction();
            try {
                $newRun = ModelRun::create([
                    'model_name'   => 'SVR + GWO (Grey Wolf)',
                    'model_type'   => 'svr_gwo',
                    'status'       => 'success',
                    'started_at'   => now(),
                    'finished_at'  => now(),
                    'total_rows'   => $response['dataset']['total_rows'],
                    'train_rows'   => $response['dataset']['train_rows'],
                    'test_rows'    => $response['dataset']['test_rows'],
                    'train_period' => $response['dataset']['train_period'],
                    'test_period'  => $response['dataset']['test_period'],
                    'created_by'   => auth()->user()->username ?? 'operator',
                ]);

                ModelParameter::create([
                    'model_run_id'  => $newRun->id,
                    'kernel'        => 'rbf',
                    'c_value'       => (string)$BEST_C,
                    'epsilon_value' => (string)$BEST_EPS,
                    'gamma_value'   => (string)$BEST_GAMMA,
                ]);

                ModelMetric::create([
                    'model_run_id' => $newRun->id,
                    'mae'          => $NEW_MAE,
                    'rmse'         => $NEW_RMSE,
                    'mape'         => $NEW_MAPE,
                    'r2_score'     => $NEW_R2,
                    'accuracy'     => max(0, 100 - $NEW_MAPE),
                    'dataset_type' => 'test',
                ]);

                // Simpan Hasil Prediksi
                $predictionsData = [];
                foreach ($response['predictions'] as $pred) {
                    $predictionsData[] = [
                        'model_run_id'     => $newRun->id,
                        'tanggal'          => $pred['tanggal'],
                        'rayon_id'         => $pred['rayon_id'],
                        'rayon_name'       => $pred['rayon'],
                        'actual_value'     => $pred['actual'],
                        'predicted_value'  => $pred['predicted'],
                        'error_value'      => $pred['error'],
                        'percentage_error' => $pred['percentage_error'],
                        'created_at'       => now(),
                        'updated_at'       => now()
                    ];
                }

                foreach (array_chunk($predictionsData, 500) as $chunk) {
                    PredictionResult::insert($chunk);
                }

                DB::commit();
            } catch (\Exception $dbEx) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $dbEx->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Gagal menyimpan: ' . $dbEx->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'is_better'  => $isBetter,
                    'old_params' => ['c' => $oldC, 'epsilon' => $oldEps, 'gamma' => $oldGamma, 'mape' => $oldMape, 'r2' => $oldR2],
                    'new_params' => ['c' => $BEST_C, 'epsilon' => $BEST_EPS, 'gamma' => $BEST_GAMMA, 'mape' => $NEW_MAPE, 'r2' => $NEW_R2],
                    'message'    => $isBetter
                        ? 'Optimasi GWO selesai. Parameter global optimal baru berhasil disimpan.'
                        : 'Optimasi GWO selesai. Parameter baru tidak lebih baik; model tetap menggunakan parameter sebelumnya.',
                ]);
            }

            $sessionKey = $isBetter ? 'success' : 'info';
            return redirect()->back()->with($sessionKey,
                $isBetter
                    ? 'Optimasi GWO selesai. Model diperbarui dengan parameter baru (MAPE ' . $NEW_MAPE . '%).'
                    : 'Optimasi GWO selesai. Parameter tidak lebih baik; model tetap.'
            );

        } catch (\Exception $apiEx) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan koneksi ke Python API: ' . $apiEx->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Koneksi gagal: ' . $apiEx->getMessage());
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function parseArrayInput($input, $default = [])
    {
        if (empty($input)) {
            return $default;
        }
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $clean = trim($input, "[]{} \t\n\r\0\x0B");
        if ($clean === '') {
            return $default;
        }
        $parts = explode(',', $clean);
        return array_map(function($val) {
            $val = trim($val, " '\"");
            return is_numeric($val) ? (float)$val : $val;
        }, $parts);
    }

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
}
