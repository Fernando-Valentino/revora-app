@props([
    'lastRun',
    'chartData',
    'rayons',
    'metrics' => null,
])

<!-- STEP 4 CONTENT -->
<div id="step-content-4" class="step-content-section d-none">
    @php
        $allSvrPredictions = $lastRun ? $lastRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
        $allSvrMapped = $allSvrPredictions->map(fn($p) => [
            'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
            'rayon_id' => (int)$p->rayon_id,
            'actual_value' => (double)$p->actual_value,
            'predicted_value' => (double)$p->predicted_value
        ])->toArray();

        $rayonStats = collect([]);
        $bestRayon = null;
        $worstRayon = null;
        $avgDailyDeviation = 0;
        
        $maxActualDate = '-';
        $maxActualVal = 0;
        $maxPredictedDate = '-';
        $maxPredictedVal = 0;
        
        $totalActualSum = 0;
        $totalPredictedSum = 0;
        $totalDiff = 0;
        $totalDiffPercent = 0;
        $predictedAtMaxActual = 0;
        $maxActualAccuracy = 0;
        $actualAtMaxPredicted = 0;
        
        if ($lastRun) {
            $rayonStats = $lastRun->predictionResults()
                ->select('rayon_name', 
                    DB::raw('AVG(percentage_error) as avg_mape'), 
                    DB::raw('AVG(error_value) as avg_error'),
                    DB::raw('SUM(actual_value) as total_actual'), 
                    DB::raw('SUM(predicted_value) as total_predicted')
                )
                ->groupBy('rayon_name')
                ->get();
            
            $bestRayon = $rayonStats->sortBy('avg_mape')->first();
            $worstRayon = $rayonStats->sortByDesc('avg_mape')->first();
            
            $avgDailyDeviation = $lastRun->predictionResults()->avg('error_value') ?? 0;
            
            if ($chartData->count() > 0) {
                $maxActualRow = $chartData->sortByDesc('actual_value')->first();
                $maxPredictedRow = $chartData->sortByDesc('predicted_value')->first();
                
                $maxActualDate = $maxActualRow ? Carbon\Carbon::parse($maxActualRow->tanggal)->translatedFormat('d F Y') : '-';
                $maxActualVal = $maxActualRow ? $maxActualRow->actual_value : 0;
                
                $maxPredictedDate = $maxPredictedRow ? Carbon\Carbon::parse($maxPredictedRow->tanggal)->translatedFormat('d F Y') : '-';
                $maxPredictedVal = $maxPredictedRow ? $maxPredictedRow->predicted_value : 0;
                
                $predictedAtMaxActual = $maxActualRow ? $maxActualRow->predicted_value : 0;
                $actualAtMaxPredicted = $maxPredictedRow ? $maxPredictedRow->actual_value : 0;
                $maxActualAccuracy = $maxActualVal > 0 ? (1 - abs($maxActualVal - $predictedAtMaxActual) / $maxActualVal) * 100 : 0;
                
                $totalActualSum = $chartData->sum('actual_value');
                $totalPredictedSum = $chartData->sum('predicted_value');
                $totalDiff = abs($totalActualSum - $totalPredictedSum);
                $totalDiffPercent = $totalActualSum > 0 ? ($totalDiff / $totalActualSum) * 100 : 0;
            }
        }
    @endphp
    <!-- 4. Ringkasan Sukses Eksekusi Terakhir -->
@if($lastRun)
    @php
        $trainPeriodParts = $lastRun->train_period ? explode(' - ', $lastRun->train_period) : [];
        $trainDays = count($trainPeriodParts) === 2
            ? \Carbon\Carbon::parse(trim($trainPeriodParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($trainPeriodParts[1]))) + 1
            : null;

        $testPeriodParts = $lastRun->test_period ? explode(' - ', $lastRun->test_period) : [];
        $testDays = count($testPeriodParts) === 2
            ? \Carbon\Carbon::parse(trim($testPeriodParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($testPeriodParts[1]))) + 1
            : null;
    @endphp
    <div class="card mb-4 bg-white">
        <div class="card-body">
            <h5 class="card-title text-success"><i class="bi bi-check-circle-fill me-2"></i>Ringkasan Eksekusi Model SVR Terakhir</h5>
            <div class="row g-3 small">
                <div class="col-6 col-md-3 border-end border-light">
                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Jumlah Data Efektif</span>
                    <strong class="fs-6 text-dark">{{ number_format($lastRun->train_rows + $lastRun->test_rows, 0, ',', '.') }} baris</strong>
                    <span class="text-muted d-block" style="font-size: 10px;">(mentah: {{ number_format($lastRun->total_rows, 0, ',', '.') }} baris, terpotong 150 lag)</span>
                </div>
                <div class="col-6 col-md-3 border-end border-light">
                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Training (80%)</span>
                    <strong class="text-dark d-block mb-1">{{ number_format($lastRun->train_rows, 0, ',', '.') }} baris
                        @if($trainDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($trainDays, 0, ',', '.') }} hari)</span>@endif
                    </strong>
                    <span class="text-muted" style="font-size: 10px;">Periode: {{ $lastRun->train_period }}</span>
                </div>
                <div class="col-6 col-md-3 border-end border-light">
                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Data Testing (20%)</span>
                    <strong class="text-dark d-block mb-1">{{ number_format($lastRun->test_rows, 0, ',', '.') }} baris
                        @if($testDays) <span class="fw-normal text-secondary" style="font-size:11px;">({{ number_format($testDays, 0, ',', '.') }} hari)</span>@endif
                    </strong>
                    <span class="text-muted" style="font-size: 10px;">Periode: {{ $lastRun->test_period }}</span>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Waktu Penyelesaian</span>
                    <strong class="text-dark d-block">{{ Carbon\Carbon::parse($lastRun->finished_at)->translatedFormat('d F Y') }}</strong>
                    <span class="text-muted d-block" style="font-size: 10px;">Jam: {{ Carbon\Carbon::parse($lastRun->finished_at)->format('H:i:s') }} WIB</span>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- 5. Section Hasil Evaluasi Model & Visualisasi (Hanya jika lastRun sukses) -->
@if(!$lastRun)
    <div class="card text-center py-5 shadow-sm border border-light bg-white">
        <div class="card-body py-4">
            <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
            <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi</h5>
            <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan. Silakan jalankan <strong>Generate Prediksi SVR</strong> terlebih dahulu.</p>
        </div>
    </div>
@else
    <!-- Card Metrik Evaluasi Model SVR -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill me-2 text-primary"></i>Hasil Evaluasi Model SVR</h5>
        <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 12px; padding: 5px 12px;">
            <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
        </button>
    </div>
    <div class="row g-3 mb-4">
        <!-- MAE -->
        <div class="col-12 col-md-6 col-lg">
            <div class="metric-card-custom">
                <span class="metric-label-custom">Mean Absolute Error (MAE)</span>
                <span class="metric-value-custom">Rp {{ number_format($metrics->mae, 0, ',', '.') }}</span>
                <span class="text-muted small" style="font-size: 11px;">Rata-rata selisih nominal error</span>
            </div>
        </div>
        <!-- RMSE -->
        <div class="col-12 col-md-6 col-lg">
            <div class="metric-card-custom">
                <span class="metric-label-custom">Root Mean Squared Error (RMSE)</span>
                <span class="metric-value-custom">Rp {{ number_format($metrics->rmse, 0, ',', '.') }}</span>
                <span class="text-muted small" style="font-size: 11px;">Ukuran penyimpangan ekstrem</span>
            </div>
        </div>
        <!-- R2 Score -->
        <div class="col-12 col-md-6 col-lg">
            <div class="metric-card-custom">
                <span class="metric-label-custom">R² Score</span>
                <span class="metric-value-custom">{{ number_format($metrics->r2_score, 2, ',', '.') }}</span>
                @php
                    $r2Val = $metrics->r2_score;
                    $r2Interpret = 'Lemah';
                    $r2Class = 'bg-danger-subtle text-danger';
                    if ($r2Val >= 0.67) {
                        $r2Interpret = 'Kuat';
                        $r2Class = 'bg-success-subtle text-success';
                    } elseif ($r2Val >= 0.33) {
                        $r2Interpret = 'Moderat';
                        $r2Class = 'bg-primary-subtle text-primary';
                    }
                @endphp
                <div>
                    <span class="badge border-0 {{ $r2Class }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $r2Interpret }}</span>
                </div>
            </div>
        </div>
        <!-- MAPE detail percentage -->
        <div class="col-12 col-md-6 col-lg">
            <div class="metric-card-custom">
                <span class="metric-label-custom">Persentase MAPE</span>
                <span class="metric-value-custom text-secondary">{{ number_format($metrics->mape, 2, ',', '.') }}%</span>
                <span class="text-muted small" style="font-size: 11px;">Rata-rata persentase error</span>
            </div>
        </div>
        <!-- Akurasi MAPE -->
        <div class="col-12 col-md-6 col-lg">
            <div class="metric-card-custom">
                <span class="metric-label-custom">Akurasi (100% - MAPE)</span>
                <span class="metric-value-custom text-success">{{ number_format($metrics->accuracy, 2, ',', '.') }}%</span>
                @php
                    $mapeVal = $metrics->mape;
                    $mapeInterpret = 'Cukup / Reasonable';
                    $mapeClass = 'bg-warning-subtle text-warning';
                    if ($mapeVal < 10) {
                        $mapeInterpret = 'Sangat Akurat';
                        $mapeClass = 'bg-success-subtle text-success';
                    } elseif ($mapeVal < 20) {
                        $mapeInterpret = 'Baik / Good';
                        $mapeClass = 'bg-success-subtle text-success';
                    } elseif ($mapeVal > 50) {
                        $mapeInterpret = 'Lemah / Weak';
                        $mapeClass = 'bg-danger-subtle text-danger';
                    }
                @endphp
                <div>
                    <span class="badge border-0 {{ $mapeClass }} py-1 px-2.5" style="font-size: 9.5px; font-weight: 600;">{{ $mapeInterpret }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Analisis & Rekomendasi Model -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body">
            <h5 class="card-title text-dark mb-3"><i class="bi bi-chat-left-text-fill me-2 text-primary-custom"></i>Analisis Kinerja & Rekomendasi Model</h5>
            
            @php
                $mape = $metrics->mape;
                $r2 = $metrics->r2_score;
                $rmse = $metrics->rmse;
                $mae = $metrics->mae;
                // Hitung Rata-rata Aktual untuk RMSE
                $meanActual = $lastRun->predictionResults()->avg('actual_value') ?? 0;
            @endphp

            <x-model-analysis-results
                :mape="$mape"
                :r2="$r2"
                :rmse="$rmse"
                :mae="$mae"
                :meanActual="$meanActual"
                target="svr_default"
            />
        </div>
    </div>

    <!-- Grafik & Tabel Section - Ditumpuk Vertikal agar Rapi -->
    <div class="row g-4 mb-4">
        <!-- Grafik Line Chart (Lebar Penuh) -->
        <div class="col-12">
            <div class="card mb-0 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR <span class="badge bg-light text-dark border ms-1 small" id="svr-chart-data-count" style="font-size: 11px;">Total Data: {{ count($chartData) }}</span></h5>
                        <div class="d-flex align-items-center gap-2">
                            <label for="filter_rayon_id_chart" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                            <select id="filter_rayon_id_chart" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px; width: 160px;" onchange="window.updateSvrChart(this.value)">
                                <option value="0">Semua Rayon</option>
                                @foreach($rayons as $rayon)
                                    <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="height: 380px; position: relative; width: 100%;">
                        <canvas id="svrChart"></canvas>
                    </div>
                    <!-- Analisis Singkat Grafik -->
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-primary shadow-sm">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary-custom me-1"></i>Analisis Grafik</h6>
                        <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                            <li>
                                @if($totalDiffPercent < 5)
                                    Prediksi sangat sesuai dengan data aktual — selisih kumulatif hanya <strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong>.
                                @elseif($totalDiffPercent < 15)
                                    Prediksi cukup sesuai dengan data aktual — selisih kumulatif <strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong>.
                                @else
                                    Terdapat selisih yang cukup signifikan antara prediksi dan aktual (<strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong>); parameter perlu dioptimalkan.
                                @endif
                            </li>
                            <li>Puncak aktual terjadi pada <strong>{{ $maxActualDate }}</strong> (Rp {{ number_format($maxActualVal, 0, ',', '.') }}), prediksi pada hari itu: <strong>Rp {{ number_format($predictedAtMaxActual, 0, ',', '.') }}</strong>.</li>
                            <li>Puncak prediksi jatuh pada <strong>{{ $maxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($maxPredictedVal, 0, ',', '.') }}</strong>.</li>
                            <li>Model mampu mengikuti pola fluktuasi harian dengan total selisih kumulatif <strong>Rp {{ number_format($totalDiff, 0, ',', '.') }}</strong> dari total aktual <strong>Rp {{ number_format($totalActualSum, 0, ',', '.') }}</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Prediksi (Lebar Penuh) -->
        <div class="col-12">
            <div class="card mb-0 bg-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 border-0 pb-0"><i class="bi bi-table me-2 text-primary-custom"></i>Tabel Hasil Prediksi (Data Testing)</h5>
                            
                            <!-- Rayon Filter -->
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter_rayon_id" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                                <select id="filter_rayon_id" name="rayon_id" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px; width: auto; min-width: 140px;">
                                    <option value="0">Semua Rayon</option>
                                    @foreach($rayons as $rayon)
                                        <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-custom-nowrap" id="predictionTable" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Rayon</th>
                                        <th class="text-end">Realisasi Aktual</th>
                                        <th class="text-end">Hasil Prediksi SVR</th>
                                        <th class="text-end">Nilai Error</th>
                                        <th class="text-end">Persentase Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Analisis Ringkas Tabel Per Rayon -->
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i>Kesimpulan Hasil Prediksi Per Rayon</h6>
                        <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                            @if($bestRayon)
                                <li>Rayon paling akurat: <strong class="text-success">{{ $bestRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format($bestRayon->avg_mape, 2, ',', '.') }}%</strong>.</li>
                            @endif
                            @if($worstRayon)
                                <li>Rayon dengan error terbesar: <strong class="text-danger">{{ $worstRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format($worstRayon->avg_mape, 2, ',', '.') }}%</strong>.</li>
                            @endif
                            <li>Rata-rata selisih prediksi harian: <strong>Rp {{ number_format(abs($avgDailyDeviation), 0, ',', '.') }}</strong> per hari.</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Bawah -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-4">
        <div>
            <button class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold text-sm me-2" onclick="goToStep(3)">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Eksekusi Model
            </button>
            <a href="{{ route('operator.prediksi.index') }}" class="btn btn-border"><i class="bi bi-arrow-clockwise"></i> Refresh Hasil</a>
            
            <form id="resetModelForm" method="POST" action="{{ route('operator.prediksi.reset') }}" class="d-none">
                @csrf
                <input type="hidden" name="id" id="reset_run_id" value="">
            </form>
        </div>
    </div>
@endif
</div>
