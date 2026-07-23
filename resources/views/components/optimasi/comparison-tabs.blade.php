@props([
    'comparisons',
    'chartMetrics',
    'lastRun',
    'gsRun',
    'gwoRun',
    'rayons',
    'rayonId' => 0,
    'readonly' => false,
    'gridBest' => null,
    'gwoBest' => null
])

<div id="results-step-content-5" class="step-opt-content {{ $readonly ? '' : 'd-none' }}">
    <!-- Result Cards (Grid Search vs GWO) -->
    @if($gridBest && $gwoBest)
        <div class="row g-4 mb-4">
            <!-- Grid Search Result Card -->
            <div class="col-md-6">
                <div class="card h-100 border-start border-4 border-start-secondary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-grid-3x3 me-2"></i>Hasil Tuning Grid Search</h5>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6"><span class="small text-secondary">Best C:</span></div>
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gridBest['c'] }}</span></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><span class="small text-secondary">Epsilon (&epsilon;):</span></div>
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gridBest['epsilon'] }}</span></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><span class="small text-secondary">Gamma (&gamma;):</span></div>
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gridBest['gamma'] }}</span></div>
                        </div>
                        <div class="row g-2 pt-2 border-top mt-2">
                            <div class="col-6"><span class="small text-secondary fw-bold">Akurasi Model:</span></div>
                            <div class="col-6"><span class="small fw-bold text-success">{{ $gridBest['accuracy'] }}</span></div>
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
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwoBest['c'] }}</span></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><span class="small text-secondary">Epsilon (&epsilon;):</span></div>
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwoBest['epsilon'] }}</span></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><span class="small text-secondary">Gamma (&gamma;):</span></div>
                            <div class="col-6"><span class="small fw-semibold text-dark">{{ $gwoBest['gamma'] }}</span></div>
                        </div>
                        <div class="row g-2 pt-2 border-top mt-2">
                            <div class="col-6"><span class="small text-secondary fw-bold">Akurasi Model:</span></div>
                            <div class="col-6"><span class="small fw-bold text-success">{{ $gwoBest['accuracy'] }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Comparison Table -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title border-0 pb-0 mb-0">
                    <i class="bi bi-table me-2 text-primary-custom"></i>
                    {{ $readonly ? 'Tabel Hasil Perbandingan Optimasi' : 'Hasil Optimasi Parameter' }}
                </h5>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5"
                    data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal"
                    style="border-radius: 8px; font-size: 11.5px; padding: 4px 10px;">
                    <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="comparisonTable">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th>C</th>
                            <th>Epsilon (&epsilon;)</th>
                            <th>Gamma (&gamma;)</th>
                            <th>MAE</th>
                            <th>RMSE</th>
                            <th>MAPE</th>
                            @if($readonly)
                                <th>R² Score</th>
                                <th>Akurasi</th>
                            @else
                                <th>Akurasi</th>
                                <th>R² Score</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisons as $comp)
                            <tr>
                                <td style="font-weight: 600;">{{ $comp['metode'] ?? ($comp['name'] ?? '-') }}</td>
                                <td>{{ $comp['c'] }}</td>
                                <td>{{ $comp['epsilon'] }}</td>
                                <td>{{ $comp['gamma'] }}</td>
                                <td>{{ $comp['mae'] }}</td>
                                <td>{{ $comp['rmse'] }}</td>
                                @if($readonly)
                                    <td>{{ $comp['mape'] }}</td>
                                    <td>{{ $comp['r2'] }}</td>
                                    <td style="font-weight: 600;" class="text-success">{{ $comp['akurasi'] }}</td>
                                @else
                                    <td style="font-weight: 600;" class="text-success">{{ $comp['mape'] }}</td>
                                    <td style="font-weight: 700;" class="text-primary">{{ $comp['akurasi'] }}</td>
                                    <td>{{ $comp['r2'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grafik Perbandingan Performa -->
    <div class="card bg-white mb-4 shadow-sm border border-light">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-bar-chart-line me-2 text-primary-custom"></i>Grafik Perbandingan Performa Model</h5>
            <div style="position: relative; height: 320px;">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Grafik Tren Perbandingan Ketiga Model SVR -->
    <div class="card bg-white mb-4 shadow-sm border border-light">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2 text-primary-custom"></i>Grafik Tren Perbandingan Hasil Prediksi <span class="badge bg-light text-dark border ms-1 small" id="comparison-chart-data-count" style="font-size: 11px;">Total Data: -</span></h5>
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
            <h5 class="card-title text-dark mb-4"><i class="bi bi-chat-left-text-fill me-2 text-primary-custom"></i>Analisis Perbandingan Performa Model</h5>
            <x-model-comparison-analysis 
                :comparisons="$comparisons" 
                :chartMetrics="$chartMetrics" 
                :lastRun="$lastRun" 
                :gsRun="$gsRun" 
                :gwoRun="$gwoRun" 
            />
        </div>
    </div>

    <!-- Back Button for Operator -->
    @if(!$readonly)
        <div class="d-flex justify-content-start mt-4 mb-4">
            <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm"
                onclick="currentMethod === 'grid' ? goToGridStep(4) : goToGwoStep(4)">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Hasil Optimasi
            </button>
        </div>
    @endif
</div>
