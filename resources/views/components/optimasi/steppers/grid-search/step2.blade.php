@props([
    'gsRun',
    'historyGsRuns',
    'bestGsId',
    'readonly' => false,
])

<!-- Grid Step 2: Form -->
<div id="grid-step-content-2" class="step-opt-content d-none">
    <div class="card mb-4 bg-white">
        <form id="gridSearchForm" onsubmit="event.preventDefault(); startGridSearchTuning();">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title border-0 pb-0 mb-0"><i class="bi bi-gear-fill me-2 text-primary-custom"></i>Konfigurasi Parameter Grid Search</h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="auto_develop_grid" onchange="toggleAutoGridDevelop()" @if($gsRun) disabled @endif>
                        <label class="form-check-label small fw-bold text-secondary" for="auto_develop_grid">Otomatis Kembangkan</label>
                    </div>
                </div>
                @if($gsRun)
                    @php
                        $gsMetric = $gsRun->modelMetrics()->where('dataset_type', 'test')->first();
                        $gsParam = $gsRun->modelParameter;
                        $gsMape = $gsMetric ? number_format($gsMetric->mape, 2, ',', '.') : '5,89';
                        $gsC = $gsParam ? $gsParam->c_value : '200';
                        $gsEps = $gsParam ? $gsParam->epsilon_value : '0,001';
                        $gsGam = $gsParam ? $gsParam->gamma_value : '0,01';

                        if (is_numeric($gsC)) {
                            $formatted = number_format((float) $gsC, 6, ',', '.');
                            $gsC = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                        if (is_numeric($gsEps)) {
                            $formatted = number_format((float) $gsEps, 8, ',', '.');
                            $gsEps = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                        if (is_numeric($gsGam)) {
                            $formatted = number_format((float) $gsGam, 6, ',', '.');
                            $gsGam = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                        }
                    @endphp
                    <div class="alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" id="grid-alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 text-info me-3" id="grid-alert-icon"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1" style="font-size: 13.5px;" id="grid-alert-title">Grid Search Telah Dijalankan</h6>
                                <p class="mb-0 text-secondary" style="font-size: 12.5px;" id="grid-alert-desc">
                                    Model aktif saat ini menggunakan parameter optimal: <strong>C = {{ $gsC }}</strong>, <strong>&epsilon; = {{ $gsEps }}</strong>, <strong>&gamma; = {{ $gsGam }}</strong> dengan nilai <strong>MAPE: {{ $gsMape }}%</strong>.
                                </p>
                            </div>
                        </div>
                        @if(!$readonly)
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-1.5 rounded-3" onclick="unlockGridParams()" id="btn-unlock-grid">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Parameter
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-1.5 rounded-3 d-none" onclick="lockGridParams()" id="btn-lock-grid">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Rentang Nilai C (Penalty)</label>
                        <input type="text" name="grid_c" id="grid_c" class="form-control rounded-3" value="[10, 50, 100, 150, 200]" @if($gsRun) disabled @endif>
                        <span class="text-muted" style="font-size: 10px;" id="grid_c_help">5 nilai: 10, 50, 100, 150, 200</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Rentang Epsilon (&epsilon;)</label>
                        <input type="text" name="grid_epsilon" id="grid_epsilon" class="form-control rounded-3" value="[0.001, 0.005, 0.01, 0.05]" @if($gsRun) disabled @endif>
                        <span class="text-muted" style="font-size: 10px;" id="grid_epsilon_help">4 nilai: 0.001, 0.005, 0.01, 0.05</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark">Rentang Gamma (&gamma;)</label>
                        <input type="text" name="grid_gamma" id="grid_gamma" class="form-control rounded-3" value="['scale', 0.001, 0.01, 0.05]" @if($gsRun) disabled @endif>
                        <span class="text-muted" style="font-size: 10px;" id="grid_gamma_help">4 nilai: 'scale', 0.001, 0.01, 0.05</span>
                    </div>
                </div>
                <div class="alert alert-secondary mt-3 p-3 rounded-3 text-sm d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2 fs-5 text-primary"></i>
                    <div id="grid-info-text">Grid Search akan menguji <strong>80 kombinasi</strong> (5&times;4&times;4) parameter menggunakan <strong>5-Fold Cross Validation</strong> (total 400 fits). Metrik evaluasi: <strong>RMSE</strong>.</div>
                </div>
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm" onclick="goToGridStep(1)">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Validasi Dataset
                    </button>
                    @if(!$readonly)
                        <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                            <i class="bi bi-play-fill me-1"></i>Jalankan Grid Search
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Riwayat Optimasi Grid Search -->
    <div class="card bg-white mb-4 shadow-sm border border-light">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary-custom"></i>Riwayat Optimasi Grid Search</h5>
                @if(!$historyGsRuns->isEmpty() && !$readonly)
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-semibold text-xs px-3" onclick="confirmResetOptimasiAll('grid_search')">
                        <i class="bi bi-trash3-fill me-1"></i> Reset Semua Riwayat
                    </button>
                @endif
            </div>
            <x-optimasi.tables.history-table :runs="$historyGsRuns" :bestId="$bestGsId" method="grid_search" :readonly="$readonly" />
        </div>
    </div>
</div>
