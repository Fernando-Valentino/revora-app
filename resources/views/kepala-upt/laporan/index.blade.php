@extends('layouts.app')

@section('title', 'Laporan Analisis Prediksi Pendapatan')
@section('subtitle', 'Halaman ini digunakan untuk melihat, menganalisis, dan mengekspor laporan perbandingan realisasi vs prediksi pendapatan retribusi.')

@section('content')
<style>
    /* Custom Styling for Premium Minimalist Report Page */
    .report-container {
        font-family: 'Inter', sans-serif;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.01);
    }
    .filter-input {
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 13px;
        color: #334155;
        transition: all 0.2s ease;
    }
    .filter-input:focus {
        background-color: #ffffff;
        border-color: #005BAA;
        box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.08);
        outline: none;
    }
    .btn-action-primary {
        background-color: #005BAA;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 13px;
        padding: 9px 18px;
        transition: all 0.2s ease;
    }
    .btn-action-primary:hover {
        background-color: #004d90;
        transform: translateY(-1px);
    }
    .btn-action-outline {
        background-color: #ffffff;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
        padding: 9px 14px;
        transition: all 0.2s ease;
    }
    .btn-action-outline:hover {
        background-color: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }
    .report-card-summary {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .report-card-summary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.035);
    }
    .icon-circle-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .metric-title {
        font-size: 11px;
        font-weight: 600;
        color: #829ab1;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .metric-val {
        font-size: 20px;
        font-weight: 700;
        color: #102a43;
        font-variant-numeric: tabular-nums;
    }
    .chart-container-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.01);
    }
    .future-proj-card {
        background: linear-gradient(145deg, #ffffff, #fcfdff);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 91, 170, 0.02);
    }
    .list-proj-item {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    .list-proj-item:hover {
        background: #ffffff;
        border-color: rgba(0, 91, 170, 0.2);
        transform: translateX(2px);
    }
    .recommendation-box {
        background: rgba(14, 165, 233, 0.03);
        border: 1px solid rgba(14, 165, 233, 0.12);
        border-radius: 14px;
        padding: 16px;
        transition: all 0.2s ease;
    }
    .recommendation-box:hover {
        background: rgba(14, 165, 233, 0.05);
        border-color: rgba(14, 165, 233, 0.2);
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 6px;
    }
    .table-modern thead th {
        background: #f8fafc;
        border: none;
        color: #627d98;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
    }
    .table-modern tbody tr {
        background: #ffffff;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.005);
    }
    .table-modern tbody tr:hover {
        transform: scale(1.003);
        box-shadow: 0 6px 15px rgba(0, 91, 170, 0.03);
        background: #fafcfe;
    }
    .table-modern tbody td {
        padding: 14px 16px;
        border-top: 1px solid #f0f4f8;
        border-bottom: 1px solid #f0f4f8;
        color: #334e68;
        font-size: 13px;
    }
    .table-modern tbody td:first-child {
        border-left: 1px solid #f0f4f8;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }
    .table-modern tbody td:last-child {
        border-right: 1px solid #f0f4f8;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    .table-modern tfoot td {
        padding: 16px;
        font-weight: 700;
        border-top: 2px solid #cbd5e1;
        color: #102a43;
    }
    .decision-alert {
        border-left: 4px solid;
        border-radius: 12px;
        padding: 16px;
        font-size: 12.5px;
        line-height: 1.6;
    }
    .decision-alert-success {
        background-color: #f6fdf9;
        border-color: #10b981;
        color: #065f46;
    }
    .decision-alert-primary {
        background-color: #f0f7ff;
        border-color: #3b82f6;
        color: #1e40af;
    }
    .decision-alert-danger {
        background-color: #fffbfa;
        border-color: #ef4444;
        color: #991b1b;
    }
    .animate-pulse {
        animation: pulse-ring 2s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(0.95); opacity: 1; }
    }
</style>

<div class="container-fluid p-0 report-container">

    <!-- Filter Toolbar (Sleek Inline Design) -->
    <div class="card border-0 mb-4 filter-card">
        <div class="card-body p-3">
            <form method="GET" action="" class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 pe-0 text-secondary" style="font-size: 12px;"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="start_date" class="form-control border-0 bg-transparent py-2" style="font-size: 13px; font-weight: 500;" value="{{ $startDate }}" title="Tanggal Mulai" />
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 pe-0 text-secondary" style="font-size: 12px;"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="end_date" class="form-control border-0 bg-transparent py-2" style="font-size: 13px; font-weight: 500;" value="{{ $endDate }}" title="Tanggal Akhir" />
                    </div>
                </div>
                <div class="col-lg-4 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 pe-0 text-secondary" style="font-size: 12px;"><i class="bi bi-geo-alt"></i></span>
                        <select name="rayon_id" class="form-select border-0 bg-transparent py-2" style="font-size: 13px; font-weight: 500;">
                            <option value="0" {{ $rayonId == 0 ? 'selected' : '' }}>Semua Rayon (Gabungan)</option>
                            @foreach($rayons as $r)
                                <option value="{{ $r->id }}" {{ $rayonId == $r->id ? 'selected' : '' }}>{{ $r->nama_rayon }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn-action-primary w-100 py-2 d-flex align-items-center justify-content-center gap-1"><i class="bi bi-filter"></i> Saring</button>
                    <a href="{{ request()->url() }}" class="btn-action-outline py-2" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Kinerja (Card Grid) -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Jumlah Data -->
        <div class="col-md-3">
            <div class="card border-0 report-card-summary">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                    <div>
                        <span class="metric-title d-block">Jumlah Laporan</span>
                        <div class="metric-val">{{ $summary['total_data'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Realisasi Aktual -->
        <div class="col-md-3">
            <div class="card border-0 report-card-summary">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle-wrapper bg-success-subtle text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <span class="metric-title d-block">Realisasi (Aktual)</span>
                        <div class="metric-val text-success" style="font-size: 18px;">{{ $summary['total_aktual'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Proyeksi Target -->
        <div class="col-md-3">
            <div class="card border-0 report-card-summary">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle-wrapper bg-info-subtle text-info">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <span class="metric-title d-block">Proyeksi (SVR)</span>
                        <div class="metric-val text-info" style="font-size: 18px;">{{ $summary['total_prediksi'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4: MAPE -->
        <div class="col-md-3">
            <div class="card border-0 report-card-summary bg-dark text-white">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle-wrapper bg-white-50 text-warning" style="background: rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div>
                        <span class="metric-title d-block text-white-50">Rerata Kesalahan</span>
                        <div class="metric-val text-warning">{{ $summary['mape'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Proyeksi Layout -->
    <div class="row g-4 mb-4">
        <!-- Left: Chart & Analysis -->
        <div class="col-lg-8">
            <div class="card border-0 chart-container-card p-4">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-dark m-0" style="font-size: 14px;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Tren Realisasi vs Prediksi SVR</h5>
                            <small class="text-secondary">Periode: {{ $summary['periode'] }}</small>
                        </div>
                        <a href="{{ route('kepala-upt.laporan.export-pdf', request()->query()) }}" class="btn-action-outline btn-sm d-flex align-items-center gap-1.5" style="font-size: 12px; padding: 6px 12px;"><i class="bi bi-file-earmark-pdf text-danger"></i> Cetak PDF</a>
                    </div>
                    
                    @if(count($chartActualValues) > 0)
                        <div style="height: 290px; position: relative; width: 100%;">
                            <canvas id="laporanChart"></canvas>
                        </div>

                        <!-- Analysis Metrics below Chart -->
                        <div class="mt-4 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark m-0" style="font-size: 13px;"><i class="bi bi-journal-text me-2 text-primary"></i>Analisis Kinerja & Performa Periode</h6>
                                <button class="btn btn-xs btn-outline-primary d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#accuracyCriteriaModal" style="border-radius: 8px; font-size: 11px; padding: 3px 8px;">
                                    <i class="bi bi-info-circle"></i> Acuan Kriteria Akurasi
                                </button>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border-0" style="background-color: #f8fafc !important;">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">RATA-RATA HARIAN</span>
                                        <strong class="text-dark d-block" style="font-size: 13.5px; font-variant-numeric: tabular-nums;">{{ $analysis['avg_actual'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border-0" style="background-color: #f8fafc !important;">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">PROYEKSI HARIAN</span>
                                        <strong class="d-block" style="font-size: 13.5px; color: #005BAA; font-variant-numeric: tabular-nums;">{{ $analysis['avg_predict'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border-0" style="background-color: #f8fafc !important;">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">DEVIASI NOMINAL</span>
                                        <strong class="text-danger d-block" style="font-size: 13.5px; font-variant-numeric: tabular-nums;">{{ $analysis['avg_deviation'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            @php
                                $alertType = 'decision-alert-danger';
                                $iconStyle = 'bi-exclamation-triangle text-danger';
                                if (strpos($analysis['status_akurasi'], 'Sangat Akurat') !== false) {
                                    $alertType = 'decision-alert-success';
                                    $iconStyle = 'bi-patch-check text-success';
                                } elseif (strpos($analysis['status_akurasi'], 'Baik') !== false) {
                                    $alertType = 'decision-alert-primary';
                                    $iconStyle = 'bi-check-circle text-primary';
                                }
                            @endphp

                            <div class="decision-alert {{ $alertType }} d-flex align-items-start gap-3 mt-3">
                                <i class="bi {{ $iconStyle }} fs-5 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <strong style="font-size: 13px;">Rekomendasi Keputusan (Status: {{ $analysis['status_akurasi'] }})</strong>
                                    <p class="mb-1 mt-1 text-secondary-emphasis" style="font-size: 12px;">{{ $analysis['keterangan_akurasi'] }}</p>
                                    <span class="small text-muted d-block mt-1">Selisih nominal harian rata-rata realisasi retribusi dan proyeksi adalah <strong>{{ $analysis['avg_deviation'] }}</strong> per hari.</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-graph-up text-secondary d-block fs-1 mb-2"></i>
                            <span class="text-muted small d-block">Tidak ada data untuk dirender pada grafik selama periode ini.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Future Projections -->
        <div class="col-lg-4">
            <div class="card border-0 h-100 future-proj-card p-4">
                <div class="card-body p-0 d-flex flex-column h-100" id="future-forecast-card-body" data-rayon-id="{{ $rayonId }}" data-forecast-url="{{ route('kepala-upt.laporan.forecast') }}">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="text-uppercase text-secondary fw-bold mb-0" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="bi bi-cpu text-primary me-1"></i> Prediksi 7 Hari Ke Depan
                        </h6>
                        <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill" style="font-size: 9px; font-weight: 600;">AI Proyeksi</span>
                    </div>

                    <!-- Loading State -->
                    <div class="text-center py-5 my-auto" id="forecast-loading-state">
                        <div class="spinner-border text-primary spinner-border-sm mb-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted small d-block">Menghitung proyeksi AI...</span>
                    </div>

                    <!-- Content State (hidden by default) -->
                    <div id="forecast-content-state" style="display: none !important;" class="d-flex flex-column h-100 flex-grow-1">
                        <div class="mb-3.5">
                            <span class="text-secondary small d-block mb-1">Estimasi Total Pendapatan</span>
                            <h3 class="fw-bold mb-1" id="forecast-total-predicted" style="font-size: 24px; color: #005BAA; font-variant-numeric: tabular-nums;">-</h3>
                            <span class="text-muted small">
                                Rerata: <strong class="text-dark" id="forecast-avg-predicted">-</strong> / hari
                            </span>
                        </div>

                        <!-- Mini list harian -->
                        <div class="mb-4 flex-grow-1">
                            <span class="text-secondary d-block small mb-2 fw-semibold">Detail Harian</span>
                            <div class="d-flex flex-column gap-2" id="forecast-daily-list" style="max-height: 185px; overflow-y: auto; padding-right: 2px;">
                                <!-- Will be filled dynamically -->
                            </div>
                        </div>

                        <!-- Rekomendasi AI Box -->
                        <div class="recommendation-box mt-auto">
                            <h6 class="fw-bold text-info d-flex align-items-center mb-2" style="font-size: 11.5px;">
                                <i class="bi bi-lightbulb-fill text-warning me-1.5 animate-pulse"></i> Rekomendasi AI (SVR-GWO):
                            </h6>
                            <ul class="mb-0 ps-3 text-secondary" id="forecast-recommendations" style="font-size: 11px; line-height: 1.5; padding-left: 1.2rem !important;">
                                <!-- Will be filled dynamically -->
                            </ul>
                        </div>
                    </div>

                    <!-- Error State (hidden by default) -->
                    <div class="text-center py-5 my-auto" id="forecast-error-state" style="display: none !important;">
                        <i class="bi bi-robot text-muted d-block fs-2 mb-2"></i>
                        <span class="text-muted small d-block" id="forecast-error-msg">Gagal memuat proyeksi masa depan.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card (Clean separation rows) -->
    <div class="card border-0 filter-card p-4">
        <div class="card-body p-0">
            <h5 class="fw-bold text-dark mb-3.5" style="font-size: 14px;"><i class="bi bi-table me-2 text-primary"></i>Tabel Rincian Harian</h5>
            
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">No</th>
                            <th>Tanggal</th>
                            <th>Rayon</th>
                            <th style="text-align: right;">Realisasi Aktual</th>
                            <th style="text-align: right;">Prediksi Target</th>
                            <th style="text-align: right;">Selisih (Error)</th>
                            <th style="text-align: right; width: 120px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">% Kesalahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr>
                                <td>{{ $rep['no'] }}</td>
                                <td>{{ $rep['tanggal'] }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill" style="font-size: 10px; font-weight: 500;">
                                        {{ $rep['rayon'] }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 500; font-variant-numeric: tabular-nums;">Rp {{ number_format($rep['aktual'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 600; color: #005BAA; font-variant-numeric: tabular-nums;">Rp {{ number_format($rep['prediksi'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 500; color: {{ $rep['error'] >= 0 ? '#10b981' : '#ef4444' }}; font-variant-numeric: tabular-nums;">
                                    {{ $rep['error'] >= 0 ? '+' : '' }}Rp {{ number_format($rep['error'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; font-variant-numeric: tabular-nums;" class="{{ $rep['aktual'] > 0 && abs($rep['error'])/$rep['aktual']*100 > 20 ? 'text-warning' : 'text-success' }}">
                                    {{ $rep['pct_error'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data transaksi laporan yang cocok dengan kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($reports) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border-top: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Total Periode Ini</td>
                                <td style="text-align: right; color: #10b981; border-top: 1px solid #cbd5e1; font-variant-numeric: tabular-nums;">{{ $total_period['aktual'] }}</td>
                                <td style="text-align: right; color: #005BAA; border-top: 1px solid #cbd5e1; font-variant-numeric: tabular-nums;">{{ $total_period['prediksi'] }}</td>
                                <td style="text-align: right; color: #1f2937; border-top: 1px solid #cbd5e1; font-variant-numeric: tabular-nums;">{{ $total_period['error'] }}</td>
                                <td style="text-align: right; color: #1f2937; border-top: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; border-top-right-radius: 12px; border-bottom-right-radius: 12px; font-variant-numeric: tabular-nums;">MAPE: {{ $total_period['pct_error'] }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forecastCard = document.getElementById('future-forecast-card-body');
        if (forecastCard) {
            const rayonId = forecastCard.getAttribute('data-rayon-id') || 0;
            const forecastUrl = forecastCard.getAttribute('data-forecast-url');
            
            const loadingState = document.getElementById('forecast-loading-state');
            const contentState = document.getElementById('forecast-content-state');
            const errorState = document.getElementById('forecast-error-state');
            const errorMsg = document.getElementById('forecast-error-msg');
            
            fetch(`${forecastUrl}?rayon_id=${rayonId}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success && res.data) {
                        const data = res.data;
                        
                        document.getElementById('forecast-total-predicted').innerText = data.total_predicted;
                        document.getElementById('forecast-avg-predicted').innerText = data.avg_predicted;
                        
                        const dailyList = document.getElementById('forecast-daily-list');
                        dailyList.innerHTML = '';
                        
                        if (data.detail_harian && data.detail_harian.length > 0) {
                            data.detail_harian.forEach(day => {
                                const dateObj = new Date(day.tanggal);
                                const dayNum = dateObj.getDay();
                                const isWeekend = dayNum === 0 || dayNum === 6;
                                
                                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                const dayName = dayNames[dayNum];
                                
                                const options = { day: 'numeric', month: 'short' };
                                const formattedDate = dateObj.toLocaleDateString('id-ID', options);
                                
                                const formattedVal = new Intl.NumberFormat('id-ID').format(day.pendapatan);
                                const weekendBadge = isWeekend ? `<span class="badge bg-warning-subtle text-warning px-1.5 py-0.5 rounded-pill ms-1" style="font-size: 8px;">Weekend</span>` : '';
                                
                                const dayHtml = `
                                    <div class="d-flex justify-content-between align-items-center list-proj-item">
                                        <span class="text-dark fw-medium" style="font-size: 11.5px;">
                                            ${dayName}, ${formattedDate}
                                            ${weekendBadge}
                                        </span>
                                        <span class="fw-bold text-dark" style="font-variant-numeric: tabular-nums;">Rp ${formattedVal}</span>
                                    </div>
                                `;
                                dailyList.insertAdjacentHTML('beforeend', dayHtml);
                            });
                        }
                        
                        const recsList = document.getElementById('forecast-recommendations');
                        recsList.innerHTML = '';
                        if (data.recommendations && data.recommendations.length > 0) {
                            data.recommendations.forEach(rec => {
                                recsList.insertAdjacentHTML('beforeend', `<li class="mb-1">${rec}</li>`);
                            });
                        } else {
                            recsList.insertAdjacentHTML('beforeend', `<li class="mb-1 text-muted">Tidak ada rekomendasi spesifik.</li>`);
                        }
                        
                        loadingState.style.setProperty('display', 'none', 'important');
                        contentState.style.setProperty('display', 'flex', 'important');
                    } else {
                        throw new Error(res.message || 'Gagal mengambil data proyeksi.');
                    }
                })
                .catch(err => {
                    loadingState.style.setProperty('display', 'none', 'important');
                    if (errorMsg) errorMsg.innerText = err.message || 'Gagal memuat proyeksi masa depan. Pastikan server FastAPI ML aktif di port 8000.';
                    errorState.style.setProperty('display', 'block', 'important');
                });
        }
    });
</script>

@if(count($chartActualValues) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('laporanChart').getContext('2d');
            
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 260);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.08)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

            const gradientPredict = ctx.createLinearGradient(0, 0, 0, 260);
            gradientPredict.addColorStop(0, 'rgba(244, 197, 66, 0.04)');
            gradientPredict.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Realisasi (Aktual)',
                            data: @json($chartActualValues),
                            borderColor: '#005BAA',
                            borderWidth: 2,
                            backgroundColor: gradientActual,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#005BAA',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'Proyeksi (Prediksi SVR)',
                            data: @json($chartPredictValues),
                            borderColor: '#F4C542',
                            borderWidth: 2,
                            backgroundColor: gradientPredict,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#F4C542',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            borderDash: [5, 4]
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
                            align: 'end',
                            labels: {
                                boxWidth: 8,
                                boxHeight: 8,
                                font: { family: 'Inter', size: 11, weight: '500' }
                            }
                        },
                        tooltip: {
                            padding: 10,
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
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
                            grid: { borderDash: [6, 6], color: '#e2e8f0', drawBorder: false },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                },
                                font: { family: 'Inter', size: 10 }
                            }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { family: 'Inter', size: 9.5 } }
                        }
                    }
                }
            });
        });
    </script>
@endif
@endsection
