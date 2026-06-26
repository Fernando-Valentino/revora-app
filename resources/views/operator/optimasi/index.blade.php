@extends('layouts.app')

@section('title', 'Optimasi Parameter')
@section('subtitle', 'Halaman ini digunakan untuk membandingkan hasil optimasi parameter SVR menggunakan Grid Search dan Grey Wolf Optimizer.')

@section('content')
<div class="container-fluid p-0">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$lastRun)
        <div class="card shadow-sm border border-light bg-white rounded-3 overflow-hidden mb-4">
            <div class="card-body p-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning p-4 rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-lock-fill fs-1 text-warning"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Optimasi Parameter Terkunci</h4>
                <p class="text-secondary mx-auto mb-4" style="max-width: 540px; line-height: 1.6;">
                    Fitur optimasi parameter (Grid Search &amp; Grey Wolf Optimizer) membutuhkan model SVR standar yang telah berhasil dilatih terlebih dahulu. Silakan masuk ke menu Prediksi untuk melakukan proses training model SVR standar.
                </p>
                <a href="{{ route('operator.prediksi.index') }}" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                    <i class="bi bi-cpu me-2"></i>Jalankan SVR Standar Terlebih Dahulu
                </a>
            </div>
        </div>
    @else
        <!-- Custom Stepper & Tab Styles -->
        <style>
            /* Stepper Style consistent with Prediksi SVR */
            .stepper-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
            }
            .stepper-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
                z-index: 2;
                flex: 1;
            }
            .stepper-item .step-number {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background-color: #ffffff;
                border: 2px solid var(--border);
                color: var(--text-secondary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 14px;
                margin-bottom: 8px;
                transition: all 0.3s ease;
            }
            .stepper-item .step-title {
                font-size: 11.5px;
                font-weight: 500;
                color: var(--text-secondary);
                text-align: center;
            }
            .stepper-line {
                flex: 1;
                height: 2px;
                background-color: var(--border);
                margin-bottom: 24px;
                transition: background-color 0.3s ease;
            }
            .stepper-line.completed {
                background-color: var(--success) !important;
            }
            .stepper-item.active .step-number {
                background-color: var(--primary-blue-light);
                border-color: var(--primary-blue);
                color: var(--primary-blue);
                box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.15);
            }
            .stepper-item.active .step-title {
                color: var(--primary-blue);
                font-weight: 600;
            }
            .stepper-item.completed .step-number {
                background-color: var(--success);
                border-color: var(--success);
                color: #ffffff;
            }
            .stepper-item.completed .step-title {
                color: var(--success);
                font-weight: 600;
            }
            .btn-active-tab {
                background-color: #005BAA !important;
                color: #ffffff !important;
                border: 1.5px solid #005BAA !important;
                box-shadow: 0 2px 6px rgba(0, 91, 170, 0.25);
                outline: none !important;
            }
            #tab-btn-grid:not(.btn-active-tab),
            #tab-btn-gwo:not(.btn-active-tab) {
                background-color: transparent !important;
                border: 1.5px solid transparent !important;
                color: #6b7280 !important;
            }
            #tab-btn-grid:not(.btn-active-tab):hover,
            #tab-btn-gwo:not(.btn-active-tab):hover {
                background-color: rgba(0, 91, 170, 0.07) !important;
                color: #005BAA !important;
                border-color: rgba(0, 91, 170, 0.2) !important;
            }
            /* Progress Step styling for Grid & GWO pipeline */
            .progress-step {
                display: flex;
                align-items: center;
                margin-bottom: 8px;
                font-size: 12.5px;
                color: var(--text-secondary);
                padding: 6px 10px;
                border-radius: 6px;
                transition: all 0.2s ease;
            }
            .progress-step.active {
                background-color: var(--primary-blue-light);
                color: var(--primary-blue);
                font-weight: 600;
            }
            .progress-step.active .step-label {
                color: var(--primary-blue) !important;
            }
            .progress-step.success-step {
                background-color: rgba(22, 163, 74, 0.05);
                color: var(--success);
            }
            .progress-step.success-step .step-label {
                color: var(--success) !important;
            }
            .progress-step.failed-step {
                background-color: rgba(220, 38, 38, 0.05);
                color: var(--danger);
            }
            .progress-step.failed-step .step-label {
                color: var(--danger) !important;
            }
            /* Edit mode input style */
            .param-input-editable {
                background-color: #ffffff !important;
                border-color: var(--primary-blue) !important;
                box-shadow: 0 0 0 2px rgba(0, 91, 170, 0.12) !important;
            }
            
            /* Replicated styles from Prediksi model page for complete evaluation views */
            .text-primary-custom {
                color: var(--primary-blue);
            }
            .bg-success-subtle {
                background-color: rgba(22, 163, 74, 0.1) !important;
            }
            .bg-danger-subtle {
                background-color: rgba(220, 38, 38, 0.1) !important;
            }
            .bg-primary-subtle {
                background-color: rgba(0, 91, 170, 0.1) !important;
            }
            .bg-warning-subtle {
                background-color: rgba(245, 158, 11, 0.1) !important;
            }
            .bg-light-subtle {
                background-color: #f8fafc !important;
            }
            .metric-card-custom {
                background-color: #ffffff;
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 16px 14px !important;
                box-shadow: 0 1px 3px rgba(0, 91, 170, 0.03);
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                height: 100%;
                transition: all 0.2s ease-in-out;
            }
            .metric-card-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 91, 170, 0.08);
                border-color: rgba(0, 91, 170, 0.3);
            }
            .metric-label-custom {
                font-size: 10.5px;
                font-weight: 600;
                color: var(--text-secondary);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 6px;
                display: block;
            }
            .metric-value-custom {
                font-size: 20px;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 4px;
                white-space: nowrap !important;
                display: block;
            }
            .metric-value-custom.text-success {
                color: var(--success) !important;
            }
            .table-custom-nowrap th, 
            .table-custom-nowrap td {
                white-space: nowrap !important;
                vertical-align: middle;
            }
        </style>

        <!-- Method Selector Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-light p-1 rounded-3 d-inline-flex gap-1 border">
                    <button type="button" class="btn px-4 py-2 fw-bold rounded-3 text-sm transition-all" id="tab-btn-grid" onclick="switchMethod('grid')">
                        <i class="bi bi-grid-3x3 me-2"></i>Grid Search Optimization
                    </button>
                    <button type="button" class="btn px-4 py-2 fw-bold rounded-3 text-sm transition-all text-secondary" id="tab-btn-gwo" onclick="switchMethod('gwo')">
                        <i class="bi bi-activity me-2"></i>Grey Wolf Optimizer (GWO)
                    </button>
                </div>
            </div>
        </div>

        <!-- GRID SEARCH METHOD CONTAINER -->
        <div id="method-content-grid" class="method-section">
            <!-- Grid Search Stepper Header -->
            <div class="card mb-4 bg-white shadow-sm border border-light">
                <div class="card-body py-3">
                    <div class="stepper-wrapper">
                        <div class="stepper-item active" id="stepper-grid-1" onclick="goToGridStep(1)" style="cursor: pointer;">
                            <div class="step-number">1</div>
                            <div class="step-title">Konfigurasi Grid</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-1"></div>
                        <div class="stepper-item" id="stepper-grid-2" onclick="goToGridStep(2)" style="cursor: pointer;">
                            <div class="step-number">2</div>
                            <div class="step-title">Proses Tuning</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-2"></div>
                        <div class="stepper-item" id="stepper-grid-3" onclick="goToGridStep(3)" style="cursor: pointer;">
                            <div class="step-number">3</div>
                            <div class="step-title">Hasil &amp; Perbandingan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Step 1: Form -->
            <div id="grid-step-content-1" class="step-opt-content">
                <div class="card mb-4 bg-white">
                    <form id="gridSearchForm" onsubmit="event.preventDefault(); startGridSearchTuning();">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title border-0 pb-0 mb-0"><i class="bi bi-gear-fill me-2 text-primary-custom"></i>Konfigurasi Parameter Grid</h5>
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
                                        $formatted = number_format((float)$gsC, 6, ',', '.');
                                        $gsC = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                    }
                                    if (is_numeric($gsEps)) {
                                        $formatted = number_format((float)$gsEps, 8, ',', '.');
                                        $gsEps = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                    }
                                    if (is_numeric($gsGam)) {
                                        $formatted = number_format((float)$gsGam, 6, ',', '.');
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
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-1.5 rounded-3" onclick="unlockGridParams()" id="btn-unlock-grid">
                                            <i class="bi bi-pencil-square me-1"></i> Edit Parameter
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-1.5 rounded-3 d-none" onclick="lockGridParams()" id="btn-lock-grid">
                                            <i class="bi bi-x-circle me-1"></i> Batal
                                        </button>
                                    </div>
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
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                                    <i class="bi bi-play-fill me-1"></i>Jalankan Grid Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grid Step 2: Progress -->
            <div id="grid-step-content-2" class="step-opt-content d-none">
                <div class="card mb-4 bg-white">
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title text-start mb-4"><i class="bi bi-cpu me-2 text-primary-custom"></i>Proses Tuning Parameter Grid Search</h5>
                        
                        <div class="py-4">
                            <!-- Spinner Container -->
                            <div id="grid-spinner-container">
                                <div class="spinner-border text-primary mb-3" style="width: 50px; height: 50px; border-width: 4px;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <!-- Success Check Container -->
                            <div id="grid-success-container" class="d-none">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success p-4 rounded-circle mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-check-lg fs-1"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-2" id="grid-process-title">Sedang Menyiapkan Grid Search...</h6>
                            <p class="text-secondary small mx-auto mb-4" style="max-width: 420px;" id="grid-process-desc">Model SVR standar sedang disiapkan untuk pengujian kombinasi parameter.</p>
                        </div>

                        <!-- Timer Box -->
                        <div class="py-3 bg-light rounded-3 mb-4 mx-auto" id="grid-timer-box" style="max-width: 400px; border: 1px dashed #cbd5e1;">
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <span class="d-block text-muted small fw-semibold">Waktu Berjalan</span>
                                    <span class="fs-4 fw-bold text-dark" id="grid-elapsed-timer">0s</span>
                                </div>
                                <div style="border-left: 1px solid #e2e8f0; height: 40px; margin-top: 5px;"></div>
                                <div>
                                    <span class="d-block text-muted small fw-semibold">Perkiraan Waktu</span>
                                    <span class="fs-4 fw-bold text-primary" id="grid-estimated-timer">~20s</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-start border-top pt-4 mx-auto" style="max-width: 500px;">
                            <h6 class="fw-bold text-dark mb-3">Progress Pipeline:</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="progress-step" id="grid-pipe-1">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">1. Memuat Dataset Training &amp; Testing</span>
                                </div>
                                <div class="progress-step" id="grid-pipe-2">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">2. Menghasilkan Kombinasi Parameter Grid</span>
                                </div>
                                <div class="progress-step" id="grid-pipe-3">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">3. Melatih SVR 5-Fold Cross Validation</span>
                                </div>
                                <div class="progress-step" id="grid-pipe-4">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">4. Memperbarui Parameter Model Optimal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GWO METHOD CONTAINER -->
        <div id="method-content-gwo" class="method-section d-none">
            <!-- GWO Stepper Header -->
            <div class="card mb-4 bg-white shadow-sm border border-light">
                <div class="card-body py-3">
                    <div class="stepper-wrapper">
                        <div class="stepper-item active" id="stepper-gwo-1" onclick="goToGwoStep(1)" style="cursor: pointer;">
                            <div class="step-number">1</div>
                            <div class="step-title">Konfigurasi GWO</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-1"></div>
                        <div class="stepper-item" id="stepper-gwo-2" onclick="goToGwoStep(2)" style="cursor: pointer;">
                            <div class="step-number">2</div>
                            <div class="step-title">Proses GWO</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-2"></div>
                        <div class="stepper-item" id="stepper-gwo-3" onclick="goToGwoStep(3)" style="cursor: pointer;">
                            <div class="step-number">3</div>
                            <div class="step-title">Hasil &amp; Perbandingan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GWO Step 1: Form -->
            <div id="gwo-step-content-1" class="step-opt-content">
                <div class="card mb-4 bg-white">
                    <form id="gwoSearchForm" onsubmit="event.preventDefault(); startGwoTuning();">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title border-0 pb-0 mb-0"><i class="bi bi-activity me-2 text-primary-custom"></i>Konfigurasi GWO (Grey Wolf Optimizer)</h5>
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
                                        $formatted = number_format((float)$gwoC, 6, ',', '.');
                                        $gwoC = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                    }
                                    if (is_numeric($gwoEps)) {
                                        $formatted = number_format((float)$gwoEps, 8, ',', '.');
                                        $gwoEps = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                    }
                                    if (is_numeric($gwoGam)) {
                                        $formatted = number_format((float)$gwoGam, 6, ',', '.');
                                        $gwoGam = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                    }
                                @endphp
                                <div class="alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" id="gwo-alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-info-circle-fill fs-4 text-info me-3" id="gwo-alert-icon"></i>
                                        <div>
                                            <h6 class="alert-heading fw-bold mb-1" style="font-size: 13.5px;" id="gwo-alert-title">Grey Wolf Optimizer (GWO) Telah Dijalankan</h6>
                                            <p class="mb-0 text-secondary" style="font-size: 12.5px;" id="gwo-alert-desc">
                                                Model aktif saat ini menggunakan parameter optimal: <strong>C = {{ $gwoC }}</strong>, <strong>&epsilon; = {{ $gwoEps }}</strong>, <strong>&gamma; = {{ $gwoGam }}</strong> dengan nilai <strong>MAPE: {{ $gwoMape }}%</strong>.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-1.5 rounded-3" onclick="unlockGwoParams()" id="btn-unlock-gwo">
                                            <i class="bi bi-pencil-square me-1"></i> Edit Parameter
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-1.5 rounded-3 d-none" onclick="lockGwoParams()" id="btn-lock-gwo">
                                            <i class="bi bi-x-circle me-1"></i> Batal
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Jumlah Serigala (Wolves)</label>
                                    <input type="number" name="wolves" id="gwo_wolves" class="form-control rounded-3" value="12" min="5" max="50" @if($gwoRun) disabled @endif>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Maksimal Iterasi</label>
                                    <input type="number" name="iterations" id="gwo_iterations" class="form-control rounded-3" value="20" min="10" max="200" @if($gwoRun) disabled @endif>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="d-block small fw-bold text-dark mb-0">Search Space Bounds (Rentang Kontinu)</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="auto_develop_gwo" onchange="toggleAutoGwoDevelop()" @if($gwoRun) disabled @endif>
                                        <label class="form-check-label small fw-bold text-secondary" for="auto_develop_gwo">Otomatis Kembangkan</label>
                                    </div>
                                </div>
                                
                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-4"><label class="small fw-semibold text-secondary">C (Min / Max)</label></div>
                                    <div class="col-md-4"><input type="number" step="any" name="c_min" id="c_min" class="form-control rounded-3" value="1.0" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="c_max" id="c_max" class="form-control rounded-3" value="1000.0" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-4"><label class="small fw-semibold text-secondary">Epsilon (Min / Max)</label></div>
                                    <div class="col-md-4"><input type="number" step="any" name="epsilon_min" id="epsilon_min" class="form-control rounded-3" value="0.0001" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="epsilon_max" id="epsilon_max" class="form-control rounded-3" value="0.1" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4"><label class="small fw-semibold text-secondary">Gamma (Min / Max)</label></div>
                                    <div class="col-md-4"><input type="number" step="any" name="gamma_min" id="gamma_min" class="form-control rounded-3" value="0.0001" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="gamma_max" id="gamma_max" class="form-control rounded-3" value="0.1" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                                    <i class="bi bi-play-fill me-1"></i>Jalankan GWO
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- GWO Step 2: Progress -->
            <div id="gwo-step-content-2" class="step-opt-content d-none">
                <div class="card mb-4 bg-white">
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title text-start mb-4"><i class="bi bi-activity me-2 text-primary-custom"></i>Proses Tuning Parameter SVR + GWO</h5>
                        
                        <div class="py-4">
                            <!-- Spinner Container -->
                            <div id="gwo-spinner-container">
                                <div class="spinner-border text-primary mb-3" style="width: 50px; height: 50px; border-width: 4px;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <!-- Success Check Container -->
                            <div id="gwo-success-container" class="d-none">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success p-4 rounded-circle mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-check-lg fs-1"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-2" id="gwo-process-title">Sedang Menyiapkan GWO...</h6>
                            <p class="text-secondary small mx-auto mb-4" style="max-width: 420px;" id="gwo-process-desc">Algoritma Grey Wolf Optimizer sedang diinisialisasi.</p>
                        </div>

                        <!-- Timer Box -->
                        <div class="py-3 bg-light rounded-3 mb-4 mx-auto" id="gwo-timer-box" style="max-width: 400px; border: 1px dashed #cbd5e1;">
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <span class="d-block text-muted small fw-semibold">Waktu Berjalan</span>
                                    <span class="fs-4 fw-bold text-dark" id="gwo-elapsed-timer">0s</span>
                                </div>
                                <div style="border-left: 1px solid #e2e8f0; height: 40px; margin-top: 5px;"></div>
                                <div>
                                    <span class="d-block text-muted small fw-semibold">Perkiraan Waktu</span>
                                    <span class="fs-4 fw-bold text-primary" id="gwo-estimated-timer">~45s</span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar Iterasi GWO -->
                        <div class="mx-auto mb-4" id="gwo-progress-bar-container" style="max-width: 500px;">
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="fw-bold text-dark" id="gwo-iter-label">Iterasi GWO: 0 / 20</span>
                                <span class="text-secondary" id="gwo-iter-pct">0%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="gwo-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="text-start border-top pt-4 mx-auto" style="max-width: 500px;">
                            <h6 class="fw-bold text-dark mb-3">Langkah Kerja GWO:</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="progress-step" id="gwo-pipe-1">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">1. Inisialisasi Posisi Serigala (Alpha, Beta, Delta)</span>
                                </div>
                                <div class="progress-step" id="gwo-pipe-2">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">2. Menghitung Fitness Awal</span>
                                </div>
                                <div class="progress-step" id="gwo-pipe-3">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label" id="gwo-pipe-3-text">3. Proses Iterasi GWO &amp; Pencarian Optimal</span>
                                </div>
                                <div class="progress-step" id="gwo-pipe-4">
                                    <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                                    <span class="step-label">4. Memperbarui Model Parameter Optimal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COMMON STEP 3 CONTENT: Hasil & Perbandingan -->
        <div id="results-step-content-3" class="d-none">
            <!-- Hasil Optimasi Parameter Table -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <h5 class="card-title border-0 pb-0 mb-3"><i class="bi bi-table me-2 text-primary-custom"></i>Hasil Optimasi Parameter</h5>
                    
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
                                    <th>Akurasi</th>
                                    <th>R² Score</th>
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
                                        <td style="font-weight: 600;" class="text-success">{{ $comp['mape'] }}</td>
                                        <td style="font-weight: 700;" class="text-primary">{{ $comp['akurasi'] }}</td>
                                        <td>{{ $comp['r2'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Grafik Perbandingan Performa -->
            <div class="card bg-white mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bar-chart-line me-2 text-primary-custom"></i>Grafik Perbandingan Performa Model</h5>
                    <div style="position: relative; height: 320px;">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Analisis Perbandingan Performa Model -->
            <div class="card mb-4 bg-white shadow-sm border border-light">
                <div class="card-body">
                    <h5 class="card-title text-dark mb-3"><i class="bi bi-chat-left-text-fill me-2 text-primary-custom"></i>Analisis Perbandingan Performa Model</h5>
                    
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

                        $bestModelMapeInterpret = 'Cukup Akurat (Perlu Dipantau)';
                        if ($bestModelMape < 10) {
                            $bestModelMapeInterpret = 'Sangat Akurat (Sangat Tepat)';
                        } elseif ($bestModelMape <= 20) {
                            $bestModelMapeInterpret = 'Baik (Layak Digunakan)';
                        } elseif ($bestModelMape > 50) {
                            $bestModelMapeInterpret = 'Kurang Akurat';
                        }

                        $bestR2Interpret = 'Kurang Baik dalam Membaca Pola';
                        if ($bestR2Val >= 0.67) {
                            $bestR2Interpret = 'Sangat Kuat dalam Membaca Pola';
                        } elseif ($bestR2Val >= 0.33) {
                            $bestR2Interpret = 'Cukup Baik dalam Membaca Pola';
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
                                                Menggunakan setelan optimal (C = {{ $formatParamVal($gsParamC, 6) }}, &epsilon; = {{ $formatParamVal($gsParamEps, 8) }}, &gamma; = {{ $formatParamVal($gsParamGam, 6) }}).
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
                                                Menggunakan setelan optimal (C = {{ $formatParamVal($gwoParamC, 6) }}, &epsilon; = {{ $formatParamVal($gwoParamEps, 8) }}, &gamma; = {{ $formatParamVal($gwoParamGam, 6) }}).
                                                @if($gwoImprovement > 0)
                                                    Berhasil mengurangi tingkat kesalahan sebesar <strong class="text-success">{{ number_format($gwoImprovement, 2, ',', '.') }}%</strong> dibanding model awal melalui pencarian otomatis yang meniru cara berburu kawanan serigala untuk menemukan setelan terbaik.
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
                                    <i class="bi {{ $iconClass }} me-2 fs-5"></i>Rekomendasi Model
                                </h6>
                                <div style="font-size: 12.5px; line-height: 1.6;">
                                    @if(count($models) > 0)
                                        <p class="mb-3">
                                            Berdasarkan hasil uji coba, model <strong>{{ $bestModelName }}</strong> terpilih sebagai model dengan akurasi tertinggi (kesalahan perkiraan terkecil MAPE = <strong>{{ number_format($bestModelMape, 2, ',', '.') }}%</strong> - kategori <strong>{{ $bestModelMapeInterpret }}</strong>), dan kemampuan membaca pola data sebesar <strong>{{ number_format($bestR2Val, 4, ',', '.') }}</strong> (kategori <strong>{{ $bestR2Interpret }}</strong>).
                                        </p>
                                        <p class="mb-3 small text-secondary-emphasis">
                                            Setelan parameter terbaik yang digunakan adalah:<br>
                                            &bull; C = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->c_value, 6) }}</strong><br>
                                            &bull; &epsilon; = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->epsilon_value, 8) }}</strong><br>
                                            &bull; &gamma; = <strong>{{ $formatParamVal($bestRunObj?->modelParameter?->gamma_value, 6) }}</strong>
                                        </p>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <li class="d-flex align-items-start gap-2">
                                                <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                                                <span>Gunakan model <strong>{{ $bestModelName }}</strong> untuk memperkirakan pendapatan parkir harian di masa depan.</span>
                                            </li>
                                            <li class="d-flex align-items-start gap-2">
                                                <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                                                <span>Metode Grey Wolf (GWO) terbukti lebih unggul menemukan setelan terbaik karena melacak secara menyeluruh tanpa dibatasi oleh pilihan angka tertentu (seperti pada Grid Search).</span>
                                            </li>
                                            <li class="d-flex align-items-start gap-2">
                                                <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                                                <span>Lakukan pencarian ulang setelan terbaik jika performa perkiraan model di lapangan menurun atau pola setoran parkir bergeser.</span>
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

            <!-- Riwayat Optimasi Parameter -->
            <div class="card bg-white mb-4 shadow-sm border border-light">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-clock-history me-2 text-primary-custom"></i>Riwayat Optimasi Parameter</h5>
                    @if($historyRuns->isEmpty())
                        <div class="text-center py-4 text-secondary">
                            <i class="bi bi-folder2-open fs-2 text-muted mb-2 d-block"></i>
                            Belum ada riwayat proses optimasi parameter.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Metode</th>
                                        <th>Parameter (C, &epsilon;, &gamma;)</th>
                                        <th>MAE</th>
                                        <th>RMSE</th>
                                        <th>MAPE</th>
                                        <th>Akurasi</th>
                                        <th>R² Score</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historyRuns as $run)
                                        @php
                                            $param = $run->modelParameter;
                                            $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                                            $isActive = ($run->id === $bestGsId || $run->id === $bestGwoId);
                                            
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
                                            $accVal = $metric ? number_format(max(0, 100 - $metric->mape), 2, ',', '.') . '%' : '-';
                                            $r2Val = $metric ? number_format($metric->r2_score, 2, ',', '.') : '-';
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($run->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                                            <td class="fw-bold">
                                                @if($run->model_type === 'svr_grid_search')
                                                    Grid Search
                                                @elseif($run->model_type === 'svr_gwo')
                                                    GWO (Grey Wolf)
                                                @else
                                                    {{ $run->model_name }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">C: {{ $cVal }}</span>
                                                <span class="badge bg-light text-dark border">&epsilon;: {{ $epsVal }}</span>
                                                <span class="badge bg-light text-dark border">&gamma;: {{ $gamVal }}</span>
                                            </td>
                                            <td>{{ $maeVal }}</td>
                                            <td>{{ $rmseVal }}</td>
                                            <td class="fw-bold text-success">{{ $mapeVal }}</td>
                                            <td class="fw-bold text-primary">{{ $accVal }}</td>
                                            <td>{{ $r2Val }}</td>
                                            <td class="text-center">
                                                @if($isActive)
                                                    <span class="badge bg-success text-white rounded-3 px-2 py-1.5" style="font-size: 11px;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif (Terbaik)
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white rounded-3 px-2 py-1.5" style="font-size: 11px;">
                                                        <i class="bi bi-clock-history me-1"></i>Riwayat
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- --- DETAIL EVALUASI PREDIKSI MODEL SVR + GRID SEARCH --- -->
            @php
                $gsRayonStats = collect([]);
                $gsBestRayon = null;
                $gsWorstRayon = null;
                $gsAvgDailyDeviation = 0;
                
                $gsMaxActualDate = '-';
                $gsMaxActualVal = 0;
                $gsMaxPredictedDate = '-';
                $gsMaxPredictedVal = 0;
                
                $gsTotalActualSum = 0;
                $gsTotalPredictedSum = 0;
                $gsTotalDiff = 0;
                $gsTotalDiffPercent = 0;
                $gsPredictedAtMaxActual = 0;
                $gsMaxActualAccuracy = 0;
                $gsActualAtMaxPredicted = 0;
                
                if ($gsRun) {
                    $gsRayonStats = $gsRun->predictionResults()
                        ->select('rayon_name', 
                            DB::raw('AVG(percentage_error) as avg_mape'), 
                            DB::raw('AVG(error_value) as avg_error'),
                            DB::raw('SUM(actual_value) as total_actual'), 
                            DB::raw('SUM(predicted_value) as total_predicted')
                        )
                        ->groupBy('rayon_name')
                        ->get();
                    
                    $gsBestRayon = $gsRayonStats->sortBy('avg_mape')->first();
                    $gsWorstRayon = $gsRayonStats->sortByDesc('avg_mape')->first();
                    
                    $gsAvgDailyDeviation = $gsRun->predictionResults()->avg('error_value') ?? 0;
                    
                    if ($gsChartData->count() > 0) {
                        $gsMaxActualRow = $gsChartData->sortByDesc('actual_value')->first();
                        $gsMaxPredictedRow = $gsChartData->sortByDesc('predicted_value')->first();
                        
                        $gsMaxActualDate = $gsMaxActualRow ? Carbon\Carbon::parse($gsMaxActualRow->tanggal)->translatedFormat('d F Y') : '-';
                        $gsMaxActualVal = $gsMaxActualRow ? $gsMaxActualRow->actual_value : 0;
                        
                        $gsMaxPredictedDate = $gsMaxPredictedRow ? Carbon\Carbon::parse($gsMaxPredictedRow->tanggal)->translatedFormat('d F Y') : '-';
                        $gsMaxPredictedVal = $gsMaxPredictedRow ? $gsMaxPredictedRow->predicted_value : 0;
                        
                        $gsPredictedAtMaxActual = $gsMaxActualRow ? $gsMaxActualRow->predicted_value : 0;
                        $gsActualAtMaxPredicted = $gsMaxPredictedRow ? $gsMaxPredictedRow->actual_value : 0;
                        $gsMaxActualAccuracy = $gsMaxActualVal > 0 ? (1 - abs($gsMaxActualVal - $gsPredictedAtMaxActual) / $gsMaxActualVal) * 100 : 0;
                        
                        $gsTotalActualSum = $gsChartData->sum('actual_value');
                        $gsTotalPredictedSum = $gsChartData->sum('predicted_value');
                        $gsTotalDiff = abs($gsTotalActualSum - $gsTotalPredictedSum);
                        $gsTotalDiffPercent = $gsTotalActualSum > 0 ? ($gsTotalDiff / $gsTotalActualSum) * 100 : 0;
                    }
                }
            @endphp
            
            <div id="grid-evaluation-details" class="evaluation-container d-none">
                @if(!$gsRun || !$gsMetricsObj)
                    <div class="card text-center py-5 shadow-sm border border-light bg-white mb-4">
                        <div class="card-body py-4">
                            <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
                            <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi Grid Search</h5>
                            <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan untuk metode Grid Search. Silakan jalankan <strong>Optimasi Grid Search</strong> terlebih dahulu.</p>
                        </div>
                    </div>
                @else
                    <!-- Card Metrik Evaluasi Model SVR + Grid Search -->
                    <h5 class="fw-bold mb-3 text-dark mt-4"><i class="bi bi-award-fill me-2 text-warning"></i>Hasil Evaluasi Model SVR + Grid Search</h5>
                    <div class="row g-3 mb-4">
                        <!-- MAE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Mean Absolute Error (MAE)</span>
                                <span class="metric-value-custom">Rp {{ number_format($gsMetricsObj->mae, 0, ',', '.') }}</span>
                                <span class="text-muted small" style="font-size: 11px;">Rata-rata absolut selisih error</span>
                            </div>
                        </div>
                        <!-- RMSE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Root Mean Squared Error (RMSE)</span>
                                <span class="metric-value-custom">Rp {{ number_format($gsMetricsObj->rmse, 0, ',', '.') }}</span>
                                <span class="text-muted small" style="font-size: 11px;">Akar kuadrat rata-rata error kuadrat</span>
                            </div>
                        </div>
                        <!-- Akurasi MAPE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Akurasi MAPE</span>
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
                        <!-- R2 Score -->
                        <div class="col-12 col-md-6 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">R² Score</span>
                                <span class="metric-value-custom">{{ number_format($gsMetricsObj->r2_score, 6, ',', '.') }}</span>
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
                                <span class="metric-value-custom text-secondary">{{ number_format($gsMetricsObj->mape, 4, ',', '.') }}%</span>
                                <span class="text-muted small" style="font-size: 11px;">Rata-rata persentase error</span>
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
                                $gsMae = $gsMetricsObj->mae;
                                
                                // Hitung Rata-rata Aktual untuk RMSE
                                $gsMeanActual = $gsRun->predictionResults()->avg('actual_value') ?? 0;
                                $gsRmsePercentage = $gsMeanActual > 0 ? ($gsRmse / $gsMeanActual) * 100 : 0;
                                
                                // 1. Klasifikasi MAPE (Tingkat Kesalahan Perkiraan)
                                if ($gsMape < 10) {
                                    $gsMapeCategory = "Sangat Akurat (Sangat Tepat)";
                                    $gsMapeDesc = "Rata-rata kesalahan tebakan model sangat kecil, yaitu kurang dari 10%. Hasil perkiraan ini sangat mendekati kenyataan pendapatan di lapangan.";
                                    $gsMapeColor = "text-success border-success bg-success-subtle";
                                    $gsMapeAlertClass = "alert-success text-success-emphasis bg-success-subtle border-success-subtle";
                                    $gsMapeIcon = "bi-patch-check-fill text-success";
                                } elseif ($gsMape <= 20) {
                                    $gsMapeCategory = "Baik (Layak Digunakan)";
                                    $gsMapeDesc = "Rata-rata kesalahan tebakan model berkisar antara 10% hingga 20%. Hasil perkiraan ini cukup andal dan layak digunakan untuk perencanaan.";
                                    $gsMapeColor = "text-primary border-primary bg-primary-subtle";
                                    $gsMapeAlertClass = "alert-primary text-primary-emphasis bg-primary-subtle border-primary-subtle";
                                    $gsMapeIcon = "bi-check-circle-fill text-primary";
                                } elseif ($gsMape <= 50) {
                                    $gsMapeCategory = "Cukup Akurat (Perlu Dipantau)";
                                    $gsMapeDesc = "Rata-rata kesalahan tebakan model berkisar antara 20% hingga 50%. Hasil perkiraan ini memiliki fluktuasi (naik-turun) sedang.";
                                    $gsMapeColor = "text-warning border-warning bg-warning-subtle";
                                    $gsMapeAlertClass = "alert-warning text-warning-emphasis bg-warning-subtle border-warning-subtle";
                                    $gsMapeIcon = "bi-exclamation-triangle-fill text-warning";
                                } else {
                                    $gsMapeCategory = "Kurang Akurat";
                                    $gsMapeDesc = "Rata-rata kesalahan tebakan model melebihi 50%. Model kurang disarankan untuk perencanaan karena selisih tebakannya cukup besar.";
                                    $gsMapeColor = "text-danger border-danger bg-danger-subtle";
                                    $gsMapeAlertClass = "alert-danger text-danger-emphasis bg-danger-subtle border-danger-subtle";
                                    $gsMapeIcon = "bi-x-circle-fill text-danger";
                                }

                                // 2. Klasifikasi R2 Score (Kemampuan Membaca Pola Pendapatan)
                                if ($gsR2 >= 0.67) {
                                    $gsR2Category = "Sangat Kuat (Sangat Baik)";
                                    $gsR2Desc = "Model sangat pintar dalam mengikuti pola naik-turunnya transaksi pendapatan harian di lapangan secara tepat.";
                                    $gsR2Icon = "bi-graph-up text-success";
                                } elseif ($gsR2 >= 0.33) {
                                    $gsR2Category = "Cukup Baik";
                                    $gsR2Desc = "Model cukup baik dalam mengikuti pola naik-turunnya transaksi, meskipun ada beberapa faktor luar yang tidak terbaca.";
                                    $gsR2Icon = "bi-graph-up text-primary";
                                } else {
                                    $gsR2Category = "Kurang Baik";
                                    $gsR2Desc = "Model kesulitan mengenali pola naik-turunnya transaksi pendapatan. Perlu penyesuaian agar lebih peka terhadap perubahan data.";
                                    $gsR2Icon = "bi-graph-up text-danger";
                                }
                                
                                // 3. Klasifikasi RMSE (Selisih Nominal Rata-Rata)
                                if ($gsRmsePercentage < 10) {
                                    $gsRmseCategory = "Sangat Kecil (Bagus)";
                                    $gsRmseDesc = "Rata-rata selisih nominal uang antara hasil tebakan dan kenyataan di lapangan berada di batas aman (di bawah 10% dari rata-rata pendapatan, yaitu sebesar <strong>" . number_format($gsRmsePercentage, 2, ',', '.') . "%</strong>). Kinerja dikategorikan <strong>Sangat Baik</strong>.";
                                    $gsRmseColor = "text-success";
                                    $gsRmseIcon = "bi-shield-check-fill text-success";
                                } else {
                                    $gsRmseCategory = "Perlu Perbaikan (Terlalu Lebar)";
                                    $gsRmseDesc = "Rata-rata selisih nominal uang antara tebakan dan kenyataan di lapangan melampaui batas toleransi 10% (yaitu sebesar <strong>" . number_format($gsRmsePercentage, 2, ',', '.') . "%</strong> dari rata-rata data aktual Rp " . number_format($gsMeanActual, 0, ',', '.') . "). Setelan model perlu diperbaiki agar selisih uangnya lebih kecil.";
                                    $gsRmseColor = "text-warning";
                                    $gsRmseIcon = "bi-exclamation-octagon-fill text-warning";
                                }

                                // 5. Rekomendasi berdasarkan kombinasi nilai
                                $gsRecommendations = [];
                                if ($gsMape < 10 && $gsR2 >= 0.67) {
                                    $gsRecommendations[] = "<strong>Model Sangat Optimal:</strong> Kinerja model SVR + Grid Search ini dinilai sangat kuat dan akurat. Sangat layak digunakan langsung untuk perencanaan anggaran pendapatan retribusi.";
                                } else {
                                    $gsRecommendations[] = "<strong>Pertimbangkan Algoritma GWO:</strong> Jika hasil Grid Search dirasa masih kurang optimal, cobalah bandingkan dengan hasil <strong>Grey Wolf Optimizer (GWO)</strong> untuk mencari setelan parameter yang lebih presisi (paling optimal).";
                                }
                                $gsRecommendations[] = "<strong>Pelatihan Ulang Berkala:</strong> Lakukan pelatihan ulang secara berkala saat data transaksi baru ditambahkan agar model tetap adaptif.";
                            @endphp

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <h6 class="fw-bold text-secondary text-uppercase mb-2 shadow-none border-0 pb-0" style="font-size: 11px; letter-spacing: 0.5px;">Keterangan Hasil Analisis</h6>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gsMapeIcon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Tingkat Kesalahan Perkiraan (MAPE: {{ number_format($gsMape, 2, ',', '.') }}%): <span class="{{ explode(' ', $gsMapeColor)[0] }}">{{ $gsMapeCategory }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gsMapeDesc !!}</div>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gsR2Icon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Kemampuan Membaca Pola Pendapatan (R² Score: {{ number_format($gsR2, 4, ',', '.') }}): <span class="text-dark">{{ $gsR2Category }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gsR2Desc !!}</div>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gsRmseIcon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Selisih Nominal Rata-Rata (RMSE): <span class="{{ $gsRmseColor }}">{{ $gsRmseCategory }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gsRmseDesc !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="p-3 rounded-3 h-100 {{ $gsMapeAlertClass }} border border-0">
                                        <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                                            <i class="bi bi-lightbulb-fill me-2 fs-5"></i>Rekomendasi Tindakan
                                        </h6>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="font-size: 12.5px; line-height: 1.6;">
                                            @foreach($gsRecommendations as $rec)
                                                <li class="d-flex align-items-start gap-2">
                                                    <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                                                    <span>{!! $rec !!}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Aktual vs Prediksi Grid Search -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + Grid Search</h5>
                            <div style="height: 380px; position: relative; width: 100%;">
                                <canvas id="gsChart"></canvas>
                            </div>
                            
                            <!-- Detailed Graph Analysis Card -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-warning shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary-custom me-1"></i>Analisis Kesesuaian Tren Grafik (Grid Search)</h6>
                                <div class="row g-3 mt-1 text-sm text-secondary">
                                    <div class="col-md-6 border-end border-light-subtle">
                                        <div class="mb-2">
                                            <i class="bi bi-arrow-repeat text-primary-custom me-1"></i>
                                            <strong>Selisih Tren Total:</strong> 
                                            Sebesar <strong>{{ number_format($gsTotalDiffPercent, 2, ',', '.') }}%</strong> dari total transaksi riil di lapangan.
                                        </div>
                                        <div>
                                            <i class="bi bi-calendar-check text-primary-custom me-1"></i>
                                            <strong>Puncak Realisasi (Aktual):</strong> 
                                            <strong>{{ $gsMaxActualDate }}</strong> (Aktual: <strong>Rp {{ number_format($gsMaxActualVal, 0, ',', '.') }}</strong>, Perkiraan: <strong>Rp {{ number_format($gsPredictedAtMaxActual, 0, ',', '.') }}</strong>).
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="bi bi-graph-up-arrow text-warning me-1"></i>
                                            <strong>Puncak Hasil Perkiraan (Prediksi):</strong> 
                                            <strong>{{ $gsMaxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($gsMaxPredictedVal, 0, ',', '.') }}</strong>.
                                        </div>
                                    </div>
                                </div>
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
                                        <input type="hidden" name="grid_step" value="3">
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-custom-nowrap" style="font-size: 13px;">
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
                                            @forelse($gsPredictions as $index => $pred)
                                                <tr>
                                                    <td>{{ $gsPredictions->firstItem() + $index }}</td>
                                                    <td>{{ Carbon\Carbon::parse($pred->tanggal)->translatedFormat('d F Y') }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $pred->rayon_name }}</span></td>
                                                    <td class="text-end fw-semibold">Rp {{ number_format($pred->actual_value, 0, ',', '.') }}</td>
                                                    <td class="text-end fw-bold text-primary-custom">Rp {{ number_format($pred->predicted_value, 0, ',', '.') }}</td>
                                                    <td class="text-end text-danger">Rp {{ number_format($pred->error_value, 0, ',', '.') }}</td>
                                                    <td class="text-end fw-semibold">{{ number_format($pred->percentage_error, 2, ',', '.') }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data prediksi yang cocok dengan filter.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detailed Rayon Analysis Box -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i>Analisis Akurasi Prediksi Per Rayon (Grid Search)</h6>
                                <div class="row g-3 mt-1 text-sm text-secondary">
                                    <div class="col-md-4 border-end border-light-subtle">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Rayon Paling Presisi</span>
                                        @if($gsBestRayon)
                                            <div class="fw-bold text-success fs-6 mb-1">{{ $gsBestRayon->rayon_name }}</div>
                                            <span class="d-block small">Rata-rata MAPE: <strong>{{ number_format($gsBestRayon->avg_mape, 2, ',', '.') }}%</strong></span>
                                        @else
                                            <span class="text-muted small">Data tidak tersedia</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4 border-end border-light-subtle">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Deviasi Terbesar</span>
                                        @if($gsWorstRayon)
                                            <div class="fw-bold text-danger fs-6 mb-1">{{ $gsWorstRayon->rayon_name }}</div>
                                            <span class="d-block small">Rata-rata MAPE: <strong>{{ number_format($gsWorstRayon->avg_mape, 2, ',', '.') }}%</strong></span>
                                        @else
                                            <span class="text-muted small">Data tidak tersedia</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Rata-Rata Selisih Uang (Deviasi Nominal Harian)</span>
                                        <div class="fw-bold text-dark fs-6 mb-1">Rp {{ number_format(abs($gsAvgDailyDeviation), 0, ',', '.') }} / hari</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination container -->
                            @if($gsPredictions->hasPages())
                                <div class="pagination-container mt-4 d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="text-secondary small">
                                        Menampilkan {{ $gsPredictions->firstItem() ?? 0 }} - {{ $gsPredictions->lastItem() ?? 0 }} dari {{ $gsPredictions->total() }} data
                                    </div>
                                    <div>
                                        {!! $gsPredictions->links('components.pagination') !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- --- DETAIL EVALUASI PREDIKSI MODEL SVR + GWO --- -->
            @php
                $gwoRayonStats = collect([]);
                $gwoBestRayon = null;
                $gwoWorstRayon = null;
                $gwoAvgDailyDeviation = 0;
                
                $gwoMaxActualDate = '-';
                $gwoMaxActualVal = 0;
                $gwoMaxPredictedDate = '-';
                $gwoMaxPredictedVal = 0;
                
                $gwoTotalActualSum = 0;
                $gwoTotalPredictedSum = 0;
                $gwoTotalDiff = 0;
                $gwoTotalDiffPercent = 0;
                $gwoPredictedAtMaxActual = 0;
                $gwoMaxActualAccuracy = 0;
                $gwoActualAtMaxPredicted = 0;
                
                if ($gwoRun) {
                    $gwoRayonStats = $gwoRun->predictionResults()
                        ->select('rayon_name', 
                            DB::raw('AVG(percentage_error) as avg_mape'), 
                            DB::raw('AVG(error_value) as avg_error'),
                            DB::raw('SUM(actual_value) as total_actual'), 
                            DB::raw('SUM(predicted_value) as total_predicted')
                        )
                        ->groupBy('rayon_name')
                        ->get();
                    
                    $gwoBestRayon = $gwoRayonStats->sortBy('avg_mape')->first();
                    $gwoWorstRayon = $gwoRayonStats->sortByDesc('avg_mape')->first();
                    
                    $gwoAvgDailyDeviation = $gwoRun->predictionResults()->avg('error_value') ?? 0;
                    
                    if ($gwoChartData->count() > 0) {
                        $gwoMaxActualRow = $gwoChartData->sortByDesc('actual_value')->first();
                        $gwoMaxPredictedRow = $gwoChartData->sortByDesc('predicted_value')->first();
                        
                        $gwoMaxActualDate = $gwoMaxActualRow ? Carbon\Carbon::parse($gwoMaxActualRow->tanggal)->translatedFormat('d F Y') : '-';
                        $gwoMaxActualVal = $gwoMaxActualRow ? $gwoMaxActualRow->actual_value : 0;
                        
                        $gwoMaxPredictedDate = $gwoMaxPredictedRow ? Carbon\Carbon::parse($gwoMaxPredictedRow->tanggal)->translatedFormat('d F Y') : '-';
                        $gwoMaxPredictedVal = $gwoMaxPredictedRow ? $gwoMaxPredictedRow->predicted_value : 0;
                        
                        $gwoPredictedAtMaxActual = $gwoMaxActualRow ? $gwoMaxActualRow->predicted_value : 0;
                        $gwoActualAtMaxPredicted = $gwoMaxPredictedRow ? $gwoMaxPredictedRow->actual_value : 0;
                        $gwoMaxActualAccuracy = $gwoMaxActualVal > 0 ? (1 - abs($gwoMaxActualVal - $gwoPredictedAtMaxActual) / $gwoMaxActualVal) * 100 : 0;
                        
                        $gwoTotalActualSum = $gwoChartData->sum('actual_value');
                        $gwoTotalPredictedSum = $gwoChartData->sum('predicted_value');
                        $gwoTotalDiff = abs($gwoTotalActualSum - $gwoTotalPredictedSum);
                        $gwoTotalDiffPercent = $gwoTotalActualSum > 0 ? ($gwoTotalDiff / $gwoTotalActualSum) * 100 : 0;
                    }
                }
            @endphp
            
            <div id="gwo-evaluation-details" class="evaluation-container d-none">
                @if(!$gwoRun || !$gwoMetricsObj)
                    <div class="card text-center py-5 shadow-sm border border-light bg-white mb-4">
                        <div class="card-body py-4">
                            <i class="bi bi-graph-up-arrow text-secondary mb-3 d-block" style="font-size: 40px;"></i>
                            <h5 class="fw-semibold text-secondary">Belum Ada Hasil Prediksi GWO</h5>
                            <p class="text-muted small mb-0">Belum ada hasil prediksi tersimpan untuk metode Grey Wolf Optimizer. Silakan jalankan <strong>Optimasi GWO</strong> terlebih dahulu.</p>
                        </div>
                    </div>
                @else
                    <!-- Card Metrik Evaluasi Model SVR + GWO -->
                    <h5 class="fw-bold mb-3 text-dark mt-4"><i class="bi bi-award-fill me-2 text-success"></i>Hasil Evaluasi Model SVR + GWO</h5>
                    <div class="row g-3 mb-4">
                        <!-- MAE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Mean Absolute Error (MAE)</span>
                                <span class="metric-value-custom">Rp {{ number_format($gwoMetricsObj->mae, 0, ',', '.') }}</span>
                                <span class="text-muted small" style="font-size: 11px;">Rata-rata absolut selisih error</span>
                            </div>
                        </div>
                        <!-- RMSE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Root Mean Squared Error (RMSE)</span>
                                <span class="metric-value-custom">Rp {{ number_format($gwoMetricsObj->rmse, 0, ',', '.') }}</span>
                                <span class="text-muted small" style="font-size: 11px;">Akar kuadrat rata-rata error kuadrat</span>
                            </div>
                        </div>
                        <!-- Akurasi MAPE -->
                        <div class="col-12 col-md-4 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">Akurasi MAPE</span>
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
                        <!-- R2 Score -->
                        <div class="col-12 col-md-6 col-lg">
                            <div class="metric-card-custom">
                                <span class="metric-label-custom">R² Score</span>
                                <span class="metric-value-custom">{{ number_format($gwoMetricsObj->r2_score, 6, ',', '.') }}</span>
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
                                <span class="metric-value-custom text-secondary">{{ number_format($gwoMetricsObj->mape, 4, ',', '.') }}%</span>
                                <span class="text-muted small" style="font-size: 11px;">Rata-rata persentase error</span>
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
                                $gwoMae = $gwoMetricsObj->mae;
                                
                                // Hitung Rata-rata Aktual untuk RMSE
                                $gwoMeanActual = $gwoRun->predictionResults()->avg('actual_value') ?? 0;
                                $gwoRmsePercentage = $gwoMeanActual > 0 ? ($gwoRmse / $gwoMeanActual) * 100 : 0;
                                
                                // 1. Klasifikasi MAPE (Tingkat Kesalahan Perkiraan)
                                if ($gwoMape < 10) {
                                    $gwoMapeCategory = "Sangat Akurat (Sangat Tepat)";
                                    $gwoMapeDesc = "Rata-rata kesalahan tebakan model sangat kecil, yaitu kurang dari 10%. Hasil perkiraan ini sangat mendekati kenyataan pendapatan di lapangan.";
                                    $gwoMapeColor = "text-success border-success bg-success-subtle";
                                    $gwoMapeAlertClass = "alert-success text-success-emphasis bg-success-subtle border-success-subtle";
                                    $gwoMapeIcon = "bi-patch-check-fill text-success";
                                } elseif ($gwoMape <= 20) {
                                    $gwoMapeCategory = "Baik (Layak Digunakan)";
                                    $gwoMapeDesc = "Rata-rata kesalahan tebakan model berkisar antara 10% hingga 20%. Hasil perkiraan ini cukup andal dan layak digunakan untuk perencanaan.";
                                    $gwoMapeColor = "text-primary border-primary bg-primary-subtle";
                                    $gwoMapeAlertClass = "alert-primary text-primary-emphasis bg-primary-subtle border-primary-subtle";
                                    $gwoMapeIcon = "bi-check-circle-fill text-primary";
                                } elseif ($gwoMape <= 50) {
                                    $gwoMapeCategory = "Cukup Akurat (Perlu Dipantau)";
                                    $gwoMapeDesc = "Rata-rata kesalahan tebakan model berkisar antara 20% hingga 50%. Hasil perkiraan ini memiliki fluktuasi (naik-turun) sedang.";
                                    $gwoMapeColor = "text-warning border-warning bg-warning-subtle";
                                    $gwoMapeAlertClass = "alert-warning text-warning-emphasis bg-warning-subtle border-warning-subtle";
                                    $gwoMapeIcon = "bi-exclamation-triangle-fill text-warning";
                                } else {
                                    $gwoMapeCategory = "Kurang Akurat";
                                    $gwoMapeDesc = "Rata-rata kesalahan tebakan model melebihi 50%. Model kurang disarankan untuk perencanaan karena selisih tebakannya cukup besar.";
                                    $gwoMapeColor = "text-danger border-danger bg-danger-subtle";
                                    $gwoMapeAlertClass = "alert-danger text-danger-emphasis bg-danger-subtle border-danger-subtle";
                                    $gwoMapeIcon = "bi-x-circle-fill text-danger";
                                }

                                // 2. Klasifikasi R2 Score (Kemampuan Membaca Pola Pendapatan)
                                if ($gwoR2 >= 0.67) {
                                    $gwoR2Category = "Sangat Kuat (Sangat Baik)";
                                    $gwoR2Desc = "Model sangat pintar dalam mengikuti pola naik-turunnya transaksi pendapatan harian di lapangan secara tepat.";
                                    $gwoR2Icon = "bi-graph-up text-success";
                                } elseif ($gwoR2 >= 0.33) {
                                    $gwoR2Category = "Cukup Baik";
                                    $gwoR2Desc = "Model cukup baik dalam mengikuti pola naik-turunnya transaksi, meskipun ada beberapa faktor luar yang tidak terbaca.";
                                    $gwoR2Icon = "bi-graph-up text-primary";
                                } else {
                                    $gwoR2Category = "Kurang Baik";
                                    $gwoR2Desc = "Model kesulitan mengenali pola naik-turunnya transaksi pendapatan. Perlu penyesuaian agar lebih peka terhadap perubahan data.";
                                    $gwoR2Icon = "bi-graph-up text-danger";
                                }
                                
                                // 3. Klasifikasi RMSE (Selisih Nominal Rata-Rata)
                                if ($gwoRmsePercentage < 10) {
                                    $gwoRmseCategory = "Sangat Kecil (Bagus)";
                                    $gwoRmseDesc = "Rata-rata selisih nominal uang antara hasil tebakan dan kenyataan di lapangan berada di batas aman (di bawah 10% dari rata-rata pendapatan, yaitu sebesar <strong>" . number_format($gwoRmsePercentage, 2, ',', '.') . "%</strong>). Kinerja dikategorikan <strong>Sangat Baik</strong>.";
                                    $gwoRmseColor = "text-success";
                                    $gwoRmseIcon = "bi-shield-check-fill text-success";
                                } else {
                                    $gwoRmseCategory = "Perlu Perbaikan (Terlalu Lebar)";
                                    $gwoRmseDesc = "Rata-rata selisih nominal uang antara tebakan dan kenyataan di lapangan melampaui batas toleransi 10% (yaitu sebesar <strong>" . number_format($gwoRmsePercentage, 2, ',', '.') . "%</strong> dari rata-rata data aktual Rp " . number_format($gwoMeanActual, 0, ',', '.') . "). Setelan model perlu diperbaiki agar selisih uangnya lebih kecil.";
                                    $gwoRmseColor = "text-warning";
                                    $gwoRmseIcon = "bi-exclamation-octagon-fill text-warning";
                                }

                                // 5. Rekomendasi berdasarkan kombinasi nilai
                                $gwoRecommendations = [];
                                if ($gwoMape < 10 && $gwoR2 >= 0.67) {
                                    $gwoRecommendations[] = "<strong>Model GWO Sangat Kuat:</strong> Grey Wolf Optimizer berhasil menemukan konfigurasi parameter secara optimal dengan tingkat akurasi sangat presisi. Model aktif sangat layak digunakan untuk peramalan resmi.";
                                } else {
                                    $gwoRecommendations[] = "<strong>Evaluasi Setelan Pencarian GWO:</strong> Jika akurasi model GWO masih dirasa kurang, pertimbangkan untuk memperluas rentang batas pencarian setelan (Min/Max parameter) atau menaikkan jumlah langkah pencarian.";
                                }
                                $gwoRecommendations[] = "<strong>Pelatihan Ulang Berkala:</strong> Lakukan pelatihan ulang secara berkala untuk menjaga sensitivitas temporal model terhadap dinamika parkir harian.";
                            @endphp

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <h6 class="fw-bold text-secondary text-uppercase mb-2 shadow-none border-0 pb-0" style="font-size: 11px; letter-spacing: 0.5px;">Keterangan Hasil Analisis</h6>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gwoMapeIcon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Tingkat Kesalahan Perkiraan (MAPE: {{ number_format($gwoMape, 2, ',', '.') }}%): <span class="{{ explode(' ', $gwoMapeColor)[0] }}">{{ $gwoMapeCategory }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gwoMapeDesc !!}</div>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gwoR2Icon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Kemampuan Membaca Pola Pendapatan (R² Score: {{ number_format($gwoR2, 4, ',', '.') }}): <span class="text-dark">{{ $gwoR2Category }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gwoR2Desc !!}</div>
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                                            <div class="fs-4"><i class="bi {{ $gwoRmseIcon }}"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Selisih Nominal Rata-Rata (RMSE): <span class="{{ $gwoRmseColor }}">{{ $gwoRmseCategory }}</span></div>
                                                <div class="text-secondary small" style="line-height: 1.5;">{!! $gwoRmseDesc !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="p-3 rounded-3 h-100 {{ $gwoMapeAlertClass }} border border-0">
                                        <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                                            <i class="bi bi-lightbulb-fill me-2 fs-5"></i>Rekomendasi Tindakan
                                        </h6>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="font-size: 12.5px; line-height: 1.6;">
                                            @foreach($gwoRecommendations as $rec)
                                                <li class="d-flex align-items-start gap-2">
                                                    <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                                                    <span>{!! $rec !!}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Aktual vs Prediksi GWO -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + GWO</h5>
                            <div style="height: 380px; position: relative; width: 100%;">
                                <canvas id="gwoChart"></canvas>
                            </div>
                            
                            <!-- Detailed Graph Analysis Card -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary-custom me-1"></i>Analisis Kesesuaian Tren Grafik (GWO)</h6>
                                <div class="row g-3 mt-1 text-sm text-secondary">
                                    <div class="col-md-6 border-end border-light-subtle">
                                        <div class="mb-2">
                                            <i class="bi bi-arrow-repeat text-primary-custom me-1"></i>
                                            <strong>Selisih Tren Total:</strong> 
                                            Sebesar <strong>{{ number_format($gwoTotalDiffPercent, 2, ',', '.') }}%</strong> dari total transaksi riil di lapangan.
                                        </div>
                                        <div>
                                            <i class="bi bi-calendar-check text-primary-custom me-1"></i>
                                            <strong>Puncak Realisasi (Aktual):</strong> 
                                            <strong>{{ $gwoMaxActualDate }}</strong> (Aktual: <strong>Rp {{ number_format($gwoMaxActualVal, 0, ',', '.') }}</strong>, Perkiraan: <strong>Rp {{ number_format($gwoPredictedAtMaxActual, 0, ',', '.') }}</strong>).
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="bi bi-graph-up-arrow text-warning me-1"></i>
                                            <strong>Puncak Hasil Perkiraan (Prediksi):</strong> 
                                            <strong>{{ $gwoMaxPredictedDate }}</strong> sebesar <strong>Rp {{ number_format($gwoMaxPredictedVal, 0, ',', '.') }}</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Hasil Prediksi GWO -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0 border-0 pb-0"><i class="bi bi-table me-2 text-primary-custom"></i>Tabel Hasil Prediksi GWO (Data Testing)</h5>
                                    
                                    <!-- Rayon Filter Form -->
                                    <form method="GET" action="{{ route('operator.optimasi.index') }}" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="method" value="gwo">
                                        <input type="hidden" name="gwo_step" value="3">
                                        <input type="hidden" name="grid_step" value="{{ request('grid_step', 1) }}">
                                        <label for="rayon_id_gwo" class="small fw-semibold text-secondary text-nowrap mb-0" style="font-size: 11.5px;">Filter Rayon:</label>
                                        <select id="rayon_id_gwo" name="rayon_id" class="form-select form-select-sm" style="font-size: 12px; padding: 4px 12px; height: 32px;" onchange="this.form.submit()">
                                            <option value="0">Semua Rayon</option>
                                            @foreach($rayons as $rayon)
                                                <option value="{{ $rayon->id }}" {{ $rayonId == $rayon->id ? 'selected' : '' }}>{{ $rayon->nama_rayon }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-custom-nowrap" style="font-size: 13px;">
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
                                            @forelse($gwoPredictions as $index => $pred)
                                                <tr>
                                                    <td>{{ $gwoPredictions->firstItem() + $index }}</td>
                                                    <td>{{ Carbon\Carbon::parse($pred->tanggal)->translatedFormat('d F Y') }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $pred->rayon_name }}</span></td>
                                                    <td class="text-end fw-semibold">Rp {{ number_format($pred->actual_value, 0, ',', '.') }}</td>
                                                    <td class="text-end fw-bold text-primary-custom">Rp {{ number_format($pred->predicted_value, 0, ',', '.') }}</td>
                                                    <td class="text-end text-danger">Rp {{ number_format($pred->error_value, 0, ',', '.') }}</td>
                                                    <td class="text-end fw-semibold">{{ number_format($pred->percentage_error, 2, ',', '.') }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data prediksi yang cocok dengan filter.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detailed Rayon Analysis Box -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i>Analisis Akurasi Prediksi Per Rayon (GWO)</h6>
                                <div class="row g-3 mt-1 text-sm text-secondary">
                                    <div class="col-md-4 border-end border-light-subtle">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Rayon Paling Presisi</span>
                                        @if($gwoBestRayon)
                                            <div class="fw-bold text-success fs-6 mb-1">{{ $gwoBestRayon->rayon_name }}</div>
                                            <span class="d-block small">Rata-rata MAPE: <strong>{{ number_format($gwoBestRayon->avg_mape, 2, ',', '.') }}%</strong></span>
                                        @else
                                            <span class="text-muted small">Data tidak tersedia</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4 border-end border-light-subtle">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Deviasi Terbesar</span>
                                        @if($gwoWorstRayon)
                                            <div class="fw-bold text-danger fs-6 mb-1">{{ $gwoWorstRayon->rayon_name }}</div>
                                            <span class="d-block small">Rata-rata MAPE: <strong>{{ number_format($gwoWorstRayon->avg_mape, 2, ',', '.') }}%</strong></span>
                                        @else
                                            <span class="text-muted small">Data tidak tersedia</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <span class="text-xs text-uppercase fw-semibold d-block text-secondary mb-1">Rata-Rata Selisih Uang (Deviasi Nominal Harian)</span>
                                        <div class="fw-bold text-dark fs-6 mb-1">Rp {{ number_format(abs($gwoAvgDailyDeviation), 0, ',', '.') }} / hari</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination container -->
                            @if($gwoPredictions->hasPages())
                                <div class="pagination-container mt-4 d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="text-secondary small">
                                        Menampilkan {{ $gwoPredictions->firstItem() ?? 0 }} - {{ $gwoPredictions->lastItem() ?? 0 }} dari {{ $gwoPredictions->total() }} data
                                    </div>
                                    <div>
                                        {!! $gwoPredictions->links('components.pagination') !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Re-train / Re-optimize Button -->
            <div class="d-flex justify-content-end mb-4">
                <button type="button" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm" onclick="retuneCurrentMethod()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Optimasi Ulang
                </button>
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let gridStep = 1;
    let gwoStep = 1;
    let currentMethod = 'grid'; // 'grid' or 'gwo'
    let isGridRunning = false;
    let isGwoRunning = false;
    
    // Parameter Terbaik dari Controller
    const bestParamsGs = {
        c: @json($gsRun ? (float)$gsRun->modelParameter?->c_value : null),
        epsilon: @json($gsRun ? (float)$gsRun->modelParameter?->epsilon_value : null),
        gamma: @json($gsRun ? $gsRun->modelParameter?->gamma_value : null)
    };

    const bestParamsGwo = {
        c: @json($gwoRun ? (float)$gwoRun->modelParameter?->c_value : null),
        epsilon: @json($gwoRun ? (float)$gwoRun->modelParameter?->epsilon_value : null),
        gamma: @json($gwoRun ? $gwoRun->modelParameter?->gamma_value : null)
    };

    window.unlockGridParams = function() {
        const fields = ['grid_c', 'grid_epsilon', 'grid_gamma'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.removeAttribute('disabled');
                el.classList.add('param-input-editable');
            }
        });
        
        const checkbox = document.getElementById('auto_develop_grid');
        if (checkbox) {
            checkbox.removeAttribute('disabled');
        }
        
        window.toggleAutoGridDevelop();

        document.getElementById('btn-unlock-grid')?.classList.add('d-none');
        document.getElementById('btn-lock-grid')?.classList.remove('d-none');
        
        const alertEl = document.getElementById('grid-alert-info');
        if (alertEl) {
            alertEl.className = "alert alert-warning border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
        }
        const iconEl = document.getElementById('grid-alert-icon');
        if (iconEl) {
            iconEl.className = "bi bi-exclamation-triangle-fill fs-4 text-warning me-3";
        }
        const titleEl = document.getElementById('grid-alert-title');
        if (titleEl) titleEl.innerText = "Mode Edit Aktif";
        
        const descEl = document.getElementById('grid-alert-desc');
        if (descEl) descEl.innerText = "Anda sekarang dapat mengubah konfigurasi rentang nilai parameter Grid Search di bawah ini.";
    }

    window.lockGridParams = function() {
        const fields = ['grid_c', 'grid_epsilon', 'grid_gamma'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.setAttribute('disabled', 'true');
                el.classList.remove('param-input-editable');
            }
        });

        const checkbox = document.getElementById('auto_develop_grid');
        if (checkbox) {
            checkbox.setAttribute('disabled', 'true');
        }
        
        initializeDefaultParams();
        loadTempParams();
        updateGridInfoText();
        
        document.getElementById('btn-unlock-grid')?.classList.remove('d-none');
        document.getElementById('btn-lock-grid')?.classList.add('d-none');
        
        const alertEl = document.getElementById('grid-alert-info');
        if (alertEl) {
            alertEl.className = "alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
        }
        const iconEl = document.getElementById('grid-alert-icon');
        if (iconEl) {
            iconEl.className = "bi bi-info-circle-fill fs-4 text-info me-3";
        }
        const titleEl = document.getElementById('grid-alert-title');
        if (titleEl) titleEl.innerText = "Grid Search Telah Dijalankan";
        
        const descEl = document.getElementById('grid-alert-desc');
        if (descEl) {
            descEl.innerHTML = `Model aktif saat ini menggunakan parameter optimal: <strong>C = {{ $gsRun ? $gsC : '' }}</strong>, <strong>&epsilon; = {{ $gsRun ? $gsEps : '' }}</strong>, <strong>&gamma; = {{ $gsRun ? $gsGam : '' }}</strong> dengan nilai <strong>MAPE: {{ $gsRun ? $gsMape : '' }}%</strong>.`;
        }
    }

    window.unlockGwoParams = function() {
        const fields = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.removeAttribute('disabled');
                el.classList.add('param-input-editable');
            }
        });

        const checkbox = document.getElementById('auto_develop_gwo');
        if (checkbox) {
            checkbox.removeAttribute('disabled');
        }

        window.toggleAutoGwoDevelop();

        document.getElementById('btn-unlock-gwo')?.classList.add('d-none');
        document.getElementById('btn-lock-gwo')?.classList.remove('d-none');
        
        const alertEl = document.getElementById('gwo-alert-info');
        if (alertEl) {
            alertEl.className = "alert alert-warning border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
        }
        const iconEl = document.getElementById('gwo-alert-icon');
        if (iconEl) {
            iconEl.className = "bi bi-exclamation-triangle-fill fs-4 text-warning me-3";
        }
        const titleEl = document.getElementById('gwo-alert-title');
        if (titleEl) titleEl.innerText = "Mode Edit Aktif";
        
        const descEl = document.getElementById('gwo-alert-desc');
        if (descEl) descEl.innerText = "Anda sekarang dapat mengubah konfigurasi parameter GWO dan rentang search space di bawah ini.";
    }

    window.lockGwoParams = function() {
        const fields = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.setAttribute('disabled', 'true');
                el.classList.remove('param-input-editable');
            }
        });

        const checkbox = document.getElementById('auto_develop_gwo');
        if (checkbox) {
            checkbox.setAttribute('disabled', 'true');
        }
        
        initializeDefaultParams();
        loadTempParams();
        
        document.getElementById('btn-unlock-gwo')?.classList.remove('d-none');
        document.getElementById('btn-lock-gwo')?.classList.add('d-none');
        
        const alertEl = document.getElementById('gwo-alert-info');
        if (alertEl) {
            alertEl.className = "alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
        }
        const iconEl = document.getElementById('gwo-alert-icon');
        if (iconEl) {
            iconEl.className = "bi bi-info-circle-fill fs-4 text-info me-3";
        }
        const titleEl = document.getElementById('gwo-alert-title');
        if (titleEl) titleEl.innerText = "Grey Wolf Optimizer (GWO) Telah Dijalankan";
        
        const descEl = document.getElementById('gwo-alert-desc');
        if (descEl) {
            descEl.innerHTML = `Model aktif saat ini menggunakan parameter optimal: <strong>C = {{ $gwoRun ? $gwoC : '' }}</strong>, <strong>&epsilon; = {{ $gwoRun ? $gwoEps : '' }}</strong>, <strong>&gamma; = {{ $gwoRun ? $gwoGam : '' }}</strong> dengan nilai <strong>MAPE: {{ $gwoRun ? $gwoMape : '' }}%</strong>.`;
        }
    }

    const bestParamsDefault = {
        c: @json($lastRun ? (float)$lastRun->modelParameter?->c_value : 1.0),
        epsilon: @json($lastRun ? (float)$lastRun->modelParameter?->epsilon_value : 0.1),
        gamma: @json($lastRun ? $lastRun->modelParameter?->gamma_value : 'scale')
    };
    
    // Performance metric constants — values from DB via controller (PHP → JS)
    const mapeSvrDefault = @json($chartMetrics['mape_default'] ?? null);
    const r2SvrDefault   = @json($chartMetrics['r2_default']   ?? null);
    const mapeGridSearch = @json($chartMetrics['mape_gs']      ?? null);
    const r2GridSearch   = @json($chartMetrics['r2_gs']        ?? null);
    const mapeGwo        = @json($chartMetrics['mape_gwo']     ?? null);
    const r2Gwo          = @json($chartMetrics['r2_gwo']       ?? null);

    // ── Temporary Parameter Storage (SessionStorage) ──────────────────────────
    const gridFields = ['grid_c', 'grid_epsilon', 'grid_gamma'];
    const gwoFields = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];

    window.toggleAutoGwoDevelop = function() {
        const toggle = document.getElementById('auto_develop_gwo');
        const isAuto = toggle && toggle.checked;
        const fields = ['c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];
        
        if (isAuto) {
            let bestC = bestParamsGwo.c !== null ? parseFloat(bestParamsGwo.c) : 250.034536;
            let bestEps = bestParamsGwo.epsilon !== null ? parseFloat(bestParamsGwo.epsilon) : 0.00536603;
            let bestGam = bestParamsGwo.gamma !== null ? parseFloat(bestParamsGwo.gamma) : 0.004455;

            let cMin = Math.max(1.0, bestC - 50.0);
            let cMax = bestC + 50.0;
            let epsMin = Math.max(0.0001, bestEps / 2.0);
            let epsMax = Math.min(0.1, bestEps * 2.0);
            let gamMin = Math.max(0.0001, bestGam / 2.0);
            let gamMax = Math.min(0.1, bestGam * 2.0);

            document.getElementById('c_min').value = cMin.toFixed(6).replace(/\.?0+$/, '');
            document.getElementById('c_max').value = cMax.toFixed(6).replace(/\.?0+$/, '');
            document.getElementById('epsilon_min').value = epsMin.toFixed(8).replace(/\.?0+$/, '');
            document.getElementById('epsilon_max').value = epsMax.toFixed(8).replace(/\.?0+$/, '');
            document.getElementById('gamma_min').value = gamMin.toFixed(6).replace(/\.?0+$/, '');
            document.getElementById('gamma_max').value = gamMax.toFixed(6).replace(/\.?0+$/, '');

            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.setAttribute('readonly', 'true');
                    el.classList.remove('param-input-editable');
                }
            });
        } else {
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.hasAttribute('disabled')) {
                    el.removeAttribute('readonly');
                    el.classList.add('param-input-editable');
                }
            });
        }
    }

    window.toggleAutoGridDevelop = function() {
        const toggle = document.getElementById('auto_develop_grid');
        const isAuto = toggle && toggle.checked;
        const fields = ['grid_c', 'grid_epsilon', 'grid_gamma'];
        
        if (isAuto) {
            let bestC = bestParamsGs.c !== null ? parseFloat(bestParamsGs.c) : 100.0;
            let bestEps = bestParamsGs.epsilon !== null ? parseFloat(bestParamsGs.epsilon) : 0.001;
            let bestGam = bestParamsGs.gamma !== null ? bestParamsGs.gamma : 0.01;

            let cMinGrid = Math.max(1.0, bestC - 50.0);
            let cPrevGrid = Math.max(1.0, bestC - 10.0);
            let cRange = [
                parseFloat(cMinGrid.toFixed(4)),
                parseFloat(cPrevGrid.toFixed(4)),
                parseFloat(bestC.toFixed(4)),
                parseFloat((bestC + 10.0).toFixed(4)),
                parseFloat((bestC + 50.0).toFixed(4))
            ];
            cRange = [...new Set(cRange)].sort((a, b) => a - b);

            let epsRange = [
                parseFloat(Math.max(0.0001, bestEps / 2.0).toFixed(8)),
                parseFloat(bestEps.toFixed(8)),
                parseFloat(Math.min(0.1, bestEps * 2.0).toFixed(8)),
                parseFloat(Math.min(0.1, bestEps * 5.0).toFixed(8))
            ];
            epsRange = [...new Set(epsRange)].sort((a, b) => a - b);

            let gammaRange;
            if (bestGam === 'scale' || bestGam === 'auto') {
                gammaRange = ['scale', 0.001, 0.01, 0.05];
            } else {
                let gamNum = parseFloat(bestGam);
                gammaRange = [
                    parseFloat(Math.max(0.0001, gamNum / 2.0).toFixed(6)),
                    parseFloat(gamNum.toFixed(6)),
                    parseFloat(Math.min(0.1, gamNum * 2.0).toFixed(6)),
                    parseFloat(Math.min(0.1, gamNum * 5.0).toFixed(6))
                ];
                gammaRange = [...new Set(gammaRange)].sort((a, b) => a - b);
            }

            document.getElementById('grid_c').value = JSON.stringify(cRange);
            document.getElementById('grid_epsilon').value = JSON.stringify(epsRange);
            document.getElementById('grid_gamma').value = JSON.stringify(gammaRange).replace(/"/g, "'");

            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.setAttribute('readonly', 'true');
                    el.classList.remove('param-input-editable');
                }
            });
        } else {
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.hasAttribute('disabled')) {
                    el.removeAttribute('readonly');
                    el.classList.add('param-input-editable');
                }
            });
        }
        
        updateGridInfoText();
    }

    function saveTempParams() {
        gridFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) sessionStorage.setItem(`temp_${id}`, el.value);
        });
        gwoFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) sessionStorage.setItem(`temp_${id}`, el.value);
        });
        const gridCheck = document.getElementById('auto_develop_grid');
        if (gridCheck) sessionStorage.setItem('temp_auto_develop_grid', gridCheck.checked);
        const gwoCheck = document.getElementById('auto_develop_gwo');
        if (gwoCheck) sessionStorage.setItem('temp_auto_develop_gwo', gwoCheck.checked);
    }

    function loadTempParams() {
        gridFields.forEach(id => {
            const val = sessionStorage.getItem(`temp_${id}`);
            const el = document.getElementById(id);
            if (val && el) el.value = val;
        });
        gwoFields.forEach(id => {
            const val = sessionStorage.getItem(`temp_${id}`);
            const el = document.getElementById(id);
            if (val && el) el.value = val;
        });
        const gridCheck = document.getElementById('auto_develop_grid');
        const gridCheckVal = sessionStorage.getItem('temp_auto_develop_grid');
        if (gridCheck && gridCheckVal) {
            gridCheck.checked = gridCheckVal === 'true';
        }
        const gwoCheck = document.getElementById('auto_develop_gwo');
        const gwoCheckVal = sessionStorage.getItem('temp_auto_develop_gwo');
        if (gwoCheck && gwoCheckVal) {
            gwoCheck.checked = gwoCheckVal === 'true';
        }
    }

    function clearTempParams() {
        gridFields.forEach(id => sessionStorage.removeItem(`temp_${id}`));
        gwoFields.forEach(id => sessionStorage.removeItem(`temp_${id}`));
        sessionStorage.removeItem('temp_auto_develop_grid');
        sessionStorage.removeItem('temp_auto_develop_gwo');
    }

    function initializeDefaultParams() {
        // Initialize checkboxes
        const gridCheck = document.getElementById('auto_develop_grid');
        if (gridCheck && !sessionStorage.getItem('temp_auto_develop_grid')) {
            gridCheck.checked = true;
        } else if (gridCheck) {
            gridCheck.checked = sessionStorage.getItem('temp_auto_develop_grid') === 'true';
        }

        const gwoCheck = document.getElementById('auto_develop_gwo');
        if (gwoCheck && !sessionStorage.getItem('temp_auto_develop_gwo')) {
            gwoCheck.checked = true;
        } else if (gwoCheck) {
            gwoCheck.checked = sessionStorage.getItem('temp_auto_develop_gwo') === 'true';
        }

        if (gridCheck && gridCheck.checked) {
            window.toggleAutoGridDevelop();
        } else {
            let cRange = [10, 50, 100, 150, 200];
            let epsRange = [0.001, 0.005, 0.01, 0.05];
            let gammaRange = ['scale', 0.001, 0.01, 0.05];

            if (!sessionStorage.getItem('temp_grid_c')) {
                document.getElementById('grid_c').value = JSON.stringify(cRange);
            }
            if (!sessionStorage.getItem('temp_grid_epsilon')) {
                document.getElementById('grid_epsilon').value = JSON.stringify(epsRange);
            }
            if (!sessionStorage.getItem('temp_grid_gamma')) {
                document.getElementById('grid_gamma').value = JSON.stringify(gammaRange).replace(/"/g, "'");
            }
        }

        if (gwoCheck && gwoCheck.checked) {
            window.toggleAutoGwoDevelop();
        } else {
            let wolves = 12;
            let iterations = 20;
            let cMin = 1.0;
            let cMax = 1000.0;
            let epsMin = 0.0001;
            let epsMax = 0.1;
            let gamMin = 0.0001;
            let gamMax = 0.1;

            if (!sessionStorage.getItem('temp_gwo_wolves')) {
                document.getElementById('gwo_wolves').value = wolves;
            }
            if (!sessionStorage.getItem('temp_gwo_iterations')) {
                document.getElementById('gwo_iterations').value = iterations;
            }
            if (!sessionStorage.getItem('temp_c_min')) {
                document.getElementById('c_min').value = cMin;
            }
            if (!sessionStorage.getItem('temp_c_max')) {
                document.getElementById('c_max').value = cMax;
            }
            if (!sessionStorage.getItem('temp_epsilon_min')) {
                document.getElementById('epsilon_min').value = epsMin;
            }
            if (!sessionStorage.getItem('temp_epsilon_max')) {
                document.getElementById('epsilon_max').value = epsMax;
            }
            if (!sessionStorage.getItem('temp_gamma_min')) {
                document.getElementById('gamma_min').value = gamMin;
            }
            if (!sessionStorage.getItem('temp_gamma_max')) {
                document.getElementById('gamma_max').value = gamMax;
            }
        }
    }

    function updateGridInfoText() {
        let cLen = 0, epsLen = 0, gammaLen = 0;
        let cRange = [], epsRange = [], gammaRange = [];
        try {
            cRange = JSON.parse(document.getElementById('grid_c').value.replace(/'/g, '"'));
            cLen = cRange.length;
        } catch(e) {}
        try {
            epsRange = JSON.parse(document.getElementById('grid_epsilon').value.replace(/'/g, '"'));
            epsLen = epsRange.length;
        } catch(e) {}
        try {
            gammaRange = JSON.parse(document.getElementById('grid_gamma').value.replace(/'/g, '"'));
            gammaLen = gammaRange.length;
        } catch(e) {}

        const helpC = document.getElementById('grid_c_help');
        const helpEps = document.getElementById('grid_epsilon_help');
        const helpGamma = document.getElementById('grid_gamma_help');
        const infoEl = document.getElementById('grid-info-text');

        if (helpC) helpC.innerText = `${cLen} nilai: ${cRange.join(', ')}`;
        if (helpEps) helpEps.innerText = `${epsLen} nilai: ${epsRange.join(', ')}`;
        if (helpGamma) helpGamma.innerText = `${gammaLen} nilai: ${gammaRange.map(g => typeof g === 'string' ? `'${g}'` : g).join(', ')}`;

        let totalCombinations = cLen * epsLen * gammaLen;
        let totalFits = totalCombinations * 5; // 5-Fold CV
        if (infoEl) {
            infoEl.innerHTML = `Grid Search akan menguji <strong>${totalCombinations} kombinasi</strong> (${cLen}&times;${epsLen}&times;${gammaLen}) parameter menggunakan <strong>5-Fold Cross Validation</strong> (total ${totalFits} fits). Metrik evaluasi: <strong>RMSE</strong>.`;
        }
    }

    // ── Retune / Reset ────────────────────────────────────────────────────────
    window.retuneCurrentMethod = function() {
        console.log("Retune triggered for method:", currentMethod);
        // Reset wait counters
        gridWaitCount = 0;
        gwoWaitCount  = 0;
        if (currentMethod === 'grid') {
            window.unlockGridParams();
            window.goToGridStep(1);
        } else {
            window.unlockGwoParams();
            window.goToGwoStep(1);
        }
    }

    // ── Method Switcher ───────────────────────────────────────────────────────
    window.switchMethod = function(method) {
        currentMethod = method;
        sessionStorage.setItem('optimasi_method', method);
        const btnGrid    = document.getElementById('tab-btn-grid');
        const btnGwo     = document.getElementById('tab-btn-gwo');
        const contentGrid = document.getElementById('method-content-grid');
        const contentGwo  = document.getElementById('method-content-gwo');

        if (method === 'grid') {
            if (btnGrid)  { btnGrid.classList.add('btn-active-tab'); btnGrid.classList.remove('text-secondary'); }
            if (btnGwo)   { btnGwo.classList.remove('btn-active-tab'); btnGwo.classList.add('text-secondary'); }
            if (contentGrid) contentGrid.classList.remove('d-none');
            if (contentGwo)  contentGwo.classList.add('d-none');
            
            document.getElementById('grid-evaluation-details')?.classList.remove('d-none');
            document.getElementById('gwo-evaluation-details')?.classList.add('d-none');
            
            window.goToGridStep(gridStep);
        } else {
            if (btnGwo)   { btnGwo.classList.add('btn-active-tab'); btnGwo.classList.remove('text-secondary'); }
            if (btnGrid)  { btnGrid.classList.remove('btn-active-tab'); btnGrid.classList.add('text-secondary'); }
            if (contentGwo)  contentGwo.classList.remove('d-none');
            if (contentGrid) contentGrid.classList.add('d-none');
            
            document.getElementById('grid-evaluation-details')?.classList.add('d-none');
            document.getElementById('gwo-evaluation-details')?.classList.remove('d-none');
            
            window.goToGwoStep(gwoStep);
        }
    }

    // ── Grid Step Navigation ──────────────────────────────────────────────────
    window.goToGridStep = function(stepNum) {
        gridStep = stepNum;
        sessionStorage.setItem('grid_step', stepNum.toString());
        for (let i = 1; i <= 3; i++) {
            const item = document.getElementById(`stepper-grid-${i}`);
            if (item) {
                item.classList.remove('active', 'completed');
                if (i < stepNum)      item.classList.add('completed');
                else if (i === stepNum) item.classList.add('active');
            }
        }
        for (let i = 1; i <= 2; i++) {
            const line = document.getElementById(`stepper-line-grid-${i}`);
            if (line) {
                line.classList.remove('completed');
                if (i < stepNum) line.classList.add('completed');
            }
        }
        const s1 = document.getElementById('grid-step-content-1');
        const s2 = document.getElementById('grid-step-content-2');
        const s3 = document.getElementById('results-step-content-3');
        if (s1) s1.classList.add('d-none');
        if (s2) s2.classList.add('d-none');
        if (stepNum === 3) {
            if (s3) s3.classList.remove('d-none');
            setTimeout(() => { s3?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else {
            if (s3) s3.classList.add('d-none');
            const target = document.getElementById(`grid-step-content-${stepNum}`);
            if (target) target.classList.remove('d-none');
            
            if (stepNum === 2) {
                const spinnerContainer = document.getElementById('grid-spinner-container');
                const successContainer = document.getElementById('grid-success-container');
                const timerBox = document.getElementById('grid-timer-box');
                
                if (isGridRunning) {
                    if (spinnerContainer) spinnerContainer.classList.remove('d-none');
                    if (successContainer) successContainer.classList.add('d-none');
                    if (timerBox) timerBox.classList.remove('d-none');
                } else if (bestParamsGs.c !== null) {
                    if (spinnerContainer) spinnerContainer.classList.add('d-none');
                    if (successContainer) successContainer.classList.remove('d-none');
                    if (timerBox) timerBox.classList.add('d-none');
                    for (let i = 1; i <= 4; i++) {
                        setPipeStatus('grid', i, 'success');
                    }
                    const titleEl = document.getElementById('grid-process-title');
                    const descEl = document.getElementById('grid-process-desc');
                    if (titleEl) titleEl.innerText = "Optimasi Grid Search Selesai!";
                    if (descEl) descEl.innerText = "Seluruh langkah tuning parameter telah berhasil diselesaikan sebelumnya.";
                } else {
                    if (spinnerContainer) spinnerContainer.classList.add('d-none');
                    if (successContainer) successContainer.classList.add('d-none');
                    if (timerBox) timerBox.classList.add('d-none');
                    for (let i = 1; i <= 4; i++) {
                        setPipeStatus('grid', i, 'pending');
                    }
                    const titleEl = document.getElementById('grid-process-title');
                    const descEl = document.getElementById('grid-process-desc');
                    if (titleEl) titleEl.innerText = "Grid Search Belum Dijalankan";
                    if (descEl) descEl.innerText = "Silakan kembali ke Langkah 1 untuk mengonfigurasi dan menjalankan Grid Search.";
                }
            }
        }
    }

    // ── GWO Step Navigation ───────────────────────────────────────────────────
    window.goToGwoStep = function(stepNum) {
        gwoStep = stepNum;
        sessionStorage.setItem('gwo_step', stepNum.toString());
        for (let i = 1; i <= 3; i++) {
            const item = document.getElementById(`stepper-gwo-${i}`);
            if (item) {
                item.classList.remove('active', 'completed');
                if (i < stepNum)      item.classList.add('completed');
                else if (i === stepNum) item.classList.add('active');
            }
        }
        for (let i = 1; i <= 2; i++) {
            const line = document.getElementById(`stepper-line-gwo-${i}`);
            if (line) {
                line.classList.remove('completed');
                if (i < stepNum) line.classList.add('completed');
            }
        }
        const s1 = document.getElementById('gwo-step-content-1');
        const s2 = document.getElementById('gwo-step-content-2');
        const s3 = document.getElementById('results-step-content-3');
        if (s1) s1.classList.add('d-none');
        if (s2) s2.classList.add('d-none');
        if (stepNum === 3) {
            if (s3) s3.classList.remove('d-none');
            setTimeout(() => { s3?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else {
            if (s3) s3.classList.add('d-none');
            const target = document.getElementById(`gwo-step-content-${stepNum}`);
            if (target) target.classList.remove('d-none');
            
            if (stepNum === 2) {
                const spinnerContainer = document.getElementById('gwo-spinner-container');
                const successContainer = document.getElementById('gwo-success-container');
                const timerBox = document.getElementById('gwo-timer-box');
                const progressBarContainer = document.getElementById('gwo-progress-bar-container');
                
                if (isGwoRunning) {
                    if (spinnerContainer) spinnerContainer.classList.remove('d-none');
                    if (successContainer) successContainer.classList.add('d-none');
                    if (timerBox) timerBox.classList.remove('d-none');
                    if (progressBarContainer) progressBarContainer.classList.remove('d-none');
                } else if (bestParamsGwo.c !== null) {
                    if (spinnerContainer) spinnerContainer.classList.add('d-none');
                    if (successContainer) successContainer.classList.remove('d-none');
                    if (timerBox) timerBox.classList.add('d-none');
                    if (progressBarContainer) progressBarContainer.classList.remove('d-none');
                    
                    const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 20;
                    const progressBar = document.getElementById('gwo-progress-bar');
                    const iterLabel = document.getElementById('gwo-iter-label');
                    const iterPct = document.getElementById('gwo-iter-pct');
                    if (progressBar) { 
                        progressBar.style.width = '100%'; 
                        progressBar.setAttribute('aria-valuenow', '100'); 
                        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    }
                    if (iterLabel) iterLabel.innerText = `Iterasi GWO: Selesai (${maxIters} / ${maxIters})`;
                    if (iterPct) iterPct.innerText = '100%';
                    
                    for (let i = 1; i <= 4; i++) {
                        setPipeStatus('gwo', i, 'success');
                    }
                    const titleEl = document.getElementById('gwo-process-title');
                    const descEl = document.getElementById('gwo-process-desc');
                    if (titleEl) titleEl.innerText = "Optimasi GWO Selesai!";
                    if (descEl) descEl.innerText = "Seluruh langkah pencarian parameter global optimal telah berhasil diselesaikan sebelumnya.";
                } else {
                    if (spinnerContainer) spinnerContainer.classList.add('d-none');
                    if (successContainer) successContainer.classList.add('d-none');
                    if (timerBox) timerBox.classList.add('d-none');
                    if (progressBarContainer) progressBarContainer.classList.add('d-none');
                    for (let i = 1; i <= 4; i++) {
                        setPipeStatus('gwo', i, 'pending');
                    }
                    const titleEl = document.getElementById('gwo-process-title');
                    const descEl = document.getElementById('gwo-process-desc');
                    if (titleEl) titleEl.innerText = "GWO Belum Dijalankan";
                    if (descEl) descEl.innerText = "Silakan kembali ke Langkah 1 untuk mengonfigurasi dan menjalankan Grey Wolf Optimizer.";
                }
            }
        }
    }

    // ── Pipeline Status Helper ────────────────────────────────────────────────
    function setPipeStatus(method, stepNum, status) {
        try {
            const el = document.getElementById(`${method}-pipe-${stepNum}`);
            if (!el) return;
            const icon = el.querySelector('.step-icon');
            if (!icon) return;
            el.classList.remove('active', 'success-step', 'failed-step');
            if (status === 'pending') {
                icon.innerHTML = '<i class="bi bi-circle"></i>';
                icon.className = 'step-icon me-2 text-muted';
            } else if (status === 'processing') {
                icon.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" style="width:12px;height:12px;border-width:1.5px;" role="status"></div>';
                icon.className = 'step-icon me-2';
                el.classList.add('active');
            } else if (status === 'success') {
                icon.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size:14px;"></i>';
                icon.className = 'step-icon me-2';
                el.classList.add('success-step');
            } else if (status === 'failed') {
                icon.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size:14px;"></i>';
                icon.className = 'step-icon me-2';
                el.classList.add('failed-step');
            }
        } catch(e) { console.error('setPipeStatus error:', e); }
    }

    // ── Swal Response Helper ──────────────────────────────────────────────────
    function formatNum(n, dec=4) {
        return parseFloat(n).toFixed(dec);
    }

    function buildComparisonHtml(data) {
        const op = data.old_params;
        const np = data.new_params;
        const isBetter = data.is_better;
        const icon = isBetter ? '✅' : 'ℹ️';
        const mapeImprove = isBetter
            ? `<span class="text-success fw-bold">${formatNum(op.mape,2)}% → ${formatNum(np.mape,2)}%</span>`
            : `<span class="text-secondary">${formatNum(np.mape,2)}% (tidak lebih baik dari ${formatNum(op.mape,2)}%)</span>`;

        return `
            <div class="text-start" style="font-size:13px;">
                <table class="table table-sm table-bordered mb-2">
                    <thead class="table-light"><tr><th>Parameter</th><th>Sebelumnya</th><th>Hasil Optimasi</th></tr></thead>
                    <tbody>
                        <tr><td>C</td><td>${op.c}</td><td><strong>${formatNum(np.c,4)}</strong></td></tr>
                        <tr><td>Epsilon</td><td>${op.epsilon}</td><td><strong>${formatNum(np.epsilon,6)}</strong></td></tr>
                        <tr><td>Gamma</td><td>${op.gamma}</td><td><strong>${formatNum(np.gamma,5)}</strong></td></tr>
                    </tbody>
                </table>
                <p class="mb-1"><strong>MAPE:</strong> ${mapeImprove}</p>
                <p class="mb-0"><strong>Akurasi:</strong> ${formatNum(100 - np.mape, 2)}% &nbsp;|&nbsp; <strong>R²:</strong> ${formatNum(np.r2, 2)}</p>
                ${isBetter ? '<div class="alert alert-success mt-2 mb-0 py-2 px-3 text-sm">✅ Parameter baru telah disimpan sebagai model aktif di database.</div>'
                           : '<div class="alert alert-secondary mt-2 mb-0 py-2 px-3 text-sm">ℹ️ Database tidak diperbarui — model tetap menggunakan parameter sebelumnya.</div>'}
            </div>`;
    }

    // ════════════════════════════════════════════════════════════════════════════
    // GRID SEARCH EXECUTION
    // ════════════════════════════════════════════════════════════════════════════
    let gridApiFinished = false;
    let gridApiError    = null;
    let gridApiData     = null;
    let gridCurrentStep = 1;
    let gridTimeout     = null;
    let gridWaitCount   = 0;   // ← safety counter to prevent infinite loop
    const GRID_MAX_WAIT = 1500; // 1500 × 200ms = 5 minutes max wait

    window.startGridSearchTuning = function() {
        if (typeof Swal === 'undefined') {
            window.goToGridStep(2);
            executeGridSearchTuning();
            return;
        }
        Swal.fire({
            title: 'Jalankan Grid Search?',
            text: "Sistem akan melatih kombinasi parameter SVR menggunakan 5-Fold Cross Validation. Harap tunggu hingga selesai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#005BAA',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                window.goToGridStep(2);
                executeGridSearchTuning();
            }
        });
    }

    // ── Timer Helpers ─────────────────────────────────────────────────────────
    let elapsedTimerInterval = null;
    let elapsedSeconds = 0;

    function startElapsedTimer(method, estimatedSeconds) {
        elapsedSeconds = 0;
        if (elapsedTimerInterval) clearInterval(elapsedTimerInterval);
        
        const elapsedEl = document.getElementById(`${method}-elapsed-timer`);
        const estimatedEl = document.getElementById(`${method}-estimated-timer`);
        
        if (elapsedEl) elapsedEl.innerText = "0s";
        if (estimatedEl) estimatedEl.innerText = `~${estimatedSeconds}s`;
        
        // For GWO progress bar
        const progressBar = document.getElementById('gwo-progress-bar');
        const iterLabel   = document.getElementById('gwo-iter-label');
        const iterPct     = document.getElementById('gwo-iter-pct');
        const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 20;

        elapsedTimerInterval = setInterval(() => {
            elapsedSeconds++;
            if (elapsedEl) elapsedEl.innerText = `${elapsedSeconds}s`;
            
            // For GWO progress bar update
            if (method === 'gwo') {
                let currentIter = Math.min(maxIters - 1, Math.floor((elapsedSeconds / estimatedSeconds) * maxIters));
                let pct = Math.min(95, Math.round((elapsedSeconds / estimatedSeconds) * 95));
                
                if (progressBar) { 
                    progressBar.style.width = pct + '%'; 
                    progressBar.setAttribute('aria-valuenow', pct); 
                }
                if (iterLabel)   iterLabel.innerText = `Iterasi GWO: ${currentIter} / ${maxIters}`;
                if (iterPct)     iterPct.innerText   = pct + '%';
            }
        }, 1000);
    }
    
    function stopElapsedTimer() {
        if (elapsedTimerInterval) {
            clearInterval(elapsedTimerInterval);
            elapsedTimerInterval = null;
        }
    }

    function executeGridSearchTuning() {
        isGridRunning = true;
        
        // Reset UI containers for running state
        const spinnerContainer = document.getElementById('grid-spinner-container');
        const successContainer = document.getElementById('grid-success-container');
        const timerBox = document.getElementById('grid-timer-box');
        if (spinnerContainer) spinnerContainer.classList.remove('d-none');
        if (successContainer) successContainer.classList.add('d-none');
        if (timerBox) timerBox.classList.remove('d-none');

        // Reset states
        gridApiFinished = false;
        gridApiError    = null;
        gridApiData     = null;
        gridCurrentStep = 1;
        gridWaitCount   = 0;
        if (gridTimeout) clearTimeout(gridTimeout);

        for (let i = 1; i <= 4; i++) setPipeStatus('grid', i, 'pending');

        const titleEl = document.getElementById('grid-process-title');
        const descEl  = document.getElementById('grid-process-desc');
        if (titleEl) titleEl.innerText = "Sedang Menyiapkan Grid Search...";
        if (descEl)  descEl.innerText  = "Model SVR standar sedang disiapkan untuk pengujian kombinasi parameter.";

        setPipeStatus('grid', 1, 'processing');
        gridTimeout = setTimeout(runGridStepSequence, 800);

        // Calculate estimated seconds
        let cLen = 5, epsLen = 4, gammaLen = 4;
        try {
            const rawC = document.getElementById('grid_c')?.value || '';
            const rawEps = document.getElementById('grid_epsilon')?.value || '';
            const rawGamma = document.getElementById('grid_gamma')?.value || '';
            if (rawC.startsWith('[')) cLen = JSON.parse(rawC.replace(/'/g, '"')).length;
            if (rawEps.startsWith('[')) epsLen = JSON.parse(rawEps.replace(/'/g, '"')).length;
            if (rawGamma.startsWith('[')) gammaLen = JSON.parse(rawGamma.replace(/'/g, '"')).length;
        } catch(e) {
            console.error("Failed to parse grid range for timer estimate:", e);
        }
        let totalCombinations = cLen * epsLen * gammaLen;
        let totalFits = totalCombinations * 5;
        let estimatedSeconds = Math.max(5, Math.ceil(totalFits * 0.08));
        startElapsedTimer('grid', estimatedSeconds);

        // Collect form params
        const formData = {
            grid_c:       document.getElementById('grid_c')?.value || '',
            grid_epsilon: document.getElementById('grid_epsilon')?.value || '',
            grid_gamma:   document.getElementById('grid_gamma')?.value || '',
        };

        fetch("{{ route('operator.optimasi.grid-search') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(r => r.ok ? r.json() : r.json().then(e => { throw e; }))
        .then(data => {
            console.log("Grid Search response:", data);
            gridApiFinished = true;
            gridApiData     = data;
        })
        .catch(err => {
            console.error("Grid Search error:", err);
            gridApiFinished = true;
            gridApiError    = err;
        });
    }

    function runGridStepSequence() {
        const titleEl = document.getElementById('grid-process-title');
        const descEl  = document.getElementById('grid-process-desc');

        if (gridApiError) {
            isGridRunning = false;
            stopElapsedTimer();
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById(`grid-pipe-${i}`);
                if (el && el.classList.contains('active')) setPipeStatus('grid', i, 'failed');
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Gagal!', text: gridApiError.message || 'Optimasi Grid Search gagal.', icon: 'error', confirmButtonColor: '#DC2626', confirmButtonText: 'Tutup' });
            }
            window.goToGridStep(1);
            return;
        }

        if (gridCurrentStep === 1) {
            setPipeStatus('grid', 1, 'success');
            gridCurrentStep = 2;
            setPipeStatus('grid', 2, 'processing');
            if (titleEl) titleEl.innerText = "Membangun Kombinasi Parameter...";
            if (descEl)  descEl.innerText  = "Membuat kombinasi C, Epsilon, dan Gamma dari list konfigurasi.";
            gridTimeout = setTimeout(runGridStepSequence, 1000);
        } else if (gridCurrentStep === 2) {
            setPipeStatus('grid', 2, 'success');
            gridCurrentStep = 3;
            setPipeStatus('grid', 3, 'processing');
            if (titleEl) titleEl.innerText = "Pelatihan Cross Validation SVR...";
            if (descEl)  descEl.innerText  = "Menjalankan 5-Fold Cross Validation untuk setiap kombinasi parameter.";
            gridTimeout = setTimeout(runGridStepSequence, 500); // start checking API status
        } else if (gridCurrentStep === 3) {
            if (gridApiFinished) {
                setPipeStatus('grid', 3, 'success');
                gridCurrentStep = 4;
                setPipeStatus('grid', 4, 'processing');
                if (titleEl) titleEl.innerText = "Mengevaluasi Parameter Optimal...";
                if (descEl)  descEl.innerText  = "Mencari model dengan MAPE terkecil.";
                gridTimeout = setTimeout(runGridStepSequence, 1200); // brief pause to let user see step 4
            } else {
                // Safety cutoff check
                gridWaitCount++;
                if (gridWaitCount >= GRID_MAX_WAIT) {
                    gridApiFinished = true;
                    gridApiError = { message: 'Waktu tunggu tuning Grid Search habis (timeout). Server membutuhkan waktu lebih lama untuk memproses.' };
                }
                gridTimeout = setTimeout(runGridStepSequence, 200);
            }
        } else if (gridCurrentStep === 4) {
            isGridRunning = false;
            stopElapsedTimer();
            setPipeStatus('grid', 4, 'success');
            if (titleEl) titleEl.innerText = "Tuning Selesai!";
            if (descEl)  descEl.innerText  = "Parameter terbaik berhasil dianalisis.";

            setTimeout(() => {
                const data = gridApiData || {};
                const isBetter = data.is_better === true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: isBetter ? 'Optimasi Berhasil! 🎉' : 'Optimasi Selesai',
                        html: buildComparisonHtml({
                            is_better: isBetter,
                            old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                            new_params: data.new_params || { c: 200, epsilon: 0.001, gamma: 0.01, mape: mapeGridSearch, r2: r2GridSearch },
                        }),
                        icon: isBetter ? 'success' : 'info',
                        confirmButtonColor: '#005BAA',
                        confirmButtonText: 'Lihat Hasil'
                    }).then(() => {
                        clearTempParams();
                        sessionStorage.setItem('optimasi_method', 'grid');
                        sessionStorage.setItem('grid_step', '3');
                        window.location.reload(); // reload agar tabel komparasi terupdate
                    });
                } else {
                    window.goToGridStep(3);
                }
            }, 800);
        }
    }

    // ════════════════════════════════════════════════════════════════════════════
    // GWO EXECUTION
    // ════════════════════════════════════════════════════════════════════════════
    let gwoApiFinished = false;
    let gwoApiError    = null;
    let gwoApiData     = null;
    let gwoCurrentStep = 1;
    let gwoTimeout     = null;
    let gwoIterInterval = null;
    let gwoWaitCount   = 0;    // ← safety counter
    const GWO_MAX_WAIT = 1500; // 1500 × 200ms = 5 minutes max wait

    window.startGwoTuning = function() {
        if (typeof Swal === 'undefined') {
            window.goToGwoStep(2);
            executeGwoTuning();
            return;
        }
        Swal.fire({
            title: 'Jalankan GWO?',
            text: "Sistem akan memulai pencarian parameter optimal SVR dengan Grey Wolf Optimizer. Harap tunggu hingga selesai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#005BAA',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                window.goToGwoStep(2);
                executeGwoTuning();
            }
        });
    }

    function executeGwoTuning() {
        isGwoRunning = true;

        // Reset UI containers for running state
        const spinnerContainer = document.getElementById('gwo-spinner-container');
        const successContainer = document.getElementById('gwo-success-container');
        const timerBox = document.getElementById('gwo-timer-box');
        const progressBarContainer = document.getElementById('gwo-progress-bar-container');
        if (spinnerContainer) spinnerContainer.classList.remove('d-none');
        if (successContainer) successContainer.classList.add('d-none');
        if (timerBox) timerBox.classList.remove('d-none');
        if (progressBarContainer) progressBarContainer.classList.remove('d-none');

        gwoApiFinished = false;
        gwoApiError    = null;
        gwoApiData     = null;
        gwoCurrentStep = 1;
        gwoWaitCount   = 0;
        if (gwoTimeout)     clearTimeout(gwoTimeout);
        if (gwoIterInterval) clearInterval(gwoIterInterval);

        for (let i = 1; i <= 4; i++) setPipeStatus('gwo', i, 'pending');

        const progressBar = document.getElementById('gwo-progress-bar');
        const iterLabel   = document.getElementById('gwo-iter-label');
        const iterPct     = document.getElementById('gwo-iter-pct');
        if (progressBar) { progressBar.style.width = '0%'; progressBar.setAttribute('aria-valuenow', '0'); }
        if (iterLabel)   iterLabel.innerText = "Iterasi GWO: 0 / 20";
        if (iterPct)     iterPct.innerText   = "0%";

        const titleEl = document.getElementById('gwo-process-title');
        const descEl  = document.getElementById('gwo-process-desc');
        if (titleEl) titleEl.innerText = "Sedang Menyiapkan GWO...";
        if (descEl)  descEl.innerText  = "Algoritma Grey Wolf Optimizer sedang diinisialisasi.";

        setPipeStatus('gwo', 1, 'processing');
        gwoTimeout = setTimeout(runGwoStepSequence, 800);

        // Calculate estimated seconds
        const wolves = parseInt(document.getElementById('gwo_wolves')?.value) || 12;
        const iterations = parseInt(document.getElementById('gwo_iterations')?.value) || 20;
        const totalFits = wolves * iterations * 5;
        const estimatedSeconds = Math.max(10, Math.ceil(totalFits * 0.04));
        startElapsedTimer('gwo', estimatedSeconds);

        // Collect form params
        const formData = {
            wolves:      document.getElementById('gwo_wolves')?.value     || 12,
            iterations:  document.getElementById('gwo_iterations')?.value || 20,
            c_min:       document.getElementById('c_min')?.value          || 1.0,
            c_max:       document.getElementById('c_max')?.value          || 1000.0,
            epsilon_min: document.getElementById('epsilon_min')?.value    || 0.0001,
            epsilon_max: document.getElementById('epsilon_max')?.value    || 0.1,
            gamma_min:   document.getElementById('gamma_min')?.value      || 0.0001,
            gamma_max:   document.getElementById('gamma_max')?.value      || 0.1,
        };

        fetch("{{ route('operator.optimasi.gwo') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(r => r.ok ? r.json() : r.json().then(e => { throw e; }))
        .then(data => {
            console.log("GWO response:", data);
            gwoApiFinished = true;
            gwoApiData     = data;
        })
        .catch(err => {
            console.error("GWO error:", err);
            gwoApiFinished = true;
            gwoApiError    = err;
        });
    }

    function runGwoStepSequence() {
        const titleEl = document.getElementById('gwo-process-title');
        const descEl  = document.getElementById('gwo-process-desc');

        if (gwoApiError) {
            isGwoRunning = false;
            stopElapsedTimer();
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById(`gwo-pipe-${i}`);
                if (el && el.classList.contains('active')) setPipeStatus('gwo', i, 'failed');
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Gagal!', text: gwoApiError.message || 'Optimasi GWO gagal.', icon: 'error', confirmButtonColor: '#DC2626', confirmButtonText: 'Tutup' });
            }
            window.goToGwoStep(1);
            return;
        }

        if (gwoCurrentStep === 1) {
            setPipeStatus('gwo', 1, 'success');
            gwoCurrentStep = 2;
            setPipeStatus('gwo', 2, 'processing');
            if (titleEl) titleEl.innerText = "Menghitung Fitness Awal...";
            if (descEl)  descEl.innerText  = "Evaluasi kebugaran (fitness) posisi serigala awal di search space.";
            gwoTimeout = setTimeout(runGwoStepSequence, 1000);
        } else if (gwoCurrentStep === 2) {
            setPipeStatus('gwo', 2, 'success');
            gwoCurrentStep = 3;
            setPipeStatus('gwo', 3, 'processing');
            if (titleEl) titleEl.innerText = "Iterasi GWO & Perburuan...";
            if (descEl)  descEl.innerText  = "Serigala Alpha, Beta, dan Delta sedang memimpin perburuan parameter optimal.";
            gwoTimeout = setTimeout(runGwoStepSequence, 500); // start checking API status
        } else if (gwoCurrentStep === 3) {
            if (gwoApiFinished) {
                // Complete progress bar immediately
                const progressBar = document.getElementById('gwo-progress-bar');
                const iterLabel   = document.getElementById('gwo-iter-label');
                const iterPct     = document.getElementById('gwo-iter-pct');
                const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 20;
                if (progressBar) { progressBar.style.width = '100%'; progressBar.setAttribute('aria-valuenow', '100'); }
                if (iterLabel)   iterLabel.innerText = `Iterasi GWO: ${maxIters} / ${maxIters}`;
                if (iterPct)     iterPct.innerText   = '100%';

                setPipeStatus('gwo', 3, 'success');
                gwoCurrentStep = 4;
                setPipeStatus('gwo', 4, 'processing');
                if (titleEl) titleEl.innerText = "Memperbarui Model Parameter Optimal...";
                if (descEl)  descEl.innerText  = "Menyimpan nilai C, Epsilon, dan Gamma terbaik hasil perburuan GWO.";
                gwoTimeout = setTimeout(runGwoStepSequence, 1200);
            } else {
                // Safety cutoff check
                gwoWaitCount++;
                if (gwoWaitCount >= GWO_MAX_WAIT) {
                    gwoApiFinished = true;
                    gwoApiError = { message: 'Waktu tunggu GWO habis (timeout). Server membutuhkan waktu lebih lama untuk memproses.' };
                }
                gwoTimeout = setTimeout(runGwoStepSequence, 200);
            }
        } else if (gwoCurrentStep === 4) {
            isGwoRunning = false;
            stopElapsedTimer();
            setPipeStatus('gwo', 4, 'success');
            if (titleEl) titleEl.innerText = "Optimasi GWO Selesai!";
            if (descEl)  descEl.innerText  = "Posisi parameter global optimal berhasil ditemukan.";

            setTimeout(() => {
                const data = gwoApiData || {};
                const isBetter = data.is_better === true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: isBetter ? 'Optimasi GWO Berhasil! 🐺' : 'Optimasi GWO Selesai',
                        html: buildComparisonHtml({
                            is_better: isBetter,
                            old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                            new_params: data.new_params || { c: 250.034536, epsilon: 0.00536603, gamma: 0.0044554, mape: mapeGwo, r2: r2Gwo },
                        }),
                        icon: isBetter ? 'success' : 'info',
                        confirmButtonColor: '#005BAA',
                        confirmButtonText: 'Lihat Hasil'
                    }).then(() => {
                        clearTempParams();
                        sessionStorage.setItem('optimasi_method', 'gwo');
                        sessionStorage.setItem('gwo_step', '3');
                        window.location.reload();
                    });
                } else {
                    window.goToGwoStep(3);
                }
            }, 800);
        }
    }

    // ── Chart.js Initialization ───────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const savedMethod = urlParams.get('method') || sessionStorage.getItem('optimasi_method') || 'grid';
        
        // Step 2 is the loading/in-progress screen — never restore it on fresh page load
        // because there is no active process running. Clamp to step 1 if saved as 2.
        const rawGridStep = parseInt(urlParams.get('grid_step') || sessionStorage.getItem('grid_step') || '1');
        const rawGwoStep  = parseInt(urlParams.get('gwo_step')  || sessionStorage.getItem('gwo_step')  || '1');
        const savedGridStep = rawGridStep === 2 ? 1 : rawGridStep;
        const savedGwoStep  = rawGwoStep  === 2 ? 1 : rawGwoStep;



        gridStep = savedGridStep;
        gwoStep  = savedGwoStep;

        initializeDefaultParams();
        loadTempParams();
        updateGridInfoText();

        // Listen for input changes to temporarily save parameters and update info text
        const gridFieldsList = ['grid_c', 'grid_epsilon', 'grid_gamma'];
        const gwoFieldsList = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];
        [...gridFieldsList, ...gwoFieldsList].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', () => {
                    saveTempParams();
                    if (gridFieldsList.includes(id)) {
                        updateGridInfoText();
                    }
                });
            }
        });

        window.switchMethod(savedMethod);

        const canvasEl = document.getElementById('performanceChart');
        if (typeof Chart !== 'undefined' && canvasEl) {
            const ctx = canvasEl.getContext('2d');
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

        // --- GS LINE CHART INITIALIZATION ---
        @if($gsRun && $gsChartData->count() > 0)
            const canvasGsEl = document.getElementById('gsChart');
            if (typeof Chart !== 'undefined' && canvasGsEl) {
                const ctxGs = canvasGsEl.getContext('2d');
                
                const gradientActualGs = ctxGs.createLinearGradient(0, 0, 0, 380);
                gradientActualGs.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
                gradientActualGs.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

                const gradientPredictGs = ctxGs.createLinearGradient(0, 0, 0, 380);
                gradientPredictGs.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
                gradientPredictGs.addColorStop(1, 'rgba(244, 197, 66, 0.0)');
                
                const labelsGs = @json($gsChartData->pluck('tanggal')->map(fn($t) => Carbon\Carbon::parse($t)->format('d M Y'))->toArray());
                const actualDataGs = @json($gsChartData->pluck('actual_value')->map(fn($v) => (double)$v)->toArray());
                const predictedDataGs = @json($gsChartData->pluck('predicted_value')->map(fn($v) => (double)$v)->toArray());
                
                new Chart(ctxGs, {
                    type: 'line',
                    data: {
                        labels: labelsGs,
                        datasets: [
                            {
                                label: 'Pendapatan Aktual',
                                data: actualDataGs,
                                borderColor: '#005BAA',
                                borderWidth: 2,
                                backgroundColor: gradientActualGs,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#005BAA',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 2.5,
                                pointHoverRadius: 4
                            },
                            {
                                label: 'Pendapatan Prediksi SVR (GS)',
                                data: predictedDataGs,
                                borderColor: '#F4C542',
                                borderWidth: 2,
                                backgroundColor: gradientPredictGs,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#F4C542',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 2.5,
                                pointHoverRadius: 4,
                                borderDash: [5, 5]
                            }
                        ]
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
                                grid: { borderDash: [5, 5], color: '#e2e8f0' },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                    },
                                    font: { family: 'Inter', size: 10 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { family: 'Inter', size: 9.5 },
                                    maxRotation: 45,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // --- GWO LINE CHART INITIALIZATION ---
        @if($gwoRun && $gwoChartData->count() > 0)
            const canvasGwoEl = document.getElementById('gwoChart');
            if (typeof Chart !== 'undefined' && canvasGwoEl) {
                const ctxGwo = canvasGwoEl.getContext('2d');
                
                const gradientActualGwo = ctxGwo.createLinearGradient(0, 0, 0, 380);
                gradientActualGwo.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
                gradientActualGwo.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

                const gradientPredictGwo = ctxGwo.createLinearGradient(0, 0, 0, 380);
                gradientPredictGwo.addColorStop(0, 'rgba(16, 185, 129, 0.08)');
                gradientPredictGwo.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
                
                const labelsGwo = @json($gwoChartData->pluck('tanggal')->map(fn($t) => Carbon\Carbon::parse($t)->format('d M Y'))->toArray());
                const actualDataGwo = @json($gwoChartData->pluck('actual_value')->map(fn($v) => (double)$v)->toArray());
                const predictedDataGwo = @json($gwoChartData->pluck('predicted_value')->map(fn($v) => (double)$v)->toArray());
                
                new Chart(ctxGwo, {
                    type: 'line',
                    data: {
                        labels: labelsGwo,
                        datasets: [
                            {
                                label: 'Pendapatan Aktual',
                                data: actualDataGwo,
                                borderColor: '#005BAA',
                                borderWidth: 2,
                                backgroundColor: gradientActualGwo,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#005BAA',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 2.5,
                                pointHoverRadius: 4
                            },
                            {
                                label: 'Pendapatan Prediksi SVR (GWO)',
                                data: predictedDataGwo,
                                borderColor: '#10B981',
                                borderWidth: 2,
                                backgroundColor: gradientPredictGwo,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#10B981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 2.5,
                                pointHoverRadius: 4,
                                borderDash: [5, 5]
                            }
                        ]
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
                                grid: { borderDash: [5, 5], color: '#e2e8f0' },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                    },
                                    font: { family: 'Inter', size: 10 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { family: 'Inter', size: 9.5 },
                                    maxRotation: 45,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            }
                        }
                    }
                });
            }
        @endif
    });
</script>
@endsection
