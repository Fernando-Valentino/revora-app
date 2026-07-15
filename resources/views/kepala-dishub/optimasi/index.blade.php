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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Tabel Hasil Perbandingan Optimasi</h5>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 11.5px; padding: 4px 10px;">
                    <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
                </button>
            </div>
            
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

    <!-- Grafik Tren Perbandingan Ketiga Model SVR -->
    @php
        $allDefaultPredictions = $lastRun ? $lastRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
        $allGsPredictionsData = $gsRun ? $gsRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
        $allGwoPredictionsData = $gwoRun ? $gwoRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
        
        $defaultMapped = $allDefaultPredictions->map(fn($p) => [
            'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
            'rayon_id' => (int)$p->rayon_id,
            'actual_value' => (double)$p->actual_value,
            'predicted_value' => (double)$p->predicted_value
        ])->toArray();

        $gsMapped = $allGsPredictionsData->map(fn($p) => [
            'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
            'rayon_id' => (int)$p->rayon_id,
            'actual_value' => (double)$p->actual_value,
            'predicted_value' => (double)$p->predicted_value
        ])->toArray();

        $gwoMapped = $allGwoPredictionsData->map(fn($p) => [
            'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
            'rayon_id' => (int)$p->rayon_id,
            'actual_value' => (double)$p->actual_value,
            'predicted_value' => (double)$p->predicted_value
        ])->toArray();
    @endphp
    <div class="card bg-white mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Grafik Tren Perbandingan Hasil Prediksi</h5>
                <div class="d-flex align-items-center gap-2">
                    <label for="rayon_id_comp" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                    <select id="rayon_id_comp" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px; width: 160px;" onchange="window.updateCompChart(this.value)">
                        <option value="0">Semua Rayon</option>
                        @foreach($rayons as $rayon)
                            <option value="{{ $rayon->id }}" {{ $rayonId == $rayon->id ? 'selected' : '' }}>{{ $rayon->nama_rayon }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="position: relative; height: 360px;">
                <canvas id="comparisonTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Analisis Perbandingan Performa Model Component -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body">
            <h5 class="card-title text-dark mb-4"><i class="bi bi-chat-left-text-fill me-2 text-primary"></i>Analisis Perbandingan Performa Model</h5>
            <x-model-comparison-analysis 
                :comparisons="$comparisons" 
                :chartMetrics="$chartMetrics" 
                :lastRun="$lastRun" 
                :gsRun="$gsRun" 
                :gwoRun="$gwoRun" 
            />
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

        // --- CLIENT-SIDE RAYON FILTERING DATA AND HELPERS ---
        const allDefaultPreds = @json($defaultMapped);
        const allGsPreds = @json($gsMapped);
        const allGwoPreds = @json($gwoMapped);

        function getFilteredData(preds, rayonId) {
            rayonId = parseInt(rayonId);
            if (rayonId === 0) {
                const grouped = {};
                preds.forEach(p => {
                    if (!grouped[p.tanggal]) {
                        grouped[p.tanggal] = { actual: 0, predicted: 0 };
                    }
                    grouped[p.tanggal].actual += p.actual_value;
                    grouped[p.tanggal].predicted += p.predicted_value;
                });
                const labels = Object.keys(grouped);
                const actual = labels.map(l => grouped[l].actual);
                const predicted = labels.map(l => grouped[l].predicted);
                return { labels, actual, predicted };
            } else {
                const filtered = preds.filter(p => p.rayon_id === rayonId);
                const labels = filtered.map(p => p.tanggal);
                const actual = filtered.map(p => p.actual_value);
                const predicted = filtered.map(p => p.predicted_value);
                return { labels, actual, predicted };
            }
        }

        window.updateCompChart = function(rayonId) {
            const defaultData = getFilteredData(allDefaultPreds, rayonId);
            const gsData = getFilteredData(allGsPreds, rayonId);
            const gwoData = getFilteredData(allGwoPreds, rayonId);
            
            if (window.comparisonChartInstance) {
                window.comparisonChartInstance.data.labels = gwoData.labels.length > 0 ? gwoData.labels : (gsData.labels.length > 0 ? gsData.labels : defaultData.labels);
                
                const dsActual = window.comparisonChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
                if (dsActual) dsActual.data = gwoData.actual.length > 0 ? gwoData.actual : (gsData.actual.length > 0 ? gsData.actual : defaultData.actual);
                
                const dsDefault = window.comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR Standar');
                if (dsDefault) dsDefault.data = defaultData.predicted;
                
                const dsGs = window.comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR + Grid Search');
                if (dsGs) dsGs.data = gsData.predicted;
                
                const dsGwo = window.comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR + GWO (Grey Wolf)');
                if (dsGwo) dsGwo.data = gwoData.predicted;
                
                window.comparisonChartInstance.update();
            }
        }

        // --- COMPARISON TREND LINE CHART INITIALIZATION ---
        const canvasCompEl = document.getElementById('comparisonTrendChart');
        if (typeof Chart !== 'undefined' && canvasCompEl) {
            const ctxComp = canvasCompEl.getContext('2d');
            
            const startRayonId = {{ $rayonId }};
            const defaultData = getFilteredData(allDefaultPreds, startRayonId);
            const gsData = getFilteredData(allGsPreds, startRayonId);
            const gwoData = getFilteredData(allGwoPreds, startRayonId);
            
            const labelsComp = gwoData.labels.length > 0 ? gwoData.labels : (gsData.labels.length > 0 ? gsData.labels : defaultData.labels);
            const actualDataComp = gwoData.actual.length > 0 ? gwoData.actual : (gsData.actual.length > 0 ? gsData.actual : defaultData.actual);
            
            const datasetsComp = [
                {
                    label: 'Pendapatan Aktual',
                    data: actualDataComp,
                    borderColor: '#005BAA',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: '#005BAA',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1,
                    pointRadius: 2.5,
                    pointHoverRadius: 4
                }
            ];

            if (defaultData.predicted.length > 0) {
                datasetsComp.push({
                    label: 'Prediksi SVR Standar',
                    data: defaultData.predicted,
                    borderColor: '#6c757d',
                    borderWidth: 1.5,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: '#6c757d',
                    pointRadius: 2,
                    borderDash: [4, 4]
                });
            }

            if (gsData.predicted.length > 0) {
                datasetsComp.push({
                    label: 'Prediksi SVR + Grid Search',
                    data: gsData.predicted,
                    borderColor: '#F59E0B',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: '#F59E0B',
                    pointRadius: 2,
                    borderDash: [3, 3]
                });
            }

            if (gwoData.predicted.length > 0) {
                datasetsComp.push({
                    label: 'Prediksi SVR + GWO (Grey Wolf)',
                    data: gwoData.predicted,
                    borderColor: '#10B981',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: '#10B981',
                    pointRadius: 2.5,
                    pointHoverRadius: 4
                });
            }

            window.comparisonChartInstance = new Chart(ctxComp, {
                type: 'line',
                data: {
                    labels: labelsComp,
                    datasets: datasetsComp
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 10,
                                padding: 12,
                                font: { family: 'Inter', size: 11, weight: '500' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            padding: 10,
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                            bodyFont: { family: 'Inter', size: 11 },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let val = context.raw;
                                    return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                font: { family: 'Inter', size: 10 },
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                }
                            },
                            grid: { borderDash: [5, 5], color: '#e2e8f0' }
                        },
                        x: {
                            ticks: { font: { family: 'Inter', size: 10 }, maxRotation: 45, minRotation: 0 }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
