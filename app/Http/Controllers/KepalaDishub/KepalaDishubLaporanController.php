<?php

namespace App\Http\Controllers\KepalaDishub;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;
use App\Models\Rayon;
use App\Models\Pendapatan;
use App\Models\PredictionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KepalaDishubLaporanController extends Controller
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
                    'tanggal_raw' => $pred->tanggal,
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

        $chartLabels = [];
        $chartActualValues = [];
        $chartPredictValues = [];
        $avgDailyActual = 0;
        $avgDailyPredicted = 0;
        $avgDailyDeviation = 0;

        if ($latestRun && count($reports) > 0) {
            $groupedByDate = $predictionResults->groupBy('tanggal')->sortKeys();
            foreach ($groupedByDate as $date => $group) {
                $chartLabels[] = date('d M Y', strtotime($date));
                $chartActualValues[] = (int) $group->sum('actual_value');
                $chartPredictValues[] = (int) $group->sum('predicted_value');
            }

            $avgDailyActual = $totalActual / count($chartActualValues);
            $avgDailyPredicted = $totalPredicted / count($chartPredictValues);

            $deviations = [];
            foreach ($chartActualValues as $i => $actVal) {
                $predVal = $chartPredictValues[$i] ?? 0;
                $deviations[] = abs($actVal - $predVal);
            }
            $avgDailyDeviation = array_sum($deviations) / count($deviations);
        }

        $statusAkurasi = 'Cukup Akurat (Perlu Pemantauan)';
        $keteranganAkurasi = 'Model peramalan memiliki tingkat kesalahan sebesar ' . number_format($avgPctError, 2, ',', '.') . '%. Direkomendasikan bagi Operator untuk melakukan pelatihan ulang model agar lebih presisi.';

        if ($avgPctError < 10) {
            $statusAkurasi = 'Sangat Akurat (Presisi Tinggi)';
            $keteranganAkurasi = 'Model peramalan sangat presisi dengan tingkat kesalahan harian hanya ' . number_format($avgPctError, 2, ',', '.') . '%. Sangat andal digunakan sebagai acuan penetapan target retribusi parkir harian.';
        } elseif ($avgPctError <= 20) {
            $statusAkurasi = 'Baik (Andal)';
            $keteranganAkurasi = 'Model peramalan memiliki tingkat kesalahan rendah sebesar ' . number_format($avgPctError, 2, ',', '.') . '%. Layak dijadikan panduan resmi target setoran jukir di lapangan.';
        }

        $analysis = [
            'avg_actual' => 'Rp ' . number_format($avgDailyActual, 0, ',', '.'),
            'avg_predict' => 'Rp ' . number_format($avgDailyPredicted, 0, ',', '.'),
            'avg_deviation' => 'Rp ' . number_format($avgDailyDeviation, 0, ',', '.'),
            'status_akurasi' => $statusAkurasi,
            'keterangan_akurasi' => $keteranganAkurasi
        ];

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

        return view('kepala-dishub.laporan.index', compact('summary', 'reports', 'total_period', 'rayons', 'startDate', 'endDate', 'rayonId', 'chartLabels', 'chartActualValues', 'chartPredictValues', 'analysis'));
    }

    public function exportPdf()
    {
        return response()->streamDownload(function () {
            echo "PDF Laporan Prediksi Retribusi Kepala Dishub (MOCK)";
        }, 'laporan-prediksi-dishub.pdf');
    }
}
