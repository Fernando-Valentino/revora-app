@props([
    'gwoRun',
    'gwoMetricsObj',
    'rayons',
    'gwoChartData',
    'gwoPredictions',
    'rayonId',
    'readonly' => false,
    'gwoTotalDiffPercent' => 0,
    'gwoMaxActualDate' => '-',
    'gwoMaxActualVal' => 0,
    'gwoPredictedAtMaxActual' => 0,
    'gwoMaxPredictedDate' => '-',
    'gwoMaxPredictedVal' => 0,
    'gwoBestRayon' => null,
    'gwoWorstRayon' => null,
    'gwoAvgDailyDeviation' => 0,
])

<div id="gwo-step-content-4" class="step-opt-content d-none">
    <div id="gwo-evaluation-details" class="evaluation-container">
        @if(!$gwoRun || !$gwoMetricsObj)
            <div class="card text-center py-5 shadow-sm border border-light bg-white mb-4">
                <div class="card-body py-4">
                    <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
                    <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi GWO</h5>
                    <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan untuk metode Grey Wolf Optimizer. Silakan jalankan <strong>Optimasi GWO</strong> terlebih dahulu.</p>
                </div>
            </div>
        @else
            @php
                $gwoTrParts = $gwoRun->train_period ? explode(' - ', $gwoRun->train_period) : [];
                $gwoTrDays = count($gwoTrParts) === 2 ? \Carbon\Carbon::parse(trim($gwoTrParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gwoTrParts[1]))) + 1 : null;
                $gwoTeParts = $gwoRun->test_period ? explode(' - ', $gwoRun->test_period) : [];
                $gwoTeDays = count($gwoTeParts) === 2 ? \Carbon\Carbon::parse(trim($gwoTeParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gwoTeParts[1]))) + 1 : null;
            @endphp
            <!-- Ringkasan Data Training/Testing GWO -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <h6 class="card-title text-success mb-3"><i class="bi bi-activity me-2"></i>Ringkasan Dataset SVR + GWO (Grey Wolf)</h6>
                    <div class="row g-3 small">
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Jumlah Data Efektif</span>
                            <strong class="fs-6 text-dark">{{ number_format($gwoRun->train_rows + $gwoRun->test_rows, 0, ',', '.') }} baris</strong>
                            <span class="text-muted d-block" style="font-size: 10px;">(mentah: {{ number_format($gwoRun->total_rows, 0, ',', '.') }} baris, terpotong 150 lag)</span>
                        </div>
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Training (80%)</span>
                            <strong class="text-dark d-block mb-1">{{ number_format($gwoRun->train_rows, 0, ',', '.') }} baris
                                @if($gwoTrDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($gwoTrDays, 0, ',', '.') }} hari)</span>@endif
                            </strong>
                            <span class="text-muted" style="font-size: 10px;">Periode: {{ $gwoRun->train_period }}</span>
                        </div>
                        <div class="col-6 col-md-3 border-end border-light">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Testing (20%)</span>
                            <strong class="text-dark d-block mb-1">{{ number_format($gwoRun->test_rows, 0, ',', '.') }} baris
                                @if($gwoTeDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($gwoTeDays, 0, ',', '.') }} hari)</span>@endif
                            </strong>
                            <span class="text-muted" style="font-size: 10px;">Periode: {{ $gwoRun->test_period }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Waktu Selesai</span>
                            <strong class="text-dark d-block">{{ Carbon\Carbon::parse($gwoRun->finished_at)->translatedFormat('d F Y') }}</strong>
                            <span class="text-muted d-block" style="font-size: 10px;">Jam: {{ Carbon\Carbon::parse($gwoRun->finished_at)->format('H:i:s') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Metrik Evaluasi Model SVR + GWO -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill me-2 text-success"></i>Hasil Evaluasi Model SVR + GWO</h5>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 12px; padding: 5px 12px;">
                    <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
                </button>
            </div>
            <div class="row g-3 mb-4">
                <!-- MAE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Mean Absolute Error (MAE)</span>
                        <span class="metric-value-custom">Rp {{ number_format($gwoMetricsObj->mae, 0, ',', '.') }}</span>
                        <span class="text-muted small" style="font-size: 11px;">Rata-rata selisih nominal error</span>
                    </div>
                </div>
                <!-- RMSE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Root Mean Squared Error (RMSE)</span>
                        <span class="metric-value-custom">Rp {{ number_format($gwoMetricsObj->rmse, 0, ',', '.') }}</span>
                        <span class="text-muted small" style="font-size: 11px;">Ukuran penyimpangan ekstrem</span>
                    </div>
                </div>
                <!-- R2 Score -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">R² Score</span>
                        <span class="metric-value-custom">{{ number_format($gwoMetricsObj->r2_score, 2, ',', '.') }}</span>
                        @php
                            $gwoR2Val = $gwoMetricsObj->r2_score;
                            $gwoR2Interpret = 'Lemah';
                            $gwoR2Class = 'bg-danger-subtle text-danger';
                            if ($gwoR2Val >= 0.67) {
                                $gwoR2Interpret = 'Kuat';
                                $gwoR2Class = 'bg-success-subtle text-success';
                            } elseif ($gwoR2Val >= 0.33) {
                                $gwoR2Interpret = 'Moderat';
                                $gwoR2Class = 'bg-primary-subtle text-primary';
                            }
                        @endphp
                        <div>
                            <span class="badge border-0 {{ $gwoR2Class }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $gwoR2Interpret }}</span>
                        </div>
                    </div>
                </div>
                <!-- MAPE detail percentage -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Persentase MAPE</span>
                        <span class="metric-value-custom text-secondary">{{ number_format($gwoMetricsObj->mape, 2, ',', '.') }}%</span>
                        <span class="text-muted small" style="font-size: 11px;">Rata-rata persentase error</span>
                    </div>
                </div>
                <!-- Akurasi MAPE -->
                <div class="col-12 col-md-6 col-lg">
                    <div class="metric-card-custom">
                        <span class="metric-label-custom">Akurasi (100% - MAPE)</span>
                        <span class="metric-value-custom text-success">{{ number_format($gwoMetricsObj->accuracy, 2, ',', '.') }}%</span>
                        @php
                            $gwoMapeVal = $gwoMetricsObj->mape;
                            $gwoMapeInterpret = 'Cukup / Reasonable';
                            $gwoMapeClass = 'bg-warning-subtle text-warning';
                            if ($gwoMapeVal < 10) {
                                $gwoMapeInterpret = 'Sangat Akurat';
                                $gwoMapeClass = 'bg-success-subtle text-success';
                            } elseif ($gwoMapeVal < 20) {
                                $gwoMapeInterpret = 'Baik / Good';
                                $gwoMapeClass = 'bg-success-subtle text-success';
                            } elseif ($gwoMapeVal > 50) {
                                $gwoMapeInterpret = 'Lemah / Weak';
                                $gwoMapeClass = 'bg-danger-subtle text-danger';
                            }
                        @endphp
                        <div>
                            <span class="badge border-0 {{ $gwoMapeClass }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $gwoMapeInterpret }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Analisis & Rekomendasi Model SVR + GWO -->
            <div class="card mb-4 bg-white shadow-sm border border-light">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3"><i class="bi bi-chat-left-text-fill me-2 text-primary-custom"></i>Analisis Kinerja & Rekomendasi Model (GWO)</h5>
                    @php
                        $gwoMape = $gwoMetricsObj->mape;
                        $gwoR2 = $gwoMetricsObj->r2_score;
                        $gwoRmse = $gwoMetricsObj->rmse;
                        $gwoMeanActual = $gwoRun->predictionResults()->avg('actual_value') ?? 0;
                    @endphp
                    <x-model-analysis-results :mape="$gwoMape" :r2="$gwoR2" :rmse="$gwoRmse" :meanActual="$gwoMeanActual" target="gwo" />
                </div>
            </div>

            <!-- Grafik Aktual vs Prediksi GWO -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + GWO <span class="badge bg-light text-dark border ms-1 small" id="gwo-chart-data-count" style="font-size: 11px;">Total Data: {{ count($gwoChartData) }}</span></h5>
                        <div class="d-flex align-items-center gap-2">
                            <label for="rayon_id_gwo_chart" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                            <select id="rayon_id_gwo_chart" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px; width: 160px;" onchange="window.updateGwoChart(this.value)">
                                <option value="0">Semua Rayon</option>
                                @foreach($rayons as $rayon)
                                    <option value="{{ $rayon->id }}" {{ $rayonId == $rayon->id ? 'selected' : '' }}>{{ $rayon->nama_rayon }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="height: 380px; position: relative; width: 100%;">
                        <canvas id="gwoChart"></canvas>
                    </div>

                    <!-- Analisis Singkat Grafik (GWO) -->
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary-custom me-1"></i>Analisis Grafik (GWO)</h6>
                        <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                            <li>
                                @if($gwoTotalDiffPercent < 5)
                                    Prediksi sangat sesuai dengan data aktual — selisih kumulatif hanya <strong>{{ number_format($gwoTotalDiffPercent, 2, ',', '.') }}%</strong>.
                                @elseif($gwoTotalDiffPercent < 15)
                                    Prediksi cukup sesuai dengan data aktual — selisih kumulatif <strong>{{ number_format($gwoTotalDiffPercent, 2, ',', '.') }}%</strong>.
                                @else
                                    Terdapat selisih yang cukup signifikan antara prediksi dan aktual (<strong>{{ number_format($gwoTotalDiffPercent, 2, ',', '.') }}%</strong>); parameter perlu dioptimalkan.
                                @endif
                            </li>
                            <li>Puncak aktual terjadi pada <strong>{{ $gwoMaxActualDate }}</strong> (Rp {{ number_format($gwoMaxActualVal, 0, ',', '.') }}), prediksi pada hari itu: <strong>Rp {{ number_format($gwoPredictedAtMaxActual, 0, ',', '.') }}</strong>.</li>
                            <li>Puncak prediksi jatuh pada <strong>{{ $gwoMaxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($gwoMaxPredictedVal, 0, ',', '.') }}</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabel Hasil Prediksi GWO -->
            <div class="card mb-4 bg-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 border-0 pb-0"><i class="bi bi-table me-2 text-primary-custom"></i>Tabel Hasil Prediksi GWO (Data Testing)</h5>

                            <!-- Rayon Filter -->
                            <div class="d-flex align-items-center gap-2">
                                <label for="rayon_id_gwo" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                                <select id="rayon_id_gwo" name="rayon_id" class="form-select form-select-sm optimasi-rayon-filter" style="font-size: 12px; padding: 4px 12px; height: 32px; width: auto; min-width: 140px;">
                                    <option value="0">Semua Rayon</option>
                                    @foreach($rayons as $rayon)
                                        <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <x-optimasi.tables.result-table :predictions="$gwoPredictions" method="gwo" :bestRayon="$gwoBestRayon" :worstRayon="$gwoWorstRayon" :avgDailyDeviation="$gwoAvgDailyDeviation" :readonly="$readonly" />
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4 mb-4">
                <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGwoStep(2)">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Konfigurasi
                </button>
                <button type="button" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGwoStep(5)">
                    Lanjut ke Perbandingan <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        @endif
    </div>
</div>
