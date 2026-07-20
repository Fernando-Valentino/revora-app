@props([
    'chartLabels',
    'chartActualValues',
    'chartPredictValues',
    'rayonId',
    'type' => 'harian',
    'forecastRoute',
])

{{-- Skeleton Placeholder --}}
<div class="sk-wrapper">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="report-card p-4">
                <span class="skeleton skeleton-text lg" style="width: 240px; margin-bottom: 18px;"></span>
                <x-ui.skeleton type="chart" height="320px" />
            </div>
        </div>
        <div class="col-12">
            <div class="report-card p-4">
                <span class="skeleton skeleton-text lg" style="width: 200px; margin-bottom: 18px;"></span>
                <x-ui.skeleton type="chart" height="250px" />
            </div>
        </div>
    </div>
</div>

{{-- Real Content --}}
<div class="sk-content">
<div class="row g-4 mb-4">
    <!-- Top Card: Actual vs Prediction Chart -->
    <div class="col-12">
        <div class="chart-container-card">
            <h2 class="section-title" style="font-size: 15px;">
                Pendapatan Aktual vs Prediksi
            </h2>
            <p class="section-desc">Perbandingan pendapatan yang diterima dengan perkiraan sistem, per periode.</p>
            @if(count($chartActualValues) > 0)
                <div style="height: 320px; position: relative; width: 100%;">
                    <canvas id="laporanChart"
                            data-labels="{{ json_encode($chartLabels) }}"
                            data-actual="{{ json_encode($chartActualValues) }}"
                            data-predict="{{ json_encode($chartPredictValues) }}">
                    </canvas>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up text-secondary d-block fs-1 mb-2"></i>
                    <span class="text-muted small d-block">Tidak ada data untuk dirender pada grafik selama periode ini.</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Card: Future Prediction Projections Chart -->
    <div class="col-12">
        <div class="future-proj-card">
            <div id="future-forecast-card-body"
                 data-rayon-id="{{ $rayonId }}"
                 data-forecast-url="{{ $forecastRoute }}"
                 data-type="{{ $type }}">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="section-title mb-1" style="font-size: 15px;" id="forecast-title">
                            Prediksi Ke Depan
                        </h2>
                        <p class="section-desc mb-0">Proyeksi estimasi pendapatan retribusi parkir untuk periode mendatang berdasarkan analisis data historis.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill" style="font-size: 10px; font-weight: 600;">Proyeksi</span>
                </div>

                <!-- Loading State: Skeleton -->
                <div id="forecast-loading-state" class="py-4">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <x-ui.skeleton type="chart" height="250px" />
                        </div>
                        <div class="col-lg-4">
                            <x-ui.skeleton type="forecast" />
                        </div>
                    </div>
                </div>

                <!-- Content State -->
                <div id="forecast-content-state" style="display: none !important;" class="row g-4">
                    <!-- Left: Forecast Chart -->
                    <div class="col-lg-8">
                        <div style="height: 250px; position: relative; width: 100%;">
                            <canvas id="forecastChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Right: Stats & Recommendations -->
                    <div class="col-lg-4 d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <span class="text-secondary small d-block mb-1">Estimasi Total Pendapatan</span>
                            <h3 class="fw-bold mb-1" id="forecast-total-predicted" style="font-size: 24px; color: #005BAA; font-variant-numeric: tabular-nums;">-</h3>
                            <span class="text-muted small">
                                Rerata: <strong class="text-dark" id="forecast-avg-predicted">-</strong>
                            </span>
                        </div>

                        <!-- Recommendations -->
                        <div class="recommendation-box mt-3">
                            <h6 class="fw-bold text-info d-flex align-items-center mb-2" style="font-size: 11.5px;">
                                <i class="bi bi-lightbulb-fill text-warning me-1.5 animate-pulse"></i> Rekomendasi:
                            </h6>
                            <ul class="mb-0 ps-3 text-secondary" id="forecast-recommendations" style="font-size: 11px; line-height: 1.5; padding-left: 1.2rem !important;">
                                <!-- Filled dynamically -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div class="text-center py-5 my-auto" id="forecast-error-state" style="display: none !important;">
                    <i class="bi bi-robot text-muted d-block fs-2 mb-2"></i>
                    <span class="text-muted small d-block" id="forecast-error-msg">Gagal memuat proyeksi masa depan.</span>
                </div>

            </div>
        </div>
    </div>
</div>
</div>
