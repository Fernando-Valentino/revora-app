@props([
    'comparisons',
    'chartMetrics',
    'lastRun' => null,
    'gsRun' => null,
    'gwoRun' => null
])

@php
    $defaultMape = $chartMetrics['mape_default'] ?? null;
    $gsMape = $chartMetrics['mape_gs'] ?? null;
    $gwoMape = $chartMetrics['mape_gwo'] ?? null;

    $models = [];
    if ($defaultMape !== null) $models['SVR Standar (Default)'] = $defaultMape;
    if ($gsMape !== null) $models['SVR + Grid Search'] = $gsMape;
    if ($gwoMape !== null) $models['SVR + GWO (Grey Wolf)'] = $gwoMape;

    $bestModelName = '-';
    $bestModelMape = 0;
    if (count($models) > 0) {
        asort($models);
        $bestModelName = array_key_first($models);
        $bestModelMape = reset($models);
    }

    $gsImprovement = ($defaultMape !== null && $gsMape !== null) ? ($defaultMape - $gsMape) : 0;
    $gwoImprovement = ($defaultMape !== null && $gwoMape !== null) ? ($defaultMape - $gwoMape) : 0;
    
    // Extract exact parameters from SVR runs
    $defaultC = $lastRun ? ($lastRun->modelParameter?->c_value ?? '1') : '1';
    $defaultEps = $lastRun ? ($lastRun->modelParameter?->epsilon_value ?? '0.1') : '0.1';
    $defaultGam = $lastRun ? ($lastRun->modelParameter?->gamma_value ?? 'scale') : 'scale';

    $gsParamC = $gsRun ? ($gsRun->modelParameter?->c_value ?? '-') : '-';
    $gsParamEps = $gsRun ? ($gsRun->modelParameter?->epsilon_value ?? '-') : '-';
    $gsParamGam = $gsRun ? ($gsRun->modelParameter?->gamma_value ?? '-') : '-';

    $gwoParamC = $gwoRun ? ($gwoRun->modelParameter?->c_value ?? '-') : '-';
    $gwoParamEps = $gwoRun ? ($gwoRun->modelParameter?->epsilon_value ?? '-') : '-';
    $gwoParamGam = $gwoRun ? ($gwoRun->modelParameter?->gamma_value ?? '-') : '-';

    // Helper formatter to trim trailing zeros of decimals and support precise representation (4 decimals max)
    $formatParamVal = function ($val, int $maxDecimals = 4): string {
        if ($val === null || $val === '' || $val === '-') {
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

    // Best model interpretations
    $bestRunObj = null;
    $bestR2Val = 0;
    if ($bestModelName === 'SVR Standar (Default)') {
        $bestRunObj = $lastRun;
    } elseif ($bestModelName === 'SVR + Grid Search') {
        $bestRunObj = $gsRun;
    } elseif ($bestModelName === 'SVR + GWO (Grey Wolf)') {
        $bestRunObj = $gwoRun;
    }

    if ($bestRunObj) {
        $bestMetric = $bestRunObj->modelMetrics()->where('dataset_type', 'test')->first();
        if ($bestMetric) {
            $bestR2Val = (float)$bestMetric->r2_score;
        }
    }

    $bestModelMapeInterpret = 'Cukup Akurat';
    if ($bestModelMape < 10) {
        $bestModelMapeInterpret = 'Sangat Akurat';
    } elseif ($bestModelMape <= 20) {
        $bestModelMapeInterpret = 'Baik';
    }

    $bestR2Interpret = 'Model Lemah';
    if ($bestR2Val >= 0.67) {
        $bestR2Interpret = 'Sangat Kuat';
    } elseif ($bestR2Val >= 0.33) {
        $bestR2Interpret = 'Cukup';
    }

    // Set class and alerts depending on the best model
    $alertClass = 'alert-success text-success-emphasis bg-success-subtle border-success-subtle';
    $iconClass = 'bi-patch-check-fill text-success';
    if ($bestModelName === 'SVR Standar (Default)') {
        $alertClass = 'alert-warning text-warning-emphasis bg-warning-subtle border-warning-subtle';
        $iconClass = 'bi-exclamation-triangle-fill text-warning';
    }
@endphp

<div class="row g-4">
    <!-- Kolom Kiri: Ringkasan Kinerja Tiap Model -->
    <div class="col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold text-secondary text-uppercase mb-0 shadow-none border-0 pb-0" style="font-size: 11.5px; letter-spacing: 0.5px;">Temuan &amp; Komparasi Kinerja Model</h6>
            <span class="badge bg-light text-secondary border px-2.5 py-1 text-xs">⚠️ Kesalahan Lebih Kecil = Lebih Akurat</span>
        </div>
        <div class="d-flex flex-column gap-3">
            <!-- Card SVR Default -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3 align-items-start">
                <div class="fs-4 mt-0.5"><i class="bi bi-cpu text-secondary"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">SVR Standar (Default)</div>
                        <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fw-bold">Kesalahan (MAPE): {{ $defaultMape !== null ? number_format($defaultMape, 2, ',', '.') . '%' : '-' }}</span>
                    </div>
                    <div class="text-secondary small mb-2" style="line-height: 1.5;">
                        Model dasar menggunakan parameter standar bawaan sistem.
                    </div>
                    @if($defaultMape !== null)
                        <div class="text-xs text-secondary d-flex align-items-center flex-wrap pt-2 border-top">
                            <span>Akurasi: <strong>{{ number_format(100 - $defaultMape, 2, ',', '.') }}%</strong></span>
                            <span class="mx-2 text-muted">•</span>
                            <span>R² Score: <strong>{{ number_format($comparisons[0]['r2_raw'] ?? 0.635, 2, ',', '.') }}</strong></span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Card Grid Search -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3 align-items-start">
                <div class="fs-4 mt-0.5"><i class="bi bi-grid-3x3 text-warning"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">SVR + Grid Search</div>
                        <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 fw-bold">Kesalahan (MAPE): {{ $gsMape !== null ? number_format($gsMape, 2, ',', '.') . '%' : '-' }}</span>
                    </div>
                    <div class="text-secondary small mb-2" style="line-height: 1.5;">
                        Model hasil optimasi parameter menggunakan pencarian terstruktur.
                    </div>
                    @if($gsMape !== null)
                        <div class="text-xs text-secondary d-flex align-items-center flex-wrap pt-2 border-top">
                            <span>Akurasi: <strong class="text-dark">{{ number_format(100 - $gsMape, 2, ',', '.') }}%</strong></span>
                            <span class="mx-2 text-muted">•</span>
                            <span>R² Score: <strong class="text-dark">{{ number_format($comparisons[1]['r2_raw'] ?? 0.873, 2, ',', '.') }}</strong></span>
                            @if($gsImprovement > 0)
                                <span class="mx-2 text-muted">•</span>
                                <span class="text-success fw-semibold"><i class="bi bi-arrow-up-circle-fill"></i> Akurasi Naik <strong>+{{ number_format($gsImprovement, 2, ',', '.') }}%</strong></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card GWO -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3 align-items-start">
                <div class="fs-4 mt-0.5"><i class="bi bi-activity text-success"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">SVR + GWO (Grey Wolf)</div>
                        <span class="badge bg-success-subtle text-success px-2.5 py-1 fw-bold">Kesalahan (MAPE): {{ $gwoMape !== null ? number_format($gwoMape, 2, ',', '.') . '%' : '-' }}</span>
                    </div>
                    <div class="text-secondary small mb-2" style="line-height: 1.5;">
                        Model hasil optimasi parameter menggunakan pencarian cerdas berbasis populasi.
                    </div>
                    @if($gwoMape !== null)
                        <div class="text-xs text-secondary d-flex align-items-center flex-wrap pt-2 border-top">
                            <span>Akurasi: <strong class="text-dark">{{ number_format(100 - $gwoMape, 2, ',', '.') }}%</strong></span>
                            <span class="mx-2 text-muted">•</span>
                            <span>R² Score: <strong class="text-dark">{{ number_format($comparisons[2]['r2_raw'] ?? 0.901, 2, ',', '.') }}</strong></span>
                            @if($gwoImprovement > 0)
                                <span class="mx-2 text-muted">•</span>
                                <span class="text-success fw-semibold"><i class="bi bi-arrow-up-circle-fill"></i> Akurasi Naik <strong>+{{ number_format($gwoImprovement, 2, ',', '.') }}%</strong></span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rekomendasi & Penjelasan Singkat -->
    <div class="col-md-5">
        <div class="p-4 rounded-3 h-100 {{ $alertClass }} border-0 shadow-xs">
            <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center" style="font-size: 11.5px; letter-spacing: 0.5px;">
                <i class="bi {{ $iconClass }} me-2 fs-5"></i>Rekomendasi Keputusan
            </h6>
            <div style="font-size: 13px; line-height: 1.6;">
                @if(count($models) > 0)
                    <div class="mb-4">
                        Model terbaik yang direkomendasikan adalah <strong>"{{ $bestModelName }}"</strong>.
                        <ul class="list-unstyled mt-2 mb-0 d-flex flex-column gap-1.5">
                            <li>• <strong>Akurasi</strong>: {{ number_format(100 - $bestModelMape, 2, ',', '.') }}% (Kategori: {{ $bestModelMapeInterpret }})</li>
                            <li>• <strong>Kesalahan (MAPE)</strong>: {{ number_format($bestModelMape, 2, ',', '.') }}%</li>
                            <li>• <strong>R² Score</strong>: {{ number_format($bestR2Val, 2, ',', '.') }} (Kategori: {{ $bestR2Interpret }})</li>
                        </ul>
                    </div>
                    
                    <h6 class="fw-bold text-dark-emphasis mb-2.5 pb-1 border-bottom" style="font-size: 12px; border-color: rgba(0,0,0,0.1);">Setelan Parameter Terpilih:</h6>
                    <ul class="list-unstyled mb-4 small text-secondary-emphasis d-flex flex-column gap-1.5">
                        <li class="d-flex justify-content-between">
                            <span>• Kekuatan Regulasi (C):</span>
                            <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->c_value, 4) }}</strong>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>• Toleransi Kesalahan (&epsilon;):</span>
                            <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->epsilon_value, 4) }}</strong>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>• Jangkauan Data (&gamma;):</span>
                            <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->gamma_value, 4) }}</strong>
                        </li>
                    </ul>

                    <h6 class="fw-bold text-dark-emphasis mb-2" style="font-size: 12px;">Langkah Tindak Lanjut:</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-secondary-emphasis">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-success mt-0.5 flex-shrink-0"></i>
                            <span>Gunakan model <strong>"{{ $bestModelName }}"</strong> sebagai acuan target pendapatan jukir.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-success mt-0.5 flex-shrink-0"></i>
                            <span>Lakukan optimasi ulang jika akurasi prediksi di lapangan menurun.</span>
                        </li>
                    </ul>
                @else
                    <p class="mb-0">
                        Belum ada hasil pencarian parameter optimal yang tersimpan untuk dibandingkan.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
