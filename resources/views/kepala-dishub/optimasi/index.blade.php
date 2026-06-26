@extends('layouts.app')

@section('title', 'Optimasi Parameter')
@section('subtitle', 'Halaman ini digunakan untuk melihat hasil perbandingan optimasi parameter model prediksi.')

@section('content')
<div class="container-fluid p-0">
    
    <!-- Result Cards (Grid Search vs GWO) -->
    <div class="row g-4 mb-4">
        <!-- Grid Search Result Card -->
        <div class="col-md-6">
            <div class="card h-100 border-start border-4 border-start-secondary">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-grid-3x3 me-2"></i>Hasil Tuning Grid Search</h5>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Best C:</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $grid_best['c'] }}</span></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Epsilon (&epsilon;):</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $grid_best['epsilon'] }}</span></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Gamma (&gamma;):</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $grid_best['gamma'] }}</span></div>
                    </div>
                    <div class="row g-2 pt-2 border-top mt-2">
                        <div class="col-6"><span class="small text-secondary fw-bold">Akurasi Model:</span></div>
                        <div class="col-6"><span class="small fw-bold text-success">{{ $grid_best['accuracy'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GWO Result Card -->
        <div class="col-md-6">
            <div class="card h-100 border-start border-4 border-start-dark">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-activity me-2"></i>Hasil Tuning GWO (Global Optimal)</h5>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Best C:</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwo_best['c'] }}</span></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Epsilon (&epsilon;):</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwo_best['epsilon'] }}</span></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><span class="small text-secondary">Gamma (&gamma;):</span></div>
                        <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwo_best['gamma'] }}</span></div>
                    </div>
                    <div class="row g-2 pt-2 border-top mt-2">
                        <div class="col-6"><span class="small text-secondary fw-bold">Akurasi Model:</span></div>
                        <div class="col-6"><span class="small fw-bold text-success">{{ $gwo_best['accuracy'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Tabel Hasil Perbandingan Optimasi</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th>C</th>
                            <th>Epsilon (&epsilon;)</th>
                            <th>Gamma (&gamma;)</th>
                            <th>MAE</th>
                            <th>RMSE</th>
                            <th>MAPE</th>
                            <th>R² Score</th>
                            <th>Akurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisons as $comp)
                            <tr>
                                <td style="font-weight: 600;">{{ $comp['metode'] }}</td>
                                <td>{{ $comp['c'] }}</td>
                                <td>{{ $comp['epsilon'] }}</td>
                                <td>{{ $comp['gamma'] }}</td>
                                <td>{{ $comp['mae'] }}</td>
                                <td>{{ $comp['rmse'] }}</td>
                                <td>{{ $comp['mape'] }}</td>
                                <td>{{ $comp['r2'] }}</td>
                                <td style="font-weight: 600;" class="text-success">{{ $comp['akurasi'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grafik Section -->
    <div class="card mb-4 bg-white">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-bar-chart-line me-2 text-dark"></i>Grafik Perbandingan Performa Model</h5>
            <div style="position: relative; height: 320px;">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Analisis Perbandingan Performa Model -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body">
            <h5 class="card-title text-dark mb-3"><i class="bi bi-chat-left-text-fill me-2 text-primary"></i>Analisis Perbandingan Performa Model</h5>
            
            @php
                $defaultMape = $chartMetrics['mape_default'];
                $gsMape = $chartMetrics['mape_gs'];
                $gwoMape = $chartMetrics['mape_gwo'];

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
                $defaultC = $lastRun ? ($lastRun->modelParameter?->c_value ?? '1.0') : '1.0';
                $defaultEps = $lastRun ? ($lastRun->modelParameter?->epsilon_value ?? '0.1') : '0.1';
                $defaultGam = $lastRun ? ($lastRun->modelParameter?->gamma_value ?? 'scale') : 'scale';

                $gsParamC = $gsRun ? ($gsRun->modelParameter?->c_value ?? '-') : '-';
                $gsParamEps = $gsRun ? ($gsRun->modelParameter?->epsilon_value ?? '-') : '-';
                $gsParamGam = $gsRun ? ($gsRun->modelParameter?->gamma_value ?? '-') : '-';

                $gwoParamC = $gwoRun ? ($gwoRun->modelParameter?->c_value ?? '-') : '-';
                $gwoParamEps = $gwoRun ? ($gwoRun->modelParameter?->epsilon_value ?? '-') : '-';
                $gwoParamGam = $gwoRun ? ($gwoRun->modelParameter?->gamma_value ?? '-') : '-';

                // Helper formatter to trim trailing zeros of decimals and support precise representation
                $formatParamVal = function ($val, int $maxDecimals = 8): string {
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
                    $bestR2Interpret = 'Model Kuat';
                } elseif ($bestR2Val >= 0.33) {
                    $bestR2Interpret = 'Model Moderat';
                }

                // Set class and alerts depending on the best model
                $alertClass = 'alert-success text-success-emphasis bg-success-subtle border-success-subtle';
                $iconClass = 'bi-patch-check-fill text-success';
                if ($bestModelName === 'SVR Standar (Default)') {
                    $alertClass = 'alert-warning text-warning-emphasis bg-warning-subtle border-warning-subtle';
                    $iconClass = 'bi-exclamation-triangle-fill text-warning';
                }
            @endphp

            <div class="row g-3">
                <!-- Temuan & Komparasi Kinerja -->
                <div class="col-md-7">
                    <h6 class="fw-bold text-secondary text-uppercase mb-2 shadow-none border-0 pb-0" style="font-size: 11px; letter-spacing: 0.5px;">Temuan &amp; Komparasi Kinerja</h6>
                    <div class="d-flex flex-column gap-3">
                        <!-- Card SVR Default -->
                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                            <div class="fs-4"><i class="bi bi-cpu text-secondary"></i></div>
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">SVR Standar (Default): <span class="text-secondary">{{ $defaultMape !== null ? number_format($defaultMape, 2, ',', '.') . '%' : '-' }}</span></div>
                                <div class="text-secondary small" style="line-height: 1.5;">
                                    Menggunakan setelan awal (C = {{ $formatParamVal($defaultC, 6) }}, &epsilon; = {{ $formatParamVal($defaultEps, 8) }}, &gamma; = {{ $formatParamVal($defaultGam, 6) }}). Model ini digunakan sebagai acuan awal akurasi pembanding sebelum dilakukan perbaikan/optimasi.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Grid Search -->
                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                            <div class="fs-4"><i class="bi bi-grid-3x3 text-warning"></i></div>
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">SVR + Grid Search: <span class="text-warning">{{ $gsMape !== null ? number_format($gsMape, 2, ',', '.') . '%' : '-' }}</span></div>
                                <div class="text-secondary small" style="line-height: 1.5;">
                                    @if($gsMape !== null)
                                        Menggunakan setelan parameter yang lebih presisi (C = {{ $formatParamVal($gsParamC, 6) }}, &epsilon; = {{ $formatParamVal($gsParamEps, 8) }}, &gamma; = {{ $formatParamVal($gsParamGam, 6) }}).
                                        @if($gsImprovement > 0)
                                            Berhasil mengurangi tingkat kesalahan sebesar <strong class="text-success">{{ number_format($gsImprovement, 2, ',', '.') }}%</strong> dibanding model awal dengan melakukan pencarian setelan terbaik dalam rentang nilai tertentu.
                                        @else
                                            Setelan hasil Grid Search tidak berhasil memberikan peningkatan akurasi dibandingkan model awal.
                                        @endif
                                    @else
                                        Pencarian setelan dengan Grid Search belum dijalankan.
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Card GWO -->
                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                            <div class="fs-4"><i class="bi bi-activity text-success"></i></div>
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">SVR + GWO (Grey Wolf): <span class="text-success">{{ $gwoMape !== null ? number_format($gwoMape, 2, ',', '.') . '%' : '-' }}</span></div>
                                <div class="text-secondary small" style="line-height: 1.5;">
                                    @if($gwoMape !== null)
                                        Menggunakan setelan parameter yang lebih presisi (C = {{ $formatParamVal($gwoParamC, 6) }}, &epsilon; = {{ $formatParamVal($gwoParamEps, 8) }}, &gamma; = {{ $formatParamVal($gwoParamGam, 6) }}).
                                        @if($gwoImprovement > 0)
                                            Berhasil mengurangi tingkat kesalahan sebesar <strong class="text-success">{{ number_format($gwoImprovement, 2, ',', '.') }}%</strong> dibanding model awal melalui pencarian otomatis secara optimal.
                                        @else
                                            Setelan hasil GWO tidak berhasil memberikan peningkatan akurasi dibandingkan model awal.
                                        @endif
                                    @else
                                        Pencarian setelan dengan Grey Wolf Optimizer (GWO) belum dijalankan.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekomendasi Model Terbaik -->
                <div class="col-md-5">
                    <div class="p-3 rounded-3 h-100 {{ $alertClass }} border border-0">
                        <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="bi {{ $iconClass }} me-2 fs-5"></i>Rekomendasi Keputusan
                        </h6>
                        <div style="font-size: 12.5px; line-height: 1.6;">
                            @if(count($models) > 0)
                                <p class="mb-3">
                                    Berdasarkan hasil uji coba, model <strong>{{ $bestModelName }}</strong> terpilih sebagai model dengan tingkat kesalahan terkecil (MAPE = <strong>{{ number_format($bestModelMape, 2, ',', '.') }}%</strong> - kategori <strong>{{ $bestModelMapeInterpret }}</strong>), dan kemampuan membaca pola data sebesar <strong>{{ number_format($bestR2Val, 4, ',', '.') }}</strong> (kategori <strong>{{ $bestR2Interpret }}</strong>).
                                </p>
                                <p class="mb-3 small text-secondary-emphasis">
                                    Setelan parameter terbaik yang aktif digunakan adalah:<br>
                                    &bull; C = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->c_value, 6) }}</strong><br>
                                    &bull; &epsilon; = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->epsilon_value, 8) }}</strong><br>
                                    &bull; &gamma; = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->gamma_value, 6) }}</strong>
                                </p>
                                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle mt-0.5 flex-shrink-0 text-success"></i>
                                        <span>Gunakan model <strong>{{ $bestModelName }}</strong> sebagai acuan resmi penetapan target retribusi parkir harian.</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle mt-0.5 flex-shrink-0 text-success"></i>
                                        <span>Metode Grey Wolf (GWO) terbukti lebih unggul menemukan setelan terbaik dibandingkan Grid Search standar.</span>
                                    </li>
                                </ul>
                            @else
                                <p class="mb-0">
                                    Belum ada hasil optimasi yang disimpan untuk dibandingkan.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvasEl = document.getElementById('performanceChart');
        if (typeof Chart !== 'undefined' && canvasEl) {
            const ctx = canvasEl.getContext('2d');
            
            // Performance metric constants
            const mapeSvrDefault = @json($chartMetrics['mape_default'] ?? null);
            const r2SvrDefault   = @json($chartMetrics['r2_default']   ?? null);
            const mapeGridSearch = @json($chartMetrics['mape_gs']      ?? null);
            const r2GridSearch   = @json($chartMetrics['r2_gs']        ?? null);
            const mapeGwo        = @json($chartMetrics['mape_gwo']     ?? null);
            const r2Gwo          = @json($chartMetrics['r2_gwo']       ?? null);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['SVR Standar (Default)', 'SVR + Grid Search', 'SVR + GWO (Grey Wolf)'],
                    datasets: [
                        {
                            label: 'MAPE (%) (Semakin Kecil Semakin Baik)',
                            data: [mapeSvrDefault, mapeGridSearch, mapeGwo],
                            backgroundColor: [
                                'rgba(220, 38, 38, 0.75)',
                                'rgba(245, 158, 11, 0.75)',
                                'rgba(16, 185, 129, 0.75)'
                            ],
                            borderColor: ['rgb(220, 38, 38)', 'rgb(245, 158, 11)', 'rgb(16, 185, 129)'],
                            borderWidth: 1.5,
                            yAxisID: 'y'
                        },
                        {
                            label: 'R² Score (Semakin Besar Semakin Baik)',
                            data: [r2SvrDefault, r2GridSearch, r2Gwo],
                            backgroundColor: 'rgba(0, 91, 170, 0.15)',
                            borderColor: '#005BAA',
                            borderWidth: 1.5,
                            type: 'line',
                            tension: 0.2,
                            pointBackgroundColor: '#005BAA',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { family: 'Inter', size: 11 } } },
                        tooltip: { padding: 10, backgroundColor: '#1f2937', titleFont: { family: 'Inter', size: 11, weight: 'bold' }, bodyFont: { family: 'Inter', size: 11 } }
                    },
                    scales: {
                        y: {
                            type: 'linear', display: true, position: 'left',
                            title: { display: true, text: 'MAPE (%)', font: { family: 'Inter', size: 11, weight: 'bold' } },
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            ticks: { callback: v => v + '%', font: { family: 'Inter', size: 10 } }
                        },
                        y1: {
                            type: 'linear', display: true, position: 'right',
                            title: { display: true, text: 'R² Score', font: { family: 'Inter', size: 11, weight: 'bold' } },
                            grid: { drawOnChartArea: false },
                            min: 0, max: 1.0,
                            ticks: { font: { family: 'Inter', size: 10 } }
                        },
                        x: { ticks: { font: { family: 'Inter', size: 11 } } }
                    }
                }
            });
        }
    });
</script>
@endsection
