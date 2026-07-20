@props([
    'historyRuns',
    'lastRun'
])

<!-- STEP 2 CONTENT -->
<div id="step-content-2" class="step-content-section d-none">
    <div class="row g-4 mb-4">
        <!-- Konfigurasi SVR Standar -->
        <div class="col-12">
            <div class="card mb-0 bg-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-sliders me-2 text-primary-custom"></i>Konfigurasi SVR Standar</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 12.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Parameter</th>
                                    <th>Nilai</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">Kernel</td>
                                    <td><code class="text-primary fw-bold bg-light px-2 py-1 rounded">RBF</code></td>
                                    <td>Kernel Radial Basis Function untuk menangani pola non-linear</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">C</td>
                                    <td><code class="text-primary fw-bold bg-light px-2 py-1 rounded">1.0</code></td>
                                    <td>Penalti terhadap kesalahan prediksi (regularisasi)</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Epsilon (&epsilon;)</td>
                                    <td><code class="text-primary fw-bold bg-light px-2 py-1 rounded">0.1</code></td>
                                    <td>Batas lebar tabung toleransi kesalahan prediksi</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Gamma (&gamma;)</td>
                                    <td><code class="text-primary fw-bold bg-light px-2 py-1 rounded">scale</code></td>
                                    <td>Parameter koefisien kernel default Scikit-learn</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 p-2 bg-light rounded text-muted small border-start border-4 border-warning px-3 py-2">
                        <i class="bi bi-info-circle-fill me-1 text-warning"></i>
                        Konfigurasi parameter ini digunakan sebagai <strong>model awal / default</strong> sebelum dilakukan optimasi hyperparameter pada menu <strong>Optimasi Parameter</strong>.
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pelatihan SVR Standar -->
        <div class="col-12">
            <div class="card bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history text-primary-custom me-2"></i>Riwayat Pelatihan SVR Standar</h5>
                        @if(!$historyRuns->isEmpty())
                            <button class="btn btn-outline-danger btn-sm rounded-3 fw-semibold text-xs px-3" onclick="confirmResetAll()">
                                <i class="bi bi-trash3-fill me-1"></i> Reset Semua Riwayat
                            </button>
                        @endif
                    </div>

                    @if($historyRuns->isEmpty())
                        <div class="text-center py-4 text-secondary">
                            <i class="bi bi-folder2-open fs-2 text-muted mb-2 d-block"></i>
                            Belum ada riwayat proses pelatihan model SVR standar.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Waktu Mulai</th>
                                        <th>Parameter (C, &epsilon;, &gamma;)</th>
                                        <th>MAE</th>
                                        <th>RMSE</th>
                                        <th>MAPE</th>
                                        <th>R² Score</th>
                                        <th>Lama Proses</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historyRuns as $run)
                                        @php
                                            $param = $run->modelParameter;
                                            $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                                            $isActive = ($lastRun && $run->id === $lastRun->id);

                                            $cVal = '-';
                                            if ($param) {
                                                $cVal = $param->c_value;
                                                if (is_numeric($cVal)) {
                                                    $formatted = number_format((float)$cVal, 6, ',', '.');
                                                    $cVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                                }
                                            }
                                            $epsVal = '-';
                                            if ($param) {
                                                $epsVal = $param->epsilon_value;
                                                if (is_numeric($epsVal)) {
                                                    $formatted = number_format((float)$epsVal, 8, ',', '.');
                                                    $epsVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                                }
                                            }
                                            $gamVal = '-';
                                            if ($param) {
                                                $gamVal = $param->gamma_value;
                                                if (is_numeric($gamVal)) {
                                                    $formatted = number_format((float)$gamVal, 6, ',', '.');
                                                    $gamVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                                }
                                            }

                                            $maeVal = $metric ? 'Rp ' . number_format($metric->mae, 0, ',', '.') : '-';
                                            $rmseVal = $metric ? 'Rp ' . number_format($metric->rmse, 0, ',', '.') : '-';
                                            $mapeVal = $metric ? number_format($metric->mape, 2, ',', '.') . '%' : '-';
                                            $r2Val = $metric ? number_format($metric->r2_score, 2, ',', '.') : '-';

                                            $start = $run->started_at ? \Carbon\Carbon::parse($run->started_at) : null;
                                            $end = $run->finished_at ? \Carbon\Carbon::parse($run->finished_at) : null;
                                            $durasi = '-';
                                            if ($start && $end) {
                                                $diffSecs = $start->diffInSeconds($end);
                                                if ($diffSecs >= 60) {
                                                    $mins = floor($diffSecs / 60);
                                                    $secs = $diffSecs % 60;
                                                    $durasi = $mins . ' m ' . $secs . ' s';
                                                } elseif ($diffSecs > 0) {
                                                    $durasi = $diffSecs . ' detik';
                                                } else {
                                                    $diffMs = $start->diffInMilliseconds($end);
                                                    $durasi = $diffMs . ' ms';
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="ps-3">{{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                                            <td>
                                                <span class="badge bg-light text-dark border">C: {{ $cVal }}</span>
                                                <span class="badge bg-light text-dark border">&epsilon;: {{ $epsVal }}</span>
                                                <span class="badge bg-light text-dark border">&gamma;: {{ $gamVal }}</span>
                                            </td>
                                            <td>{{ $maeVal }}</td>
                                            <td>{{ $rmseVal }}</td>
                                            <td class="fw-bold text-success">{{ $mapeVal }}</td>
                                            <td>{{ $r2Val }}</td>
                                            <td class="fw-semibold text-secondary">{{ $durasi }}</td>
                                            <td class="text-center">
                                                @if($isActive)
                                                    <span class="badge bg-success text-white rounded-3 px-2 py-1" style="font-size: 11px;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white rounded-3 px-2 py-1" style="font-size: 11px;">
                                                        <i class="bi bi-clock-history me-1"></i>Riwayat
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-link text-danger p-0 border-0" onclick="confirmDeleteRun({{ $run->id }}, '{{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}')" title="Hapus Riwayat">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Footer for Step 2 -->
    <div class="d-flex justify-content-between mb-4">
        <button class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-semibold" onclick="goToStep(1)">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Validasi Dataset
        </button>
        <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-semibold shadow-sm" onclick="goToStep(3)">
            Lanjut ke Generate Prediksi <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
