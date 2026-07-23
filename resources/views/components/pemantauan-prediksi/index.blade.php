@props([
    'lastRun',
    'bestParams',
    'chartActualValues',
    'chartPredictValues',
    'chartLabels',
    'rayons',
    'rayonId' => 0,
    'dataRoute',      // route for AJAX DataTable (e.g. 'kepala-upt.prediksi.data')
    'resetUrl',       // URL untuk reset filter
    'target' => 'svr_default_upt',
])

<div class="container-fluid p-0">

    <!-- Dropdown Filter Rayon -->
    <div class="card mb-4 bg-white" style="border-radius: 12px; border: 1px solid var(--border);">
        <div class="card-body p-3">
            <form method="GET" action="{{ $resetUrl }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary small fw-semibold mb-1" style="font-size: 11px;">PILIH RAYON PEMANTAUAN</label>
                    <select name="rayon_id" class="form-select rounded-2" style="font-size: 12.5px;" onchange="this.form.submit()">
                        <option value="0">Semua Rayon (Total Gabungan)</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->id }}" {{ request('rayon_id') == $r->id ? 'selected' : '' }}>{{ $r->nama_rayon }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3 py-2" style="font-size: 11.5px;"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="{{ $resetUrl }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 py-2" style="font-size: 11.5px;"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(!$lastRun)
        <div class="card text-center py-5 bg-white border" style="border-radius: 12px;">
            <div class="card-body py-4">
                <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
                <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi</h5>
                <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan di sistem. Hubungi operator untuk melakukan pelatihan model.</p>
            </div>
        </div>
    @else
        {{-- Skeleton Placeholder --}}
        <div class="sk-wrapper">
            <!-- Parameter Cards Skeleton -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid var(--border);">
                <div class="card-body p-3">
                    <span class="skeleton skeleton-text lg" style="width: 250px; margin-bottom: 15px;"></span>
                    <div class="row g-3">
                        @for($i = 0; $i < 4; $i++)
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center border d-flex flex-column align-items-center gap-2">
                                    <span class="skeleton skeleton-text sm" style="width: 60px;"></span>
                                    <span class="skeleton skeleton-text lg" style="width: 90px; margin-bottom: 0;"></span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            
            <!-- Evaluasi cards skeleton -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="skeleton skeleton-text lg" style="width: 200px;"></span>
                <span class="skeleton" style="width: 150px; height: 30px; border-radius: 8px;"></span>
            </div>
            <x-ui.skeleton type="metric" />
            
            <!-- Analysis results skeleton -->
            <div class="card mb-4 bg-white shadow-sm border border-light" style="border-radius: 12px; padding: 16px;">
                <div class="card-body p-0">
                    <span class="skeleton skeleton-text lg" style="width: 300px; margin-bottom: 15px;"></span>
                    <span class="skeleton skeleton-text" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text" style="width: 95%;"></span>
                    <span class="skeleton skeleton-text" style="width: 85%;"></span>
                </div>
            </div>
            
            <!-- Chart & Table Skeletons -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card bg-white" style="border-radius: 12px; padding: 20px 24px;">
                        <span class="skeleton skeleton-text lg" style="width: 200px; margin-bottom: 20px;"></span>
                        <x-ui.skeleton type="chart" height="350px" />
                    </div>
                </div>
                <div class="col-12">
                    <x-ui.skeleton type="table" :rows="6" />
                </div>
            </div>
        </div>

        {{-- Real Content --}}
        <div class="sk-content">
        @php
            $mapeVal = 100.0; $r2Val = 0.0; $rmseVal = 0.0; $maeVal = 0.0; $meanActual = 1513770;
            $metricObj = $lastRun->modelMetrics()->where('dataset_type', 'test')->first();
            if ($metricObj) {
                $mapeVal = (float)$metricObj->mape;
                $r2Val   = (float)$metricObj->r2_score;
                $rmseVal = (float)$metricObj->rmse;
                $maeVal  = (float)$metricObj->mae;
                $meanActual = $lastRun->predictionResults()->avg('actual_value') ?? $meanActual;
            }

            $totalActualSum     = array_sum($chartActualValues);
            $totalPredictedSum  = array_sum($chartPredictValues);
            $totalDiff          = abs($totalActualSum - $totalPredictedSum);
            $totalDiffPercent   = $totalActualSum > 0 ? ($totalDiff / $totalActualSum) * 100 : 0;

            $maxActualDate = '-'; $maxActualVal = 0; $maxPredictedDate = '-';
            $maxPredictedVal = 0; $predictedAtMaxActual = 0;
            $actualAtMaxPredicted = 0; $maxActualAccuracy = 0;

            if (count($chartActualValues) > 0) {
                $maxActualIdx = array_search(max($chartActualValues), $chartActualValues);
                $maxActualDate = $chartLabels[$maxActualIdx] ?? '-';
                $maxActualVal  = $chartActualValues[$maxActualIdx];
                $predictedAtMaxActual = $chartPredictValues[$maxActualIdx] ?? 0;
                $maxActualAccuracy = $maxActualVal > 0 ? (1 - abs($maxActualVal - $predictedAtMaxActual) / $maxActualVal) * 100 : 0;
                $maxPredictedIdx = array_search(max($chartPredictValues), $chartPredictValues);
                $maxPredictedDate = $chartLabels[$maxPredictedIdx] ?? '-';
                $maxPredictedVal  = $chartPredictValues[$maxPredictedIdx];
                $actualAtMaxPredicted = $chartActualValues[$maxPredictedIdx] ?? 0;
            }
        @endphp

        <!-- Parameter Model Cards -->
        <div class="card mb-4" style="border-radius: 12px; border: 1px solid var(--border);">
            <div class="card-body">
                <h5 class="card-title border-0 pb-0 mb-3" style="font-size: 14px;"><i class="bi bi-gear-fill me-2 text-primary"></i>Konfigurasi Parameter Model Aktif</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">C (Penalti)</span>
                            <div class="fw-bold text-dark fs-5">{{ $bestParams['c'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Epsilon (&epsilon;)</span>
                            <div class="fw-bold text-dark fs-5">{{ $bestParams['epsilon'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Gamma (&gamma;)</span>
                            <div class="fw-bold text-dark fs-5">{{ $bestParams['gamma'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-dark rounded-3 text-center text-white h-100 d-flex flex-column justify-content-center">
                            <span class="text-uppercase text-white-50 fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Metode Aktif</span>
                            <div class="fw-bold fs-6">{{ $bestParams['metode_terbaik'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluasi Model Cards -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;"><i class="bi bi-award-fill me-2 text-primary"></i>Hasil Evaluasi Tingkat Akurasi</h5>
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 11.5px; padding: 4px 10px;">
                <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
            </button>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border-radius: 12px; padding: 12px;">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">MAE (Selisih Uang Rata-Rata)</span>
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 18px;">Rp {{ number_format($maeVal, 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-radius: 12px; padding: 12px;">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">RMSE (Penyimpangan Kuadrat)</span>
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 18px;">Rp {{ number_format($rmseVal, 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-radius: 12px; padding: 12px;">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">MAPE (Tingkat Kesalahan %)</span>
                    <h5 class="fw-bold mb-0 text-success" style="font-size: 18px;">{{ number_format($mapeVal, 2, ',', '.') }}%</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-radius: 12px; padding: 12px;">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">R² Score (Kesesuaian Pola)</span>
                    <h5 class="fw-bold mb-0 text-primary" style="font-size: 18px;">{{ number_format($r2Val, 4, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <!-- Analisis & Rekomendasi -->
        <div class="card mb-4 bg-white shadow-sm border border-light" style="border-radius: 12px; padding: 16px;">
            <div class="card-body p-0">
                <h5 class="card-title text-dark mb-3" style="font-size: 14px;"><i class="bi bi-chat-left-text-fill me-2 text-primary"></i>Analisis Kinerja & Rekomendasi Model</h5>
                <x-model-analysis-results
                    :mape="$mapeVal"
                    :r2="$r2Val"
                    :rmse="$rmseVal"
                    :mae="$maeVal"
                    :meanActual="$meanActual"
                    target="{{ $target }}"
                    recTitle="Kesimpulan & Rekomendasi"
                />
            </div>
        </div>

        <!-- Grafik & Tabel Section -->
        <div class="row g-4 mb-4">
            <!-- Chart -->
            <div class="col-12">
                <div class="card mb-0 bg-white" style="border-radius: 12px; padding: 20px 24px;">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="card-title mb-0" style="font-size: 14px;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Grafik Aktual vs Prediksi Model SVR</h5>
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
                        <div style="height: 350px; position: relative; width: 100%;">
                            <canvas id="svrChart"></canvas>
                        </div>

                        <!-- Analisis Grafik -->
                        <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 12.5px;"><i class="bi bi-info-circle-fill text-primary me-1"></i>Analisis Pola & Kesesuaian Tren Grafik</h6>
                            <div class="row g-3 mt-1 text-sm text-secondary" style="font-size: 12px;">
                                <div class="col-md-6 border-end border-light-subtle">
                                    <div class="mb-2">
                                        <i class="bi bi-arrow-repeat text-primary me-1"></i>
                                        <strong>Kesesuaian Pola:</strong>
                                        @if($totalDiffPercent < 10)
                                            Sangat Cocok (Selisih akumulasi total hanya <strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong>). Pola prediksi mengikuti transaksi riil harian secara presisi.
                                        @else
                                            Selisih Sedang (Selisih akumulasi total <strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong>). Pola peramalan mengikuti fluktuasi harian secara memadai.
                                        @endif
                                    </div>
                                    <div>
                                        <i class="bi bi-calendar-check text-primary me-1"></i>
                                        <strong>Puncak Realisasi (Aktual):</strong>
                                        Pada <strong>{{ $maxActualDate }}</strong> sebesar <strong>Rp {{ number_format($maxActualVal, 0, ',', '.') }}</strong>, model memprediksi <strong>Rp {{ number_format($predictedAtMaxActual, 0, ',', '.') }}</strong> (ketepatan <strong>{{ number_format($maxActualAccuracy, 2, ',', '.') }}%</strong>).
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <i class="bi bi-graph-up-arrow text-warning me-1"></i>
                                        <strong>Puncak Peramalan (Prediksi):</strong>
                                        Diproyeksikan pada <strong>{{ $maxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($maxPredictedVal, 0, ',', '.') }}</strong> (Realisasi lapangan: <strong>Rp {{ number_format($actualAtMaxPredicted, 0, ',', '.') }}</strong>).
                                    </div>
                                    <div>
                                        <i class="bi bi-lightning-fill text-warning me-1"></i>
                                        <strong>Penyimpangan Kumulatif:</strong>
                                        Total deviasi transaksi kumulatif aktual vs prediksi adalah <strong>Rp {{ number_format($totalDiff, 0, ',', '.') }}</strong> (atau <strong>{{ number_format($totalDiffPercent, 2, ',', '.') }}%</strong> dari total pendapatan periode testing <strong>Rp {{ number_format($totalActualSum, 0, ',', '.') }}</strong>).
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Data Prediksi -->
            <div class="col-12">
                <div class="card mb-0 bg-white" style="border-radius: 12px; padding: 20px 24px;">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0" style="font-size: 14px;"><i class="bi bi-table me-2 text-primary"></i>Tabel Hasil Prediksi (Data Testing)</h5>
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
                                <thead>
                                    <tr class="table-light">
                                        <th style="width: 50px;">No</th>
                                        <th>Tanggal</th>
                                        <th>Rayon</th>
                                        <th style="text-align: right;">Pendapatan Riil (Aktual)</th>
                                        <th style="text-align: right;">Hasil Perkiraan (Prediksi)</th>
                                        <th style="text-align: right;">Selisih Nominal (Deviasi)</th>
                                        <th style="text-align: right;">Tingkat Kesalahan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- closes sk-content -->
    @endif
</div>

@if($lastRun)
@php
    $allSvrPredictions = $lastRun->predictionResults()->orderBy('tanggal', 'asc')->get();
    $allSvrMapped = $allSvrPredictions->map(fn($p) => [
        'tanggal'         => \Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
        'rayon_id'        => (int)$p->rayon_id,
        'actual_value'    => (double)$p->actual_value,
        'predicted_value' => (double)$p->predicted_value
    ])->toArray();
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allSvrPreds = @json($allSvrMapped);
        const dataRoute   = @json($dataRoute);

        function getFilteredData(preds, rayonId) {
            rayonId = parseInt(rayonId);
            if (rayonId === 0) {
                const grouped = {};
                preds.forEach(p => {
                    if (!grouped[p.tanggal]) grouped[p.tanggal] = { actual: 0, predicted: 0 };
                    grouped[p.tanggal].actual += p.actual_value;
                    grouped[p.tanggal].predicted += p.predicted_value;
                });
                const labels = Object.keys(grouped);
                return { labels, actual: labels.map(l => grouped[l].actual), predicted: labels.map(l => grouped[l].predicted) };
            } else {
                const filtered = preds.filter(p => p.rayon_id === rayonId);
                return { labels: filtered.map(p => p.tanggal), actual: filtered.map(p => p.actual_value), predicted: filtered.map(p => p.predicted_value) };
            }
        }

        window.updateSvrChart = function(rayonId) {
            const data = getFilteredData(allSvrPreds, rayonId);
            if (window.svrChartInstance) {
                window.svrChartInstance.data.labels = data.labels;
                const dsActual = window.svrChartInstance.data.datasets.find(ds => ds.label === 'Realisasi Pendapatan (Aktual)');
                if (dsActual) dsActual.data = data.actual;
                const dsPredict = window.svrChartInstance.data.datasets.find(ds => ds.label === 'Hasil Perkiraan (Prediksi SVR)');
                if (dsPredict) dsPredict.data = data.predicted;
                window.svrChartInstance.update();
            }
        };

        const ctx = document.getElementById('svrChart').getContext('2d');
        const gradientActual = ctx.createLinearGradient(0, 0, 0, 320);
        gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
        gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');
        const gradientPredict = ctx.createLinearGradient(0, 0, 0, 320);
        gradientPredict.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
        gradientPredict.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

        const startData = getFilteredData(allSvrPreds, $('#filter_rayon_id').val() || 0);

        window.svrChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: startData.labels,
                datasets: [
                    { label: 'Realisasi Pendapatan (Aktual)', data: startData.actual, borderColor: '#005BAA', borderWidth: 2, backgroundColor: gradientActual, fill: true, tension: 0.3, pointBackgroundColor: '#005BAA', pointBorderColor: '#ffffff', pointBorderWidth: 1, pointRadius: 2.5 },
                    { label: 'Hasil Perkiraan (Prediksi SVR)', data: startData.predicted, borderColor: '#F4C542', borderWidth: 2, backgroundColor: gradientPredict, fill: true, tension: 0.3, pointBackgroundColor: '#F4C542', pointBorderColor: '#ffffff', pointBorderWidth: 1, pointRadius: 2.5, borderDash: [4, 4] }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { family: 'Inter', size: 11, weight: '500' } } },
                    tooltip: { padding: 8, backgroundColor: '#1f2937', titleFont: { family: 'Inter', size: 11, weight: 'bold' }, bodyFont: { family: 'Inter', size: 11 }, callbacks: { label: function(c) { return ' ' + c.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(c.raw); } } }
                },
                scales: {
                    y: { grid: { borderDash: [5, 5], color: '#e2e8f0' }, ticks: { callback: function(v) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v); }, font: { family: 'Inter', size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 10 } } }
                }
            }
        });

        const predTable = $('#predictionTable').DataTable({
            processing: true,
            ajax: { url: dataRoute },
            columns: [
                { data: null, render: function(d, t, r, meta) { return meta.row + 1; } },
                { 
                    data: 'tanggal', 
                    render: function(d, type) { 
                        if (type === 'display' || type === 'filter') { 
                            if (!d) return '-';
                            const p = d.split('-'); 
                            if (p.length === 3) {
                                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                return parseInt(p[2], 10) + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0];
                            }
                            return d;
                        } 
                        return d; 
                    } 
                },
                { data: 'rayon_name', render: function(d) { return `<span class="badge bg-light text-dark border">${d}</span>`; } },
                { data: 'actual_value', className: 'text-end fw-semibold', render: function(d) { return 'Rp ' + parseInt(d).toLocaleString('id-ID'); } },
                { data: 'predicted_value', className: 'text-end fw-bold text-primary-custom', render: function(d) { return 'Rp ' + parseInt(d).toLocaleString('id-ID'); } },
                { data: null, className: 'text-end text-danger', render: function(d, t, row) { return 'Rp ' + Math.abs(row.actual_value - row.predicted_value).toLocaleString('id-ID'); } },
                { data: 'percentage_error', className: 'text-end fw-semibold', render: function(d) { return parseFloat(d).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%'; } }
            ],
            columnDefs: [
                { orderable: false, targets: [0] }
            ],
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            order: [[1, 'asc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });

        // Set consecutive numbering on table draw
        predTable.on('draw.dt', function () {
            const info = predTable.page.info();
            predTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = info.start + i + 1;
            });
        });

        $('#filter_rayon_id').on('change', function() {
            const val = $(this).val();
            const text = val > 0 ? $(this).find('option:selected').text() : '';
            predTable.column(2).search(text ? '^' + text + '$' : '', true, false).draw();
            
            if (typeof window.updateSvrChart === 'function') window.updateSvrChart(val);
        });
    });
</script>
@endif
