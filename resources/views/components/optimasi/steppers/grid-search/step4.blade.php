@props([
    'gsRun',
    'gsMetricsObj',
    'rayons',
    'gsChartData',
    'gsPredictions',
    'rayonId',
    'readonly' => false,
    'gsTotalDiffPercent' => 0,
    'gsMaxActualDate' => '-',
    'gsMaxActualVal' => 0,
    'gsPredictedAtMaxActual' => 0,
    'gsMaxPredictedDate' => '-',
    'gsMaxPredictedVal' => 0,
    'gsBestRayon' => null,
    'gsWorstRayon' => null,
    'gsAvgDailyDeviation' => 0,
])

<div id="grid-step-content-4" class="step-opt-content d-none">
    <div id="grid-evaluation-details" class="evaluation-container">
        @if(!$gsRun || !$gsMetricsObj)
            <div class="card text-center py-5 shadow-sm border border-light bg-white mb-4">
                <div class="card-body py-4">
                    <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
                    <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi Grid Search</h5>
                    <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan untuk metode Grid Search. Silakan jalankan <strong>Optimasi Grid Search</strong> terlebih dahulu.</p>
                </div>
            </div>
        @else
            @php
                $gsTrParts = $gsRun->train_period ? explode(' - ', $gsRun->train_period) : [];
                $gsTrDays = count($gsTrParts) === 2 ? \Carbon\Carbon::parse(trim($gsTrParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gsTrParts[1]))) + 1 : null;
                $gsTeParts = $gsRun->test_period ? explode(' - ', $gsRun->test_period) : [];
                $gsTeDays = count($gsTeParts) === 2 ? \Carbon\Carbon::parse(trim($gsTeParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gsTeParts[1]))) + 1 : null;
            @endphp
            <!-- Ringkasan Data Training/Testing Grid Search -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <h6 class="card-title text-warning mb-3"><i class="bi bi-grid-3x3 me-2"></i>Ringkasan Dataset SVR + Grid Search</h6>
                    <div class="row g-3 small">
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Jumlah Data Efektif</span>
                            <strong class="fs-6 text-dark">{{ number_format($gsRun->train_rows + $gsRun->test_rows, 0, ',', '.') }} baris</strong>
                            <span class="text-muted d-block" style="font-size: 10px;">(mentah: {{ number_format($gsRun->total_rows, 0, ',', '.') }} baris, terpotong 150 lag)</span>
                        </div>
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Training (80%)</span>
                            <strong class="text-dark d-block mb-1">{{ number_format($gsRun->train_rows, 0, ',', '.') }} baris
                                @if($gsTrDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($gsTrDays, 0, ',', '.') }} hari)</span>@endif
                            </strong>
                            <span class="text-muted" style="font-size: 10px;">Periode: {{ $gsRun->train_period }}</span>
                        </div>
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Testing (20%)</span>
                            <strong class="text-dark d-block mb-1">{{ number_format($gsRun->test_rows, 0, ',', '.') }} baris
                                @if($gsTeDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($gsTeDays, 0, ',', '.') }} hari)</span>@endif
                            </strong>
                            <span class="text-muted" style="font-size: 10px;">Periode: {{ $gsRun->test_period }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Waktu Selesai</span>
                            <strong class="text-dark d-block">{{ Carbon\Carbon::parse($gsRun->finished_at)->translatedFormat('d F Y') }}</strong>
                            <span class="text-muted d-block" style="font-size: 10px;">Jam: {{ Carbon\Carbon::parse($gsRun->finished_at)->format('H:i:s') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Metrik Evaluasi Model SVR + Grid Search -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill me-2 text-warning"></i>Hasil Evaluasi Model SVR + Grid Search</h5>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 12px; padding: 5px 12px;">
                    <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
                </button>
            </div>
            <div class="row g-3 mb-4">
                <!-- MAE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Mean Absolute Error (MAE)</span>
                        <span class="metric-value-custom">Rp {{ number_format($gsMetricsObj->mae, 0, ',', '.') }}</span>
                        <span class="text-muted small" style="font-size: 11px;">Rata-rata selisih nominal error</span>
                    </div>
                </div>
                <!-- RMSE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Root Mean Squared Error (RMSE)</span>
                        <span class="metric-value-custom">Rp {{ number_format($gsMetricsObj->rmse, 0, ',', '.') }}</span>
                        <span class="text-muted small" style="font-size: 11px;">Ukuran penyimpangan ekstrem</span>
                    </div>
                </div>
                <!-- R2 Score -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">R² Score</span>
                        <span class="metric-value-custom">{{ number_format($gsMetricsObj->r2_score, 2, ',', '.') }}</span>
                        @php
                            $gsR2Val = $gsMetricsObj->r2_score;
                            $gsR2Interpret = 'Lemah';
                            $gsR2Class = 'bg-danger-subtle text-danger';
                            if ($gsR2Val >= 0.67) {
                                $gsR2Interpret = 'Kuat';
                                $gsR2Class = 'bg-success-subtle text-success';
                            } elseif ($gsR2Val >= 0.33) {
                                $gsR2Interpret = 'Moderat';
                                $gsR2Class = 'bg-primary-subtle text-primary';
                            }
                        @endphp
                        <div>
                            <span class="badge border-0 {{ $gsR2Class }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $gsR2Interpret }}</span>
                        </div>
                    </div>
                </div>
                <!-- MAPE detail percentage -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Persentase MAPE</span>
                        <span class="metric-value-custom text-secondary">{{ number_format($gsMetricsObj->mape, 2, ',', '.') }}%</span>
                        <span class="text-muted small" style="font-size: 11px;">Rata-rata persentase error</span>
                    </div>
                </div>
                <!-- Akurasi MAPE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Akurasi (100% - MAPE)</span>
                        <span class="metric-value-custom text-success">{{ number_format($gsMetricsObj->accuracy, 2, ',', '.') }}%</span>
                        @php
                            $gsMapeVal = $gsMetricsObj->mape;
                            $gsMapeInterpret = 'Cukup / Reasonable';
                            $gsMapeClass = 'bg-warning-subtle text-warning';
                            if ($gsMapeVal < 10) {
                                $gsMapeInterpret = 'Sangat Akurat';
                                $gsMapeClass = 'bg-success-subtle text-success';
                            } elseif ($gsMapeVal < 20) {
                                $gsMapeInterpret = 'Baik / Good';
                                $gsMapeClass = 'bg-success-subtle text-success';
                            } elseif ($gsMapeVal > 50) {
                                $gsMapeInterpret = 'Lemah / Weak';
                                $gsMapeClass = 'bg-danger-subtle text-danger';
                            }
                        @endphp
                        <div>
                            <span class="badge border-0 {{ $gsMapeClass }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $gsMapeInterpret }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Analisis & Rekomendasi Model SVR + Grid Search -->
            <div class="card mb-4 bg-white shadow-sm border border-light">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3"><i class="bi bi-chat-left-text-fill me-2 text-primary-custom"></i>Analisis Kinerja & Rekomendasi Model (Grid Search)</h5>
                    @php
                        $gsMape = $gsMetricsObj->mape;
                        $gsR2 = $gsMetricsObj->r2_score;
                        $gsRmse = $gsMetricsObj->rmse;
                        $gsMeanActual = $gsRun->predictionResults()->avg('actual_value') ?? 0;
                    @endphp
                    <x-model-analysis-results :mape="$gsMape" :r2="$gsR2" :rmse="$gsRmse" :meanActual="$gsMeanActual" target="grid_search" />
                </div>
            </div>

            <!-- Grafik Aktual vs Prediksi Grid Search -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + Grid Search <span class="badge bg-light text-dark border ms-1 small" id="gs-chart-data-count" style="font-size: 11px;">Total Data: {{ count($gsChartData) }}</span></h5>
                        <div class="d-flex align-items-center gap-2">
                            <label for="rayon_id_gs_chart" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                            <select id="rayon_id_gs_chart" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px; width: 160px;" onchange="window.updateGsChart(this.value)">
                                <option value="0">Semua Rayon</option>
                                @foreach($rayons as $rayon)
                                    <option value="{{ $rayon->id }}" {{ $rayonId == $rayon->id ? 'selected' : '' }}>{{ $rayon->nama_rayon }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="height: 380px; position: relative; width: 100%;">
                        <canvas id="gsChart"></canvas>
                    </div>

                    <!-- Analisis Singkat Grafik (Grid Search) -->
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-warning shadow-sm">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary-custom me-1"></i>Analisis Grafik (Grid Search)</h6>
                        <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                            <li>
                                @if($gsTotalDiffPercent < 5)
                                    Prediksi sangat sesuai dengan data aktual — selisih kumulatif hanya <strong>{{ number_format($gsTotalDiffPercent, 2, ',', '.') }}%</strong>.
                                @elseif($gsTotalDiffPercent < 15)
                                    Prediksi cukup sesuai dengan data aktual — selisih kumulatif <strong>{{ number_format($gsTotalDiffPercent, 2, ',', '.') }}%</strong>.
                                @else
                                    Terdapat selisih yang cukup signifikan antara prediksi dan aktual (<strong>{{ number_format($gsTotalDiffPercent, 2, ',', '.') }}%</strong>); parameter perlu dioptimalkan.
                                @endif
                            </li>
                            <li>Puncak aktual terjadi pada <strong>{{ $gsMaxActualDate }}</strong> (Rp {{ number_format($gsMaxActualVal, 0, ',', '.') }}), prediksi pada hari itu: <strong>Rp {{ number_format($gsPredictedAtMaxActual, 0, ',', '.') }}</strong>.</li>
                            <li>Puncak prediksi jatuh pada <strong>{{ $gsMaxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($gsMaxPredictedVal, 0, ',', '.') }}</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabel Hasil Prediksi Grid Search -->
            <div class="card mb-4 bg-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 border-0 pb-0"><i class="bi bi-table me-2 text-primary-custom"></i>Tabel Hasil Prediksi Grid Search (Data Testing)</h5>

                            <!-- Rayon Filter Form -->
                            <form method="GET" action="{{ route('operator.optimasi.index') }}" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="method" value="grid">
                                <input type="hidden" name="grid_step" value="4">
                                <input type="hidden" name="gwo_step" value="{{ request('gwo_step', 1) }}">
                                <label for="rayon_id_gs" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                                <select id="rayon_id_gs" name="rayon_id" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px;" onchange="this.form.submit()">
                                    <option value="0">Semua Rayon</option>
                                    @foreach($rayons as $rayon)
                                        <option value="{{ $rayon->id }}" {{ $rayonId == $rayon->id ? 'selected' : '' }}>{{ $rayon->nama_rayon }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <x-optimasi.tables.result-table :predictions="$gsPredictions" method="grid" :bestRayon="$gsBestRayon" :worstRayon="$gsWorstRayon" :avgDailyDeviation="$gsAvgDailyDeviation" :readonly="$readonly" />
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4 mb-4">
                <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGridStep(2)">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Konfigurasi
                </button>
                <button type="button" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGridStep(5)">
                    Lanjut ke Perbandingan <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        @endif
    </div>
</div>
