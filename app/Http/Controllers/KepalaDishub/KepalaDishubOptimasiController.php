<?php

namespace App\Http\Controllers\KepalaDishub;

use App\Http\Controllers\Controller;
use App\Models\ModelRun;

class KepalaDishubOptimasiController extends Controller
{
    private function getLatestRun(string $modelType): ?ModelRun
    {
        return ModelRun::where('model_type', $modelType)
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();
    }

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

    public function index()
    {
        // 1. Cek SVR Standar
        $lastRun = $this->getLatestRun('svr_default');

        // 2. Nilai-nilai skripsi fallback (sekarang null/empty)
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

        // 4. Cari model Grid Search & GWO (menggunakan best run sesuai Operator)
        $gsRun  = $this->getBestRun('svr_grid_search');
        $gwoRun = $this->getBestRun('svr_gwo');

        // Helper row komparasi
        $buildRow = function (string $metode, ?ModelRun $run, array $fallback) {
            if ($run) {
                $param  = $run->modelParameter;
                $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                $mapeR  = $metric ? (float)$metric->mape     : null;
                $r2R    = $metric ? (float)$metric->r2_score : null;
                $cVal   = $param  ? (is_numeric($param->c_value)
                    ? number_format((float)$param->c_value, 4, ',', '.')
                    : $param->c_value) : '-';
                $epsVal = $param  ? (is_numeric($param->epsilon_value)
                    ? number_format((float)$param->epsilon_value, 6, ',', '.')
                    : $param->epsilon_value) : '-';
                $gamVal = $param  ? (is_numeric($param->gamma_value)
                    ? number_format((float)$param->gamma_value, 5, ',', '.')
                    : $param->gamma_value) : '-';
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
                'c'        => $lastRun ? (is_numeric($lastRun->modelParameter?->c_value)
                    ? number_format((float)$lastRun->modelParameter->c_value, 4, ',', '.')
                    : ($lastRun->modelParameter?->c_value ?? '1,0000')) : '-',
                'epsilon'  => $lastRun ? (is_numeric($lastRun->modelParameter?->epsilon_value)
                    ? number_format((float)$lastRun->modelParameter->epsilon_value, 4, ',', '.')
                    : ($lastRun->modelParameter?->epsilon_value ?? '0,1000')) : '-',
                'gamma'    => $lastRun ? ($lastRun->modelParameter?->gamma_value ?? 'scale') : '-',
                'mae'      => $lastRun && $lastRun->modelMetrics()->where('dataset_type','test')->first()?->mae !== null
                    ? 'Rp ' . number_format((float)$lastRun->modelMetrics()->where('dataset_type','test')->first()->mae, 0, ',', '.')
                    : '-',
                'rmse'     => $lastRun && $lastRun->modelMetrics()->where('dataset_type','test')->first()?->rmse !== null
                    ? 'Rp ' . number_format((float)$lastRun->modelMetrics()->where('dataset_type','test')->first()->rmse, 0, ',', '.')
                    : '-',
                'mape'     => $mapeVal !== null ? number_format($mapeVal, 2, ',', '.') . '% (' . $this->mapeCategory($mapeVal) . ')' : '-',
                'akurasi'  => $mapeVal !== null ? number_format(max(0, 100 - $mapeVal), 2, ',', '.') . '%' : '-',
                'r2'       => $r2Val !== null ? number_format($r2Val, 2, ',', '.') . ' (' . $this->r2Category($r2Val) . ')' : '-',
            ],
            $buildRow('SVR + Grid Search', $gsRun, $SKRIPSI_GS),
            $buildRow('SVR + GWO (Grey Wolf)', $gwoRun, $SKRIPSI_GWO),
        ];

        // Kartu Ringkasan
        $gsParam  = $gsRun ? $gsRun->modelParameter : null;
        $gsMetric = $gsRun ? $gsRun->modelMetrics()->where('dataset_type', 'test')->first() : null;
        $grid_best = [
            'c' => $gsParam ? (is_numeric($gsParam->c_value) ? number_format((float)$gsParam->c_value, 4, ',', '.') : $gsParam->c_value) : '-',
            'epsilon' => $gsParam ? (is_numeric($gsParam->epsilon_value) ? number_format((float)$gsParam->epsilon_value, 6, ',', '.') : $gsParam->epsilon_value) : '-',
            'gamma' => $gsParam ? (is_numeric($gsParam->gamma_value) ? number_format((float)$gsParam->gamma_value, 5, ',', '.') : $gsParam->gamma_value) : '-',
            'accuracy' => $gsMetric ? (number_format(max(0, 100 - $gsMetric->mape), 2, ',', '.') . '% (R² = ' . number_format($gsMetric->r2_score, 2, ',', '.') . ')') : '-'
        ];

        $gwoParam  = $gwoRun ? $gwoRun->modelParameter : null;
        $gwoMetric = $gwoRun ? $gwoRun->modelMetrics()->where('dataset_type', 'test')->first() : null;
        $gwo_best = [
            'c' => $gwoParam ? (is_numeric($gwoParam->c_value) ? number_format((float)$gwoParam->c_value, 4, ',', '.') : $gwoParam->c_value) : '-',
            'epsilon' => $gwoParam ? (is_numeric($gwoParam->epsilon_value) ? number_format((float)$gwoParam->epsilon_value, 6, ',', '.') : $gwoParam->epsilon_value) : '-',
            'gamma' => $gwoParam ? (is_numeric($gwoParam->gamma_value) ? number_format((float)$gwoParam->gamma_value, 5, ',', '.') : $gwoParam->gamma_value) : '-',
            'accuracy' => $gwoMetric ? (number_format(max(0, 100 - $gwoMetric->mape), 2, ',', '.') . '% (R² = ' . number_format($gwoMetric->r2_score, 2, ',', '.') . ')') : '-'
        ];

        // Nilai numerik untuk Chart.js
        $chartMetrics = [
            'mape_default' => $mapeVal,
            'r2_default'   => $r2Val,
            'mape_gs'      => $gsMetric  ? (float)$gsMetric->mape      : $SKRIPSI_GS['mape_raw'],
            'r2_gs'        => $gsMetric  ? (float)$gsMetric->r2_score  : $SKRIPSI_GS['r2_raw'],
            'mape_gwo'     => $gwoMetric ? (float)$gwoMetric->mape     : $SKRIPSI_GWO['mape_raw'],
            'r2_gwo'       => $gwoMetric ? (float)$gwoMetric->r2_score : $SKRIPSI_GWO['r2_raw'],
        ];

        return view('kepala-dishub.optimasi.index', compact('grid_best', 'gwo_best', 'comparisons', 'chartMetrics', 'lastRun', 'gsRun', 'gwoRun'));
    }
}

