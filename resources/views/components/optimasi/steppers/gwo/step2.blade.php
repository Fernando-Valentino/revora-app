@props([
    'gwoRun',
    'historyGwoRuns',
    'bestGwoId',
    'readonly' => false,
])

<!-- GWO Step 2: Form -->
<div id="gwo-step-content-2" class="step-opt-content d-none">
    <div class="card mb-4 bg-white">
        <form id="gwoSearchForm" onsubmit="event.preventDefault(); startGwoTuning();">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title border-0 pb-0 mb-0"><i class="bi bi-activity me-2 text-primary-custom"></i>Konfigurasi GWO (Grey Wolf Optimizer)</h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="auto_develop_gwo" onchange="toggleAutoGwoDevelop()" @if($gwoRun) disabled @endif>
                        <label class="form-check-label small fw-bold text-secondary" for="auto_develop_gwo">Otomatis Kembangkan</label>
                    </div>
                </div>
                @if($gwoRun)
                    @php
                        $gwoMetric = $gwoRun->modelMetrics()->where('dataset_type', 'test')->first();
                        $gwoParam = $gwoRun->modelParameter;
                        $gwoMape = $gwoMetric ? number_format($gwoMetric->mape, 2, ',', '.') : '4,74';
                        $gwoC = $gwoParam ? $gwoParam->c_value : '250,0345';
                        $gwoEps = $gwoParam ? $gwoParam->epsilon_value : '0,0053';
                        $gwoGam = $gwoParam ? $gwoParam->gamma_value : '0,0044';

                        if (is_numeric($gwoC)) {
                            $formatted = number_format((float) $gwoC, 6, ',', '.');
                            $gwoC = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                        if (is_numeric($gwoEps)) {
                            $formatted = number_format((float) $gwoEps, 8, ',', '.');
                            $gwoEps = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                        if (is_numeric($gwoGam)) {
                            $formatted = number_format((float) $gwoGam, 6, ',', '.');
                            $gwoGam = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                    @endphp
                    <div class="alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" id="gwo-alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 text-info me-3" id="gwo-alert-icon"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1" style="font-size: 13.5px;" id="gwo-alert-title">GWO Telah Dijalankan</h6>
                                <p class="mb-0 text-secondary" style="font-size: 12.5px;" id="gwo-alert-desc">
                                    Model aktif saat ini menggunakan parameter optimal: <strong>C = {{ $gwoC }}</strong>, <strong>&epsilon; = {{ $gwoEps }}</strong>, <strong>&gamma; = {{ $gwoGam }}</strong> dengan nilai <strong>MAPE: {{ $gwoMape }}%</strong>.
                                </p>
                            </div>
                        </div>
                        @if(!$readonly)
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-1.5 rounded-3" onclick="unlockGwoParams()" id="btn-unlock-gwo">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Parameter
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-1.5 rounded-3 d-none" onclick="lockGwoParams()" id="btn-lock-gwo">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Jumlah Wolf (Populasi Agen)</label>
                        <input type="number" name="gwo_wolves" id="gwo_wolves" class="form-control rounded-3" value="10" min="5" max="50" @if($gwoRun) disabled @endif>
                        <span class="text-muted" style="font-size: 10px;">Jumlah agen serigala (populasi). Rekomendasi: 10 s.d 20</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Jumlah Maksimum Iterasi</label>
                        <input type="number" name="gwo_iterations" id="gwo_iterations" class="form-control rounded-3" value="30" min="10" max="100" @if($gwoRun) disabled @endif>
                        <span class="text-muted" style="font-size: 10px;">Jumlah iterasi perulangan. Rekomendasi: 30 s.d 50</span>
                    </div>
                </div>

                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-arrows-expand me-1"></i>Search Space Range (Batas Parameter SVR)</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card p-3 bg-light border-0">
                            <label class="form-label small fw-bold text-dark mb-2">Parameter C (Penalty)</label>
                            <div class="d-flex gap-2">
                                <input type="number" step="0.01" name="c_min" id="c_min" class="form-control rounded-3" value="1.0" @if($gwoRun) disabled @endif placeholder="Min">
                                <input type="number" step="0.01" name="c_max" id="c_max" class="form-control rounded-3" value="500.0" @if($gwoRun) disabled @endif placeholder="Max">
                            </div>
                            <span class="text-muted mt-1" style="font-size: 10px;">Range C: [1.0 s.d 500.0]</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-light border-0">
                            <label class="form-label small fw-bold text-dark mb-2">Parameter Epsilon (&epsilon;)</label>
                            <div class="d-flex gap-2">
                                <input type="number" step="0.0001" name="epsilon_min" id="epsilon_min" class="form-control rounded-3" value="0.0001" @if($gwoRun) disabled @endif placeholder="Min">
                                <input type="number" step="0.0001" name="epsilon_max" id="epsilon_max" class="form-control rounded-3" value="0.1" @if($gwoRun) disabled @endif placeholder="Max">
                            </div>
                            <span class="text-muted mt-1" style="font-size: 10px;">Range &epsilon;: [0.0001 s.d 0.1]</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-light border-0">
                            <label class="form-label small fw-bold text-dark mb-2">Parameter Gamma (&gamma;)</label>
                            <div class="d-flex gap-2">
                                <input type="number" step="0.0001" name="gamma_min" id="gamma_min" class="form-control rounded-3" value="0.0001" @if($gwoRun) disabled @endif placeholder="Min">
                                <input type="number" step="0.0001" name="gamma_max" id="gamma_max" class="form-control rounded-3" value="0.1" @if($gwoRun) disabled @endif placeholder="Max">
                            </div>
                            <span class="text-muted mt-1" style="font-size: 10px;">Range &gamma;: [0.0001 s.d 0.1]</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm" onclick="goToGwoStep(1)">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Validasi Dataset
                    </button>
                    @if(!$readonly)
                        <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                            <i class="bi bi-play-fill me-1"></i>Jalankan Grey Wolf Optimizer (GWO)
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Riwayat Optimasi GWO -->
    <div class="card bg-white mb-4 shadow-sm border border-light">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary-custom"></i>Riwayat Optimasi GWO</h5>
                @if(!$historyGwoRuns->isEmpty() && !$readonly)
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-semibold text-xs px-3" onclick="confirmResetOptimasiAll('gwo')">
                        <i class="bi bi-trash3-fill me-1"></i> Reset Semua Riwayat
                    </button>
                @endif
            </div>
            <x-optimasi.tables.history-table :runs="$historyGwoRuns" :bestId="$bestGwoId" method="gwo" :readonly="$readonly" />
        </div>
    </div>
</div>
