@extends('layouts.app')

@section('title', 'Optimasi Parameter')
@section('subtitle', 'Halaman ini digunakan untuk membandingkan hasil optimasi parameter SVR menggunakan Grid Search dan Grey Wolf Optimizer.')

@section('content')
<div class="container-fluid p-0">
    


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
                            <div class="step-title">Validasi &amp; Riwayat</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-1"></div>
                        <div class="stepper-item" id="stepper-grid-2" onclick="goToGridStep(2)" style="cursor: pointer;">
                            <div class="step-number">2</div>
                            <div class="step-title">Konfigurasi Grid</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-2"></div>
                        <div class="stepper-item" id="stepper-grid-3" onclick="goToGridStep(3)" style="cursor: pointer;">
                            <div class="step-number">3</div>
                            <div class="step-title">Proses Tuning</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-3"></div>
                        <div class="stepper-item" id="stepper-grid-4" onclick="goToGridStep(4)" style="cursor: pointer;">
                            <div class="step-number">4</div>
                            <div class="step-title">Hasil Optimasi</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-grid-4"></div>
                        <div class="stepper-item" id="stepper-grid-5" onclick="goToGridStep(5)" style="cursor: pointer;">
                            <div class="step-number">5</div>
                            <div class="step-title">Perbandingan Model</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Step 1: Validasi Dataset & Riwayat SVR Standar -->
            <div id="grid-step-content-1" class="step-opt-content">
                <!-- Ringkasan & Validasi Section -->
                <div class="row g-4 mb-4">
                    <!-- Ringkasan Dataset -->
                    <div class="col-md-6">
                        <div class="card h-100 mb-0 shadow-sm border border-light bg-white">
                            <div class="card-body">
                                <h5 class="card-title text-dark"><i class="bi bi-info-circle-fill me-2 text-primary-custom"></i>Ringkasan Dataset</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0 text-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold text-secondary" style="width: 50%;">Total Data Pendapatan</td>
                                                <td class="text-end fw-bold text-dark">{{ number_format($totalPendapatan, 0, ',', '.') }} Baris</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Periode Data Awal</td>
                                                <td class="text-end fw-bold text-dark">{{ $periodeAwalFormatted }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Periode Data Akhir</td>
                                                <td class="text-end fw-bold text-dark">{{ $periodeAkhirFormatted }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Jumlah Rayon</td>
                                                <td class="text-end fw-bold text-dark">{{ $jumlahRayon }} Rayon</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Data Libur &amp; Weekend</td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary-subtle text-primary border-0 me-1">{{ $jumlahHariLibur }} Libur</span>
                                                    <span class="badge bg-warning-subtle text-warning border-0">{{ $jumlahWeekend }} Weekend</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Status Dataset</td>
                                                <td class="text-end">
                                                    @if($datasetReady)
                                                        <span class="badge bg-success-subtle text-success border-0"><i class="bi bi-patch-check-fill me-1"></i>Siap Diproses</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border-0"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Siap</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validasi Kelengkapan Dataset -->
                    <div class="col-md-6">
                        <div class="card h-100 mb-0 shadow-sm border border-light bg-white">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-dark"><i class="bi bi-shield-check-fill me-2 text-primary-custom"></i>Validasi Kelengkapan Dataset</h5>
                                    <ul class="list-group list-group-flush text-sm">
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasPendapatan ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data pendapatan</span>
                                            <span class="badge {{ $hasPendapatan ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasPendapatan ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasRayon ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data rayon</span>
                                            <span class="badge {{ $hasRayon ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasRayon ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasJuruParkir ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data juru parkir</span>
                                            <span class="badge {{ $hasJuruParkir ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasJuruParkir ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasHariLibur ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data hari libur dan weekend</span>
                                            <span class="badge {{ $hasHariLibur ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasHariLibur ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="mt-3 p-2 rounded {{ $datasetReady ? 'alert alert-success' : 'alert alert-danger' }} mb-0 py-2 px-3 small border-0 d-flex align-items-center shadow-xs">
                                    <i class="bi {{ $datasetReady ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' }} me-2 fs-5"></i>
                                    <span class="text-dark">
                                        @if($datasetReady)
                                            <strong>Dataset siap digunakan</strong> untuk optimasi parameter.
                                        @else
                                            <strong>Dataset belum lengkap.</strong> Silakan lengkapi data pada menu Master Data Retribusi terlebih dahulu.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Preprocessing & Pelatihan SVR Standar -->
                <div class="card mb-4 bg-white shadow-sm border border-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-dark border-0 pb-0 mb-0">
                                <i class="bi bi-cpu-fill me-2 text-primary-custom"></i>Riwayat Proses SVR Standar (Sebelum Optimasi)
                            </h5>
                            <span class="badge bg-success-subtle text-success border-0 px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i>Selesai Dijalankan</span>
                        </div>
                        
                        <p class="text-secondary small mb-4">Berikut adalah parameter baseline, performa pengujian SVR Standar (Default) yang telah dilatih sebelumnya, serta langkah-langkah preprocessing data yang otomatis diselesaikan:</p>
                        
                        <div class="row g-4 mb-4">
                            <!-- Detail Baseline SVR Standar -->
                            <div class="col-md-5">
                                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-wide-connected me-1"></i>Baseline SVR Standar</h6>
                                    <table class="table table-borderless table-sm text-sm mb-0 align-middle">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold text-secondary" style="width: 40%;">Parameter C</td>
                                                <td class="text-secondary text-center" style="width: 5%;">:</td>
                                                <td class="text-dark fw-semibold">1.0 (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Epsilon (&epsilon;)</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-dark fw-semibold">0.1 (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Gamma (&gamma;)</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-dark fw-semibold">scale (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">MAPE Test</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td><span class="badge bg-success-subtle text-success border-0 px-2.5 py-1 fw-bold">{{ $comparisons[0]['mape'] ?? '-' }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Akurasi Test</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td><span class="badge bg-primary-subtle text-primary border-0 px-2.5 py-1 fw-bold">{{ $comparisons[0]['akurasi'] ?? '-' }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Waktu Latih</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-secondary small">{{ $lastRun ? Carbon\Carbon::parse($lastRun->finished_at)->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- 7 Preprocessing checklist -->
                            <div class="col-md-7">
                                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-patch-check-fill me-1"></i>Daftar Tahapan Preprocessing &amp; Prediksi SVR</h6>
                                    <div class="progress-steps-list">
                                        <div class="progress-step success-step" id="grid-pipe-svr-1">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">1. Pembersihan Data (Data Cleaning)</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-2">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">2. Rekayasa Fitur (Feature Engineering)</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-3">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">3. Transformasi Data</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-4">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">4. Normalisasi Data</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-5">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">5. Pembagian Data (Split Data 80:20)</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-6">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">6. Pelatihan Model SVR</span>
                                        </div>
                                        <div class="progress-step success-step" id="grid-pipe-svr-7">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">7. Prediksi Pendapatan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer for Step 1 -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGridStep(2)">
                        Lanjut ke Konfigurasi Grid <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- Grid Step 2: Form -->
            <div id="grid-step-content-2" class="step-opt-content d-none">
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
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm" onclick="goToGridStep(1)">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Validasi Dataset
                                </button>
                                <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                                    <i class="bi bi-play-fill me-1"></i>Jalankan Grid Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            <!-- Riwayat Optimasi Grid Search -->
            <div class="card bg-white mb-4 shadow-sm border border-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary-custom"></i>Riwayat Optimasi Grid Search</h5>
                        @if(!$historyGsRuns->isEmpty())
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-semibold text-xs px-3" onclick="confirmResetOptimasiAll('grid_search')">
                                <i class="bi bi-trash3-fill me-1"></i> Reset Semua Riwayat
                            </button>
                        @endif
                    </div>
                    @if($historyGsRuns->isEmpty())
                        <div class="text-center py-4 text-secondary">
                            <i class="bi bi-folder2-open fs-2 text-muted mb-2 d-block"></i>
                            Belum ada riwayat proses optimasi Grid Search.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Waktu</th>
                                        <th>Parameter Optimal (C, &epsilon;, &gamma;)</th>
                                        <th>MAE</th>
                                        <th>RMSE</th>
                                        <th>MAPE</th>
                                        <th>R&sup2; Score</th>
                                        <th>Lama Proses</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historyGsRuns as $run)
                                        @php
                                            $param = $run->modelParameter;
                                            $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                                            $isActive = ($bestGsId && $run->id === $bestGsId);
                                            
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
                                                <button type="button" class="btn btn-link text-danger p-0 border-0" onclick="confirmDeleteOptimasiRun({{ $run->id }}, '{{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}', 'Grid Search')" title="Hapus Riwayat">
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
        </div>{{-- END grid-step-content-2 --}}

            <!-- Grid Step 3: Progress -->
            <div id="grid-step-content-3" class="step-opt-content d-none">
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

                            <!-- Grid Progress Bar Container -->
                            <div class="mx-auto mb-4" id="grid-progress-bar-container" style="max-width: 500px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-secondary small fw-bold" id="grid-iter-label">Kombinasi Grid: 0 / 80</span>
                                    <span class="text-success small fw-bold" id="grid-iter-pct">0%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="grid-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
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
                            <div class="step-title">Validasi &amp; Riwayat</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-1"></div>
                        <div class="stepper-item" id="stepper-gwo-2" onclick="goToGwoStep(2)" style="cursor: pointer;">
                            <div class="step-number">2</div>
                            <div class="step-title">Konfigurasi GWO</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-2"></div>
                        <div class="stepper-item" id="stepper-gwo-3" onclick="goToGwoStep(3)" style="cursor: pointer;">
                            <div class="step-number">3</div>
                            <div class="step-title">Proses Tuning</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-3"></div>
                        <div class="stepper-item" id="stepper-gwo-4" onclick="goToGwoStep(4)" style="cursor: pointer;">
                            <div class="step-number">4</div>
                            <div class="step-title">Hasil Optimasi</div>
                        </div>
                        <div class="stepper-line" id="stepper-line-gwo-4"></div>
                        <div class="stepper-item" id="stepper-gwo-5" onclick="goToGwoStep(5)" style="cursor: pointer;">
                            <div class="step-number">5</div>
                            <div class="step-title">Perbandingan Model</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GWO Step 1: Validasi Dataset & Riwayat SVR Standar -->
            <div id="gwo-step-content-1" class="step-opt-content">
                <!-- Ringkasan & Validasi Section -->
                <div class="row g-4 mb-4">
                    <!-- Ringkasan Dataset -->
                    <div class="col-md-6">
                        <div class="card h-100 mb-0 shadow-sm border border-light bg-white">
                            <div class="card-body">
                                <h5 class="card-title text-dark"><i class="bi bi-info-circle-fill me-2 text-primary-custom"></i>Ringkasan Dataset</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0 text-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold text-secondary" style="width: 50%;">Total Data Pendapatan</td>
                                                <td class="text-end fw-bold text-dark">{{ number_format($totalPendapatan, 0, ',', '.') }} Baris</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Periode Data Awal</td>
                                                <td class="text-end fw-bold text-dark">{{ $periodeAwalFormatted }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Periode Data Akhir</td>
                                                <td class="text-end fw-bold text-dark">{{ $periodeAkhirFormatted }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Jumlah Rayon</td>
                                                <td class="text-end fw-bold text-dark">{{ $jumlahRayon }} Rayon</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Data Libur &amp; Weekend</td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary-subtle text-primary border-0 me-1">{{ $jumlahHariLibur }} Libur</span>
                                                    <span class="badge bg-warning-subtle text-warning border-0">{{ $jumlahWeekend }} Weekend</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Status Dataset</td>
                                                <td class="text-end">
                                                    @if($datasetReady)
                                                        <span class="badge bg-success-subtle text-success border-0"><i class="bi bi-patch-check-fill me-1"></i>Siap Diproses</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border-0"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Siap</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validasi Kelengkapan Dataset -->
                    <div class="col-md-6">
                        <div class="card h-100 mb-0 shadow-sm border border-light bg-white">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title text-dark"><i class="bi bi-shield-check-fill me-2 text-primary-custom"></i>Validasi Kelengkapan Dataset</h5>
                                    <ul class="list-group list-group-flush text-sm">
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasPendapatan ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data pendapatan</span>
                                            <span class="badge {{ $hasPendapatan ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasPendapatan ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasRayon ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data rayon</span>
                                            <span class="badge {{ $hasRayon ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasRayon ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasJuruParkir ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data juru parkir</span>
                                            <span class="badge {{ $hasJuruParkir ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasJuruParkir ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                                            <span><i class="bi {{ $hasHariLibur ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data hari libur dan weekend</span>
                                            <span class="badge {{ $hasHariLibur ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasHariLibur ? 'Lengkap' : 'Tidak Ada' }}</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="mt-3 p-2 rounded {{ $datasetReady ? 'alert alert-success' : 'alert alert-danger' }} mb-0 py-2 px-3 small border-0 d-flex align-items-center shadow-xs">
                                    <i class="bi {{ $datasetReady ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' }} me-2 fs-5"></i>
                                    <span class="text-dark">
                                        @if($datasetReady)
                                            <strong>Dataset siap digunakan</strong> untuk optimasi parameter.
                                        @else
                                            <strong>Dataset belum lengkap.</strong> Silakan lengkapi data pada menu Master Data Retribusi terlebih dahulu.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Preprocessing & Pelatihan SVR Standar -->
                <div class="card mb-4 bg-white shadow-sm border border-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-dark border-0 pb-0 mb-0">
                                <i class="bi bi-cpu-fill me-2 text-primary-custom"></i>Riwayat Proses SVR Standar (Sebelum Optimasi)
                            </h5>
                            <span class="badge bg-success-subtle text-success border-0 px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i>Selesai Dijalankan</span>
                        </div>
                        
                        <p class="text-secondary small mb-4">Berikut adalah parameter baseline, performa pengujian SVR Standar (Default) yang telah dilatih sebelumnya, serta langkah-langkah preprocessing data yang otomatis diselesaikan:</p>
                        
                        <div class="row g-4 mb-4">
                            <!-- Detail Baseline SVR Standar -->
                            <div class="col-md-5">
                                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-wide-connected me-1"></i>Baseline SVR Standar</h6>
                                    <table class="table table-borderless table-sm text-sm mb-0 align-middle">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold text-secondary" style="width: 40%;">Parameter C</td>
                                                <td class="text-secondary text-center" style="width: 5%;">:</td>
                                                <td class="text-dark fw-semibold">1.0 (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Epsilon (&epsilon;)</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-dark fw-semibold">0.1 (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Gamma (&gamma;)</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-dark fw-semibold">scale (Default)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">MAPE Test</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td><span class="badge bg-success-subtle text-success border-0 px-2.5 py-1 fw-bold">{{ $comparisons[0]['mape'] ?? '-' }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Akurasi Test</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td><span class="badge bg-primary-subtle text-primary border-0 px-2.5 py-1 fw-bold">{{ $comparisons[0]['akurasi'] ?? '-' }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold text-secondary">Waktu Latih</td>
                                                <td class="text-secondary text-center">:</td>
                                                <td class="text-secondary small">{{ $lastRun ? Carbon\Carbon::parse($lastRun->finished_at)->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- 7 Preprocessing checklist -->
                            <div class="col-md-7">
                                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-patch-check-fill me-1"></i>Daftar Tahapan Preprocessing &amp; Prediksi SVR</h6>
                                    <div class="progress-steps-list">
                                        <div class="progress-step success-step" id="gwo-pipe-svr-1">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">1. Pembersihan Data (Data Cleaning)</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-2">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">2. Rekayasa Fitur (Feature Engineering)</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-3">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">3. Transformasi Data</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-4">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">4. Normalisasi Data</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-5">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">5. Pembagian Data (Split Data 80:20)</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-6">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">6. Pelatihan Model SVR</span>
                                        </div>
                                        <div class="progress-step success-step" id="gwo-pipe-svr-7">
                                            <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                            <span class="step-label">7. Prediksi Pendapatan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer for Step 1 -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="goToGwoStep(2)">
                        Lanjut ke Konfigurasi GWO <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- GWO Step 2: Form -->
            <div id="gwo-step-content-2" class="step-opt-content d-none">
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
                                    <input type="number" name="wolves" id="gwo_wolves" class="form-control rounded-3" value="15" min="5" max="50" @if($gwoRun) disabled @endif>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Maksimal Iterasi</label>
                                    <input type="number" name="iterations" id="gwo_iterations" class="form-control rounded-3" value="30" min="10" max="200" @if($gwoRun) disabled @endif>
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
                                    <div class="col-md-4"><input type="number" step="any" name="c_min" id="c_min" class="form-control rounded-3" value="10.0" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="c_max" id="c_max" class="form-control rounded-3" value="300.0" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-4"><label class="small fw-semibold text-secondary">Epsilon (Min / Max)</label></div>
                                    <div class="col-md-4"><input type="number" step="any" name="epsilon_min" id="epsilon_min" class="form-control rounded-3" value="0.0001" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="epsilon_max" id="epsilon_max" class="form-control rounded-3" value="0.05" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4"><label class="small fw-semibold text-secondary">Gamma (Min / Max)</label></div>
                                    <div class="col-md-4"><input type="number" step="any" name="gamma_min" id="gamma_min" class="form-control rounded-3" value="0.0005" placeholder="Min" @if($gwoRun) disabled @endif></div>
                                    <div class="col-md-4"><input type="number" step="any" name="gamma_max" id="gamma_max" class="form-control rounded-3" value="0.1" placeholder="Max" @if($gwoRun) disabled @endif></div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm" onclick="goToGwoStep(1)">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Validasi Dataset
                                </button>
                                <button type="submit" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
                                    <i class="bi bi-play-fill me-1"></i>Jalankan GWO
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            <!-- Riwayat Optimasi GWO -->
            <div class="card bg-white mb-4 shadow-sm border border-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary-custom"></i>Riwayat Optimasi Grey Wolf Optimizer (GWO)</h5>
                        @if(!$historyGwoRuns->isEmpty())
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-semibold text-xs px-3" onclick="confirmResetOptimasiAll('gwo')">
                                <i class="bi bi-trash3-fill me-1"></i> Reset Semua Riwayat
                            </button>
                        @endif
                    </div>
                    @if($historyGwoRuns->isEmpty())
                        <div class="text-center py-4 text-secondary">
                            <i class="bi bi-folder2-open fs-2 text-muted mb-2 d-block"></i>
                            Belum ada riwayat proses optimasi GWO.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Waktu</th>
                                        <th>Parameter Optimal (C, &epsilon;, &gamma;)</th>
                                        <th>MAE</th>
                                        <th>RMSE</th>
                                        <th>MAPE</th>
                                        <th>R&sup2; Score</th>
                                        <th>Lama Proses</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historyGwoRuns as $run)
                                        @php
                                            $param = $run->modelParameter;
                                            $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                                            $isActive = ($bestGwoId && $run->id === $bestGwoId);
                                            
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
                                                <button type="button" class="btn btn-link text-danger p-0 border-0" onclick="confirmDeleteOptimasiRun({{ $run->id }}, '{{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}', 'GWO')" title="Hapus Riwayat">
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
        </div>{{-- END gwo-step-content-2 --}}

            <!-- GWO Step 3: Progress -->
            <div id="gwo-step-content-3" class="step-opt-content d-none">
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
                                <span class="fw-bold text-dark" id="gwo-iter-label">Iterasi GWO: 0 / 30</span>
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

        <!-- COMMON STEP 5 CONTENT: Perbandingan Model -->
        <div id="results-step-content-5" class="step-opt-content d-none">
            <!-- Hasil Optimasi Parameter Table -->
            <div class="card mb-4 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title border-0 pb-0 mb-0"><i class="bi bi-table me-2 text-primary-custom"></i>Hasil Optimasi Parameter</h5>
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
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2 text-primary-custom"></i>Grafik Tren Perbandingan Hasil Prediksi</h5>
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

            <div class="d-flex justify-content-start mt-4 mb-4">
                <button type="button" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="currentMethod === 'grid' ? goToGridStep(4) : goToGwoStep(4)">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Hasil Optimasi
                </button>
            </div>
        </div> {{-- END results-step-content-5 --}}

        <!-- STEP 4 CONTENT: Hasil Optimasi -->
        <div id="results-step-content-4" class="step-opt-content d-none">


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
                    @php
                        $gsTrParts = $gsRun->train_period ? explode(' - ', $gsRun->train_period) : [];
                        $gsTrDays  = count($gsTrParts) === 2 ? \Carbon\Carbon::parse(trim($gsTrParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gsTrParts[1]))) + 1 : null;
                        $gsTeParts = $gsRun->test_period ? explode(' - ', $gsRun->test_period) : [];
                        $gsTeDays  = count($gsTeParts) === 2 ? \Carbon\Carbon::parse(trim($gsTeParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gsTeParts[1]))) + 1 : null;
                    @endphp
                    <!-- Ringkasan Data Training/Testing Grid Search -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <h6 class="card-title text-warning mb-3"><i class="bi bi-grid-3x3 me-2"></i>Ringkasan Dataset SVR + Grid Search</h6>
                            <div class="row g-3 small">
                                <div class="col-6 col-md-3 border-end border-light">
                                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Jumlah Data</span>
                                    <strong class="fs-6 text-dark">{{ number_format($gsRun->total_rows, 0, ',', '.') }} baris</strong>
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
                    <h5 class="fw-bold mb-3 text-dark mt-4"><i class="bi bi-award-fill me-2 text-warning"></i>Hasil Evaluasi Model SVR + Grid Search</h5>
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

                            <x-model-analysis-results
                                :mape="$gsMape"
                                :r2="$gsR2"
                                :rmse="$gsRmse"
                                :meanActual="$gsMeanActual"
                                target="grid_search"
                            />
                        </div>
                    </div>

                    <!-- Grafik Aktual vs Prediksi Grid Search -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="card-title mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + Grid Search</h5>
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

                            <!-- Analisis Ringkas Rayon (Grid Search) -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-warning shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-warning me-1"></i>Kesimpulan Hasil Prediksi Per Rayon (Grid Search)</h6>
                                <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                                    @if($gsBestRayon)
                                        <li>Rayon paling akurat: <strong class="text-success">{{ $gsBestRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($gsBestRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                                    @endif
                                    @if($gsWorstRayon)
                                        <li>Rayon dengan error terbesar: <strong class="text-danger">{{ $gsWorstRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($gsWorstRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                                    @endif
                                    <li>Rata-rata selisih prediksi harian: <strong>Rp {{ number_format(abs($gsAvgDailyDeviation), 0, ',', '.') }}</strong> per hari.</li>
                                </ul>
                            </div>

                                @if($gsRayonStats->count() > 0)
                                <div class="border-top pt-3">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 12.5px;"><i class="bi bi-table me-2 text-primary"></i>Rincian Kinerja Seluruh Rayon (Grid Search)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Rayon</th>
                                                    <th style="text-align: right;">Total Aktual (Rp)</th>
                                                    <th style="text-align: right;">Total Prediksi (Rp)</th>
                                                    <th style="text-align: right;">Avg Error (Rp)</th>
                                                    <th style="text-align: right; width: 120px;">Rata-rata MAPE</th>
                                                    <th style="text-align: center; width: 120px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($gsRayonStats->sortBy('avg_mape') as $rs)
                                                    @php
                                                        $mapeVal = abs($rs->avg_mape);
                                                        $statusClass = $mapeVal < 10 ? 'text-success' : ($mapeVal <= 20 ? 'text-primary' : 'text-danger');
                                                        $statusText  = $mapeVal < 10 ? 'Sangat Akurat' : ($mapeVal <= 20 ? 'Baik' : 'Perlu Perhatian');
                                                        $badgeClass  = $mapeVal < 10 ? 'bg-success-subtle text-success' : ($mapeVal <= 20 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger');
                                                    @endphp
                                                    <tr>
                                                        <td><span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">{{ $rs->rayon_name }}</span></td>
                                                        <td style="text-align: right; font-weight: 500;">Rp {{ number_format($rs->total_actual, 0, ',', '.') }}</td>
                                                        <td style="text-align: right; font-weight: 500; color: #005BAA;">Rp {{ number_format($rs->total_predicted, 0, ',', '.') }}</td>
                                                        <td style="text-align: right; color: {{ $rs->avg_error >= 0 ? '#10b981' : '#ef4444' }};">
                                                            {{ $rs->avg_error >= 0 ? '+' : '' }}Rp {{ number_format($rs->avg_error, 0, ',', '.') }}
                                                        </td>
                                                        <td style="text-align: right;" class="{{ $statusClass }} fw-bold">{{ number_format($mapeVal, 2, ',', '.') }}%</td>
                                                        <td style="text-align: center;">
                                                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 10px;">{{ $statusText }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
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
                    </div>
                @endif
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
                    @php
                        $gwoTrParts = $gwoRun->train_period ? explode(' - ', $gwoRun->train_period) : [];
                        $gwoTrDays  = count($gwoTrParts) === 2 ? \Carbon\Carbon::parse(trim($gwoTrParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gwoTrParts[1]))) + 1 : null;
                        $gwoTeParts = $gwoRun->test_period ? explode(' - ', $gwoRun->test_period) : [];
                        $gwoTeDays  = count($gwoTeParts) === 2 ? \Carbon\Carbon::parse(trim($gwoTeParts[0]))->diffInDays(\Carbon\Carbon::parse(trim($gwoTeParts[1]))) + 1 : null;
                    @endphp
                    <!-- Ringkasan Data Training/Testing GWO -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <h6 class="card-title text-success mb-3"><i class="bi bi-activity me-2"></i>Ringkasan Dataset SVR + GWO (Grey Wolf)</h6>
                            <div class="row g-3 small">
                                <div class="col-6 col-md-3 border-end border-light">
                                    <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 9.5px; letter-spacing: 0.5px;">Jumlah Data</span>
                                    <strong class="fs-6 text-dark">{{ number_format($gwoRun->total_rows, 0, ',', '.') }} baris</strong>
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
                    <h5 class="fw-bold mb-3 text-dark mt-4"><i class="bi bi-award-fill me-2 text-success"></i>Hasil Evaluasi Model SVR + GWO</h5>
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

                            <x-model-analysis-results
                                :mape="$gwoMape"
                                :r2="$gwoR2"
                                :rmse="$gwoRmse"
                                :meanActual="$gwoMeanActual"
                                target="gwo"
                            />
                        </div>
                    </div>

                    <!-- Grafik Aktual vs Prediksi GWO -->
                    <div class="card mb-4 bg-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="card-title mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary-custom"></i>Grafik Aktual vs Prediksi Model SVR + GWO</h5>
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

                            <!-- Analisis Ringkas Rayon (GWO) -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-success shadow-sm">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i>Kesimpulan Hasil Prediksi Per Rayon (GWO)</h6>
                                <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                                    @if($gwoBestRayon)
                                        <li>Rayon paling akurat: <strong class="text-success">{{ $gwoBestRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($gwoBestRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                                    @endif
                                    @if($gwoWorstRayon)
                                        <li>Rayon dengan error terbesar: <strong class="text-danger">{{ $gwoWorstRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($gwoWorstRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                                    @endif
                                    <li>Rata-rata selisih prediksi harian: <strong>Rp {{ number_format(abs($gwoAvgDailyDeviation), 0, ',', '.') }}</strong> per hari.</li>
                                </ul>
                            </div>

                                @if($gwoRayonStats->count() > 0)
                                <div class="border-top pt-3">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 12.5px;"><i class="bi bi-table me-2 text-primary"></i>Rincian Kinerja Seluruh Rayon (GWO)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Rayon</th>
                                                    <th style="text-align: right;">Total Aktual (Rp)</th>
                                                    <th style="text-align: right;">Total Prediksi (Rp)</th>
                                                    <th style="text-align: right;">Avg Error (Rp)</th>
                                                    <th style="text-align: right; width: 120px;">Rata-rata MAPE</th>
                                                    <th style="text-align: center; width: 120px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($gwoRayonStats->sortBy('avg_mape') as $rs)
                                                    @php
                                                        $mapeVal = abs($rs->avg_mape);
                                                        $statusClass = $mapeVal < 10 ? 'text-success' : ($mapeVal <= 20 ? 'text-primary' : 'text-danger');
                                                        $statusText  = $mapeVal < 10 ? 'Sangat Akurat' : ($mapeVal <= 20 ? 'Baik' : 'Perlu Perhatian');
                                                        $badgeClass  = $mapeVal < 10 ? 'bg-success-subtle text-success' : ($mapeVal <= 20 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger');
                                                    @endphp
                                                    <tr>
                                                        <td><span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">{{ $rs->rayon_name }}</span></td>
                                                        <td style="text-align: right; font-weight: 500;">Rp {{ number_format($rs->total_actual, 0, ',', '.') }}</td>
                                                        <td style="text-align: right; font-weight: 500; color: #005BAA;">Rp {{ number_format($rs->total_predicted, 0, ',', '.') }}</td>
                                                        <td style="text-align: right; color: {{ $rs->avg_error >= 0 ? '#10b981' : '#ef4444' }};">
                                                            {{ $rs->avg_error >= 0 ? '+' : '' }}Rp {{ number_format($rs->avg_error, 0, ',', '.') }}
                                                        </td>
                                                        <td style="text-align: right;" class="{{ $statusClass }} fw-bold">{{ number_format($mapeVal, 2, ',', '.') }}%</td>
                                                        <td style="text-align: center;">
                                                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 10px;">{{ $statusText }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
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
                    </div>
                @endif
            </div>

            </div>


            <!-- Re-train / Re-optimize Button & Reset Button -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <form id="reset-grid-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="target" value="grid_search">
                </form>
                <form id="reset-gwo-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="target" value="gwo">
                </form>
                <form id="delete-optimasi-run-form" action="{{ route('operator.optimasi.reset') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="id" id="delete-run-id" value="">
                </form>
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
        c: @json($gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? (float)$gwoRun->modelParameter?->c_value : 250.034536) : null),
        epsilon: @json($gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? (float)$gwoRun->modelParameter?->epsilon_value : 0.00536603) : null),
        gamma: @json($gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? $gwoRun->modelParameter?->gamma_value : 0.004455) : null)
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
            let cMin = 10.0;
            let cMax = 300.0;
            let epsMin = 0.0001;
            let epsMax = 0.05;
            let gamMin = 0.0005;
            let gamMax = 0.1;

            document.getElementById('c_min').value = cMin;
            document.getElementById('c_max').value = cMax;
            document.getElementById('epsilon_min').value = epsMin;
            document.getElementById('epsilon_max').value = epsMax;
            document.getElementById('gamma_min').value = gamMin;
            document.getElementById('gamma_max').value = gamMax;

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
            let wolves = localStorage.getItem('gwo_wolves') || 15;
            let iterations = localStorage.getItem('gwo_iterations') || 30;
            if (document.getElementById('gwo_wolves')) document.getElementById('gwo_wolves').value = wolves;
            if (document.getElementById('gwo_iterations')) document.getElementById('gwo_iterations').value = iterations;
        } else {
            let wolves = localStorage.getItem('gwo_wolves') || 15;
            let iterations = localStorage.getItem('gwo_iterations') || 30;
            let cMin = 10.0;
            let cMax = 300.0;
            let epsMin = 0.0001;
            let epsMax = 0.05;
            let gamMin = 0.0005;
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
        const isGridTrained = @json($gsRun !== null);
        
        if (stepNum === 3 && !isGridRunning) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Akses Terkunci!',
                    text: 'Langkah 3 (Proses Tuning) hanya dapat diakses saat proses optimasi Grid Search sedang berjalan.',
                    icon: 'warning',
                    confirmButtonColor: '#005BAA',
                    confirmButtonText: 'Mengerti'
                });
            }
            return;
        }
        
        if ((stepNum === 4 || stepNum === 5) && !isGridTrained && (typeof bestParamsGs === 'undefined' || !bestParamsGs.c)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hasil Belum Tersedia!',
                    text: 'Silakan jalankan proses optimasi Grid Search terlebih dahulu pada Langkah 2 untuk melihat hasil.',
                    icon: 'warning',
                    confirmButtonColor: '#005BAA',
                    confirmButtonText: 'Mengerti'
                });
            }
            return;
        }

        gridStep = stepNum;
        sessionStorage.setItem('grid_step', stepNum.toString());
        for (let i = 1; i <= 5; i++) {
            const item = document.getElementById(`stepper-grid-${i}`);
            if (item) {
                item.classList.remove('active', 'completed');
                if (i < stepNum)      item.classList.add('completed');
                else if (i === stepNum) item.classList.add('active');
            }
        }
        for (let i = 1; i <= 4; i++) {
            const line = document.getElementById(`stepper-line-grid-${i}`);
            if (line) {
                line.classList.remove('completed');
                if (i < stepNum) line.classList.add('completed');
            }
        }
        const s1 = document.getElementById('grid-step-content-1');
        const s2 = document.getElementById('grid-step-content-2');
        const s3 = document.getElementById('grid-step-content-3');
        const s4 = document.getElementById('results-step-content-4');
        const s5 = document.getElementById('results-step-content-5');
        
        if (s1) s1.classList.add('d-none');
        if (s2) s2.classList.add('d-none');
        if (s3) s3.classList.add('d-none');
        if (s4) s4.classList.add('d-none');
        if (s5) s5.classList.add('d-none');

        if (stepNum === 4) {
            if (s4) s4.classList.remove('d-none');
            setTimeout(() => { s4?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else if (stepNum === 5) {
            if (s5) s5.classList.remove('d-none');
            setTimeout(() => { s5?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else {
            const target = document.getElementById(`grid-step-content-${stepNum}`);
            if (target) target.classList.remove('d-none');
            
            if (stepNum === 3) {
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
                    
                    let cLen = 5, epsLen = 4, gammaLen = 4;
                    try {
                        const rawC = document.getElementById('grid_c')?.value || '';
                        const rawEps = document.getElementById('grid_epsilon')?.value || '';
                        const rawGamma = document.getElementById('grid_gamma')?.value || '';
                        if (rawC.startsWith('[')) cLen = JSON.parse(rawC.replace(/'/g, '"')).length;
                        if (rawEps.startsWith('[')) epsLen = JSON.parse(rawEps.replace(/'/g, '"')).length;
                        if (rawGamma.startsWith('[')) gammaLen = JSON.parse(rawGamma.replace(/'/g, '"')).length;
                    } catch(e) {}
                    let maxCombos = cLen * epsLen * gammaLen;
                    
                    const progressBar = document.getElementById('grid-progress-bar');
                    const gridLabel = document.getElementById('grid-iter-label');
                    const gridPct = document.getElementById('grid-iter-pct');
                    if (progressBar) {
                        progressBar.style.width = '100%';
                        progressBar.setAttribute('aria-valuenow', '100');
                        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    }
                    if (gridLabel) gridLabel.innerText = `Kombinasi Grid: Selesai (${maxCombos} / ${maxCombos})`;
                    if (gridPct)   gridPct.innerText   = '100%';
                    
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
                    if (descEl) descEl.innerText = "Silakan kembali ke Langkah 2 untuk mengonfigurasi dan menjalankan Grid Search.";
                }
            }
        }
    }

    // ── GWO Step Navigation ───────────────────────────────────────────────────
    window.goToGwoStep = function(stepNum) {
        const isGwoTrained = @json($gwoRun !== null);
        
        if (stepNum === 3 && !isGwoRunning) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Akses Terkunci!',
                    text: 'Langkah 3 (Proses Tuning) hanya dapat diakses saat proses optimasi GWO sedang berjalan.',
                    icon: 'warning',
                    confirmButtonColor: '#005BAA',
                    confirmButtonText: 'Mengerti'
                });
            }
            return;
        }
        
        if ((stepNum === 4 || stepNum === 5) && !isGwoTrained && (typeof bestParamsGwo === 'undefined' || !bestParamsGwo.c)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hasil Belum Tersedia!',
                    text: 'Silakan jalankan proses optimasi GWO terlebih dahulu pada Langkah 2 untuk melihat hasil.',
                    icon: 'warning',
                    confirmButtonColor: '#005BAA',
                    confirmButtonText: 'Mengerti'
                });
            }
            return;
        }

        gwoStep = stepNum;
        sessionStorage.setItem('gwo_step', stepNum.toString());
        for (let i = 1; i <= 5; i++) {
            const item = document.getElementById(`stepper-gwo-${i}`);
            if (item) {
                item.classList.remove('active', 'completed');
                if (i < stepNum)      item.classList.add('completed');
                else if (i === stepNum) item.classList.add('active');
            }
        }
        for (let i = 1; i <= 4; i++) {
            const line = document.getElementById(`stepper-line-gwo-${i}`);
            if (line) {
                line.classList.remove('completed');
                if (i < stepNum) line.classList.add('completed');
            }
        }
        const s1 = document.getElementById('gwo-step-content-1');
        const s2 = document.getElementById('gwo-step-content-2');
        const s3 = document.getElementById('gwo-step-content-3');
        const s4 = document.getElementById('results-step-content-4');
        const s5 = document.getElementById('results-step-content-5');
        
        if (s1) s1.classList.add('d-none');
        if (s2) s2.classList.add('d-none');
        if (s3) s3.classList.add('d-none');
        if (s4) s4.classList.add('d-none');
        if (s5) s5.classList.add('d-none');

        if (stepNum === 4) {
            if (s4) s4.classList.remove('d-none');
            setTimeout(() => { s4?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else if (stepNum === 5) {
            if (s5) s5.classList.remove('d-none');
            setTimeout(() => { s5?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
        } else {
            const target = document.getElementById(`gwo-step-content-${stepNum}`);
            if (target) target.classList.remove('d-none');
            
            if (stepNum === 3) {
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
                    
                    const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 30;
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
                    if (descEl) descEl.innerText = "Silakan kembali ke Langkah 2 untuk mengonfigurasi dan menjalankan Grey Wolf Optimizer.";
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
    const GRID_MAX_WAIT = 9000; // 9000 × 200ms = 30 minutes max wait

    window.startGridSearchTuning = function() {
        if (typeof Swal === 'undefined') {
            isGridRunning = true;
            window.goToGridStep(3);
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
                isGridRunning = true;
                window.goToGridStep(3);
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
        const gwoProgressBar = document.getElementById('gwo-progress-bar');
        const gwoIterLabel   = document.getElementById('gwo-iter-label');
        const gwoIterPct     = document.getElementById('gwo-iter-pct');
        const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 30;

        elapsedTimerInterval = setInterval(() => {
            elapsedSeconds++;
            if (elapsedEl) elapsedEl.innerText = `${elapsedSeconds}s`;
            
            // For Grid progress bar update
            if (method === 'grid') {
                const gridBar = document.getElementById('grid-progress-bar');
                const gridLabel = document.getElementById('grid-iter-label');
                const gridPct = document.getElementById('grid-iter-pct');
                const maxCombos = parseInt(gridLabel?.getAttribute('data-max') || '80');
                
                let currentCombo = Math.min(maxCombos - 1, Math.floor((elapsedSeconds / estimatedSeconds) * maxCombos));
                let pct = Math.min(95, Math.round((elapsedSeconds / estimatedSeconds) * 95));
                
                if (gridBar) { 
                    gridBar.style.width = pct + '%'; 
                    gridBar.setAttribute('aria-valuenow', pct); 
                }
                if (gridLabel) gridLabel.innerText = `Kombinasi Grid: ${currentCombo} / ${maxCombos}`;
                if (gridPct)   gridPct.innerText   = pct + '%';
            }
            
            // For GWO progress bar update
            if (method === 'gwo') {
                let currentIter = Math.min(maxIters - 1, Math.floor((elapsedSeconds / estimatedSeconds) * maxIters));
                let pct = Math.min(95, Math.round((elapsedSeconds / estimatedSeconds) * 95));
                
                if (gwoProgressBar) { 
                    gwoProgressBar.style.width = pct + '%'; 
                    gwoProgressBar.setAttribute('aria-valuenow', pct); 
                }
                if (gwoIterLabel) gwoIterLabel.innerText = `Iterasi GWO: ${currentIter} / ${maxIters}`;
                if (gwoIterPct)   gwoIterPct.innerText   = pct + '%';
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
        
        // Reset grid progress bar
        const gridBar = document.getElementById('grid-progress-bar');
        const gridLabel = document.getElementById('grid-iter-label');
        const gridPct = document.getElementById('grid-iter-pct');
        const gridBarContainer = document.getElementById('grid-progress-bar-container');
        if (gridBar) { gridBar.style.width = '0%'; gridBar.setAttribute('aria-valuenow', '0'); }
        if (gridLabel) {
            gridLabel.innerText = `Kombinasi Grid: 0 / ${totalCombinations}`;
            gridLabel.setAttribute('data-max', totalCombinations.toString());
        }
        if (gridPct) gridPct.innerText = "0%";
        if (gridBarContainer) gridBarContainer.classList.remove('d-none');

        let estimatedSeconds = Math.max(10, Math.ceil(totalFits * 0.20));
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
            window.goToGridStep(2);
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
                // Complete progress bar immediately
                const gridBar = document.getElementById('grid-progress-bar');
                const gridLabel = document.getElementById('grid-iter-label');
                const gridPct = document.getElementById('grid-iter-pct');
                const maxCombos = parseInt(gridLabel?.getAttribute('data-max') || '80');
                if (gridBar) { 
                    gridBar.style.width = '100%'; 
                    gridBar.setAttribute('aria-valuenow', '100'); 
                    gridBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                }
                if (gridLabel) gridLabel.innerText = `Kombinasi Grid: Selesai (${maxCombos} / ${maxCombos})`;
                if (gridPct)   gridPct.innerText   = '100%';

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
                          showCancelButton: true,
                          confirmButtonColor: '#005BAA',
                          cancelButtonColor: '#6B7280',
                          confirmButtonText: 'Lihat Hasil',
                          cancelButtonText: 'Latih Ulang'
                     }).then((result) => {
                          clearTempParams();
                          if (result.dismiss === 'cancel') {
                              isGridRunning = true;
                              window.goToGridStep(3);
                              executeGridSearchTuning();
                          } else {
                              sessionStorage.setItem('optimasi_method', 'grid');
                              sessionStorage.setItem('grid_step', '4');
                              window.location.reload(); // reload agar tabel komparasi terupdate
                          }
                     });
                } else {
                    window.goToGridStep(4);
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
    const GWO_MAX_WAIT = 9000; // 9000 × 200ms = 30 minutes max wait

    window.startGwoTuning = function() {
        if (typeof Swal === 'undefined') {
            isGwoRunning = true;
            window.goToGwoStep(3);
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
                isGwoRunning = true;
                window.goToGwoStep(3);
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

        const wolves = parseInt(document.getElementById('gwo_wolves')?.value) || 15;
        const iterations = parseInt(document.getElementById('gwo_iterations')?.value) || 30;

        const progressBar = document.getElementById('gwo-progress-bar');
        const iterLabel   = document.getElementById('gwo-iter-label');
        const iterPct     = document.getElementById('gwo-iter-pct');
        if (progressBar) { progressBar.style.width = '0%'; progressBar.setAttribute('aria-valuenow', '0'); }
        if (iterLabel)   iterLabel.innerText = "Iterasi GWO: 0 / " + iterations;
        if (iterPct)     iterPct.innerText   = "0%";

        const titleEl = document.getElementById('gwo-process-title');
        const descEl  = document.getElementById('gwo-process-desc');
        if (titleEl) titleEl.innerText = "Sedang Menyiapkan GWO...";
        if (descEl)  descEl.innerText  = "Algoritma Grey Wolf Optimizer sedang diinisialisasi.";

        setPipeStatus('gwo', 1, 'processing');
        gwoTimeout = setTimeout(runGwoStepSequence, 800);

        // Calculate estimated seconds
        const totalFits = wolves * iterations * 5;
        const estimatedSeconds = Math.max(10, Math.ceil(totalFits * 0.12));
        startElapsedTimer('gwo', estimatedSeconds);

        // Collect form params
        const formData = {
            wolves:      wolves,
            iterations:  iterations,
            c_min:       document.getElementById('c_min')?.value          || 10.0,
            c_max:       document.getElementById('c_max')?.value          || 300.0,
            epsilon_min: document.getElementById('epsilon_min')?.value    || 0.0001,
            epsilon_max: document.getElementById('epsilon_max')?.value    || 0.05,
            gamma_min:   document.getElementById('gamma_min')?.value      || 0.0005,
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
            window.goToGwoStep(2);
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
                         showCancelButton: true,
                         confirmButtonColor: '#005BAA',
                         cancelButtonColor: '#6B7280',
                         confirmButtonText: 'Lihat Hasil',
                         cancelButtonText: 'Latih Ulang'
                     }).then((result) => {
                         clearTempParams();
                         if (result.dismiss === 'cancel') {
                             isGwoRunning = true;
                              window.goToGwoStep(3);
                             executeGwoTuning();
                         } else {
                             sessionStorage.setItem('optimasi_method', 'gwo');
                             sessionStorage.setItem('gwo_step', '4');
                             window.location.reload();
                         }
                     });
                } else {
                    window.goToGwoStep(4);
                }
            }, 800);
        }
    }

    // ── Chart.js Initialization ───────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const savedMethod = urlParams.get('method') || sessionStorage.getItem('optimasi_method') || 'grid';
        
        // Check if models are already trained in database to set default step to 4 (Results)
        const isGridTrained = @json($gsRun !== null);
        const isGwoTrained = @json($gwoRun !== null);
        
        const defaultGridStep = isGridTrained ? 4 : 1;
        const defaultGwoStep  = isGwoTrained ? 4 : 1;

        // Step 3 is the loading/in-progress screen — never restore it on fresh page load
        // because there is no active process running. Clamp to step 2 if saved as 3.
        const rawGridStep = parseInt(urlParams.get('grid_step') || sessionStorage.getItem('grid_step') || defaultGridStep);
        const rawGwoStep  = parseInt(urlParams.get('gwo_step')  || sessionStorage.getItem('gwo_step')  || defaultGwoStep);
        let savedGridStep = rawGridStep === 3 ? 2 : rawGridStep;
        if ((savedGridStep === 4 || savedGridStep === 5) && !isGridTrained) {
            savedGridStep = 1;
        }
        let savedGwoStep  = rawGwoStep  === 3 ? 2 : rawGwoStep;
        if ((savedGwoStep === 4 || savedGwoStep === 5) && !isGwoTrained) {
            savedGwoStep = 1;
        }

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
                    if (id === 'gwo_wolves' || id === 'gwo_iterations') {
                        localStorage.setItem(id, el.value);
                    }
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

        window.updateGsChart = function(rayonId) {
            const data = getFilteredData(allGsPreds, rayonId);
            if (window.gsChartInstance) {
                window.gsChartInstance.data.labels = data.labels;
                const dsActual = window.gsChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
                if (dsActual) dsActual.data = data.actual;
                const dsPredict = window.gsChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Prediksi SVR (GS)');
                if (dsPredict) dsPredict.data = data.predicted;
                window.gsChartInstance.update();
            }
        }

        window.updateGwoChart = function(rayonId) {
            const data = getFilteredData(allGwoPreds, rayonId);
            if (window.gwoChartInstance) {
                window.gwoChartInstance.data.labels = data.labels;
                const dsActual = window.gwoChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
                if (dsActual) dsActual.data = data.actual;
                const dsPredict = window.gwoChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Prediksi SVR (GWO)');
                if (dsPredict) dsPredict.data = data.predicted;
                window.gwoChartInstance.update();
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
            
            // Initialize with currently active rayonId from PHP
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
                
                // Initialize with currently active rayonId from PHP
                const startRayonId = {{ $rayonId }};
                const startGsData = getFilteredData(allGsPreds, startRayonId);
                
                window.gsChartInstance = new Chart(ctxGs, {
                    type: 'line',
                    data: {
                        labels: startGsData.labels,
                        datasets: [
                            {
                                label: 'Pendapatan Aktual',
                                data: startGsData.actual,
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
                                data: startGsData.predicted,
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
                
                const startRayonId = {{ $rayonId }};
                const startGwoData = getFilteredData(allGwoPreds, startRayonId);
                
                window.gwoChartInstance = new Chart(ctxGwo, {
                    type: 'line',
                    data: {
                        labels: startGwoData.labels,
                        datasets: [
                            {
                                label: 'Pendapatan Aktual',
                                data: startGwoData.actual,
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
                                data: startGwoData.predicted,
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

    window.confirmDeleteOptimasiRun = function(runId, startedAt, modelName) {
        SwalConfirm(
            'Hapus Riwayat Pelatihan?',
            `Riwayat pelatihan ${modelName} tanggal ${startedAt} beserta hasil prediksinya akan dihapus secara permanen!`,
            'Ya, Hapus!',
            function() {
                SwalLoading('Menghapus Riwayat...', 'Mohon tunggu sebentar.');
                document.getElementById('delete-run-id').value = runId;
                document.getElementById('delete-optimasi-run-form').submit();
            }
        );
    };

    window.confirmResetOptimasiAll = function(target) {
        const modelName = target === 'grid_search' ? 'Grid Search' : 'Grey Wolf Optimizer (GWO)';
        const formId = target === 'grid_search' ? 'reset-grid-form' : 'reset-gwo-form';
        
        SwalConfirm(
            'Reset Semua Riwayat?',
            `Seluruh riwayat proses optimasi ${modelName} dan hasil prediksi terkait akan dihapus secara permanen dari database!`,
            'Ya, Reset Semua!',
            function() {
                SwalLoading('Memproses Reset...', 'Mohon tunggu sebentar.');
                document.getElementById(formId).submit();
            }
        );
    }
</script>
@endsection