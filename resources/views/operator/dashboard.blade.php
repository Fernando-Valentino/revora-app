@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid p-0">
        <style>
            .dashboard-card-hover {
                transition: all 0.2s ease-in-out;
                border: 1px solid var(--border) !important;
            }

            .dashboard-card-hover:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 16px rgba(0, 91, 170, 0.06) !important;
            }

            .quick-action-btn {
                transition: all 0.2s ease;
                border: 1px solid var(--border) !important;
                border-radius: 8px;
                background: #ffffff;
                color: var(--text-primary);
                text-decoration: none;
                display: flex;
                align-items: center;
                padding: 10px 14px;
            }

            .quick-action-btn:hover {
                background-color: var(--primary-blue-light) !important;
                border-color: var(--primary-blue) !important;
                color: var(--primary-blue) !important;
                transform: translateX(4px);
            }

            .icon-box {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                flex-shrink: 0;
            }
        </style>

        <!-- Welcome Hero Banner -->
        <div class="card border-0 text-white mb-4 shadow-sm"
            style="background: linear-gradient(135deg, var(--primary-blue-dark) 0%, var(--primary-blue) 100%); border-radius: 12px; padding: 20px 24px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 18px;"><i class="bi bi-speedometer2 me-2"></i>Dashboard
                        Operator</h4>
                    <p class="mb-0 opacity-75 small" style="font-size: 12px;">Selamat datang kembali! Pantau tren realisasi
                        pendapatan, latih model prediksi SVR, dan kelola data retribusi parkir harian.</p>
                </div>
                <div>
                    <span class="badge bg-white text-primary px-3 py-2 fw-semibold shadow-sm"
                        style="font-size: 11px; color: var(--primary-blue) !important;">
                        <i class="bi bi-person-circle me-1"></i> Operator UPT Parkir
                    </span>
                </div>
            </div>
        </div>

        {{-- Skeleton Placeholder --}}
        <div class="sk-wrapper">
            <!-- Metrics Grid Skeleton -->
            <x-ui.skeleton type="dashboard-card" />

            <!-- Side-by-Side Charts Skeleton -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="skeleton-card h-100 p-4">
                        <span class="skeleton skeleton-text lg" style="width: 250px; margin-bottom: 20px;"></span>
                        <x-ui.skeleton type="chart" height="260px" />
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="skeleton-card h-100 p-4">
                        <span class="skeleton skeleton-text lg" style="width: 250px; margin-bottom: 20px;"></span>
                        <x-ui.skeleton type="chart" height="260px" />
                    </div>
                </div>
            </div>

            <!-- Table & Sidebar Skeletons -->
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <x-ui.skeleton type="table" :rows="6" />
                </div>
                <div class="col-12 col-lg-4">
                    <div class="skeleton-card mb-4" style="padding: 20px;">
                        <span class="skeleton skeleton-text lg" style="width: 150px; margin-bottom: 15px;"></span>
                        <span class="skeleton skeleton-text" style="width: 100%; height: 35px; margin-bottom: 10px;"></span>
                        <span class="skeleton skeleton-text" style="width: 100%; height: 35px; margin-bottom: 10px;"></span>
                        <span class="skeleton skeleton-text" style="width: 100%; height: 35px; margin-bottom: 0;"></span>
                    </div>
                    <div class="skeleton-card" style="padding: 20px;">
                        <span class="skeleton skeleton-text lg" style="width: 150px; margin-bottom: 15px;"></span>
                        <span class="skeleton skeleton-text" style="width: 100%;"></span>
                        <span class="skeleton skeleton-text" style="width: 95%;"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Real Content --}}
        <div class="sk-content">
            <!-- Metrics Grid -->
            <div class="row g-3 mb-4">
                <!-- Metric 1: Realisasi Harian Terkini -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 mb-0 dashboard-card-hover bg-white"
                        style="padding: 16px 20px; border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle text-success me-3">
                                <i class="bi bi-cash-coin fs-5"></i>
                            </div>
                            <div>
                                <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                    style="font-size: 10px; letter-spacing: 0.5px;">Realisasi Terkini</span>
                                <h4 class="fw-bold text-dark mb-0" style="font-size: 18px;">
                                    {{ $metrics['total_pendapatan_harian'] }}</h4>
                                <span class="text-secondary small" style="font-size: 10.5px;">Tanggal:
                                    {{ $metrics['tanggal_terkini'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metric 2: Rata-rata Pendapatan -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 mb-0 dashboard-card-hover bg-white"
                        style="padding: 16px 20px; border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary-subtle text-primary me-3">
                                <i class="bi bi-calculator fs-5"></i>
                            </div>
                            <div>
                                <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                    style="font-size: 10px; letter-spacing: 0.5px;">Rata-Rata</span>
                                <h4 class="fw-bold text-dark mb-0" style="font-size: 18px;">{{ $metrics['rata_rata'] }}</h4>
                                <span class="text-secondary small" style="font-size: 10.5px;">Berdasarkan
                                    {{ $metrics['total_data'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metric 3: Akurasi SVR-Grid Search -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 mb-0 dashboard-card-hover bg-white"
                        style="padding: 16px 20px; border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-info-subtle text-info me-3">
                                <i class="bi bi-grid-3x3 fs-5"></i>
                            </div>
                            <div>
                                <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                    style="font-size: 10px; letter-spacing: 0.5px;">Akurasi SVR-Grid Search</span>
                                <h4 class="fw-bold text-dark mb-0" style="font-size: 18px;">{{ $metrics['mape_gs'] }}</h4>
                                <span class="text-secondary small" style="font-size: 10.5px;">MAPE Grid Search tuning</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metric 4: Akurasi SVR-GWO -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 mb-0 dashboard-card-hover bg-white"
                        style="padding: 16px 20px; border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning-subtle text-warning me-3">
                                <i class="bi bi-cpu fs-5"></i>
                            </div>
                            <div>
                                <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                    style="font-size: 10px; letter-spacing: 0.5px;">Akurasi SVR-GWO</span>
                                <h4 class="fw-bold text-dark mb-0" style="font-size: 18px;">{{ $metrics['mape_gwo'] }}</h4>
                                <span class="text-secondary small" style="font-size: 10.5px;">MAPE Grey Wolf
                                    Optimizer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphs Section: 2 Side-by-Side Charts -->
            <div class="row g-4 mb-4">
                <!-- Left Column: Actual vs Model Predictions -->
                <div class="col-12 col-lg-6">
                    <div class="card bg-white h-100" style="border-radius: 12px; padding: 20px 24px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;">Komparasi Prediksi SVR (Grid
                                    Search vs GWO)</h5>
                                <span class="text-secondary small d-block" style="font-size: 11px;">Tren realisasi
                                    pendapatan dibandingkan dengan prediksi Grid Search dan GWO</span>
                            </div>
                            <a href="{{ route('operator.prediksi.index') }}"
                                class="btn btn-outline-primary btn-sm rounded-2"
                                style="font-size: 11.5px; padding: 4px 10px;">
                                <i class="bi bi-cpu me-1"></i> Latih SVR
                            </a>
                        </div>
                        <div style="height: 260px; position: relative;">
                            <canvas id="revenueTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Future Prediction (Forecasting) -->
                <div class="col-12 col-lg-6">
                    <div class="card bg-white h-100" style="border-radius: 12px; padding: 20px 24px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;">Prediksi Pendapatan Masa Depan
                                    (Forecasting)</h5>
                                <span class="text-secondary small d-block" style="font-size: 11px;">Estimasi proyeksi
                                    pendapatan parkir ke depan</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select id="forecastPeriod" class="form-select form-select-sm"
                                    style="font-size: 11.5px; width: 160px; height: 32px; border-radius: 6px;"
                                    onchange="changeForecastPeriod(this.value)">
                                    <option value="7" selected>7 Hari ke Depan</option>
                                    <option value="14">14 Hari ke Depan</option>
                                    <option value="30">30 Hari ke Depan</option>
                                </select>
                            </div>
                        </div>
                        <div id="forecastChartContainer" class="d-none" style="height: 260px; position: relative;">
                            <canvas id="futureForecastChart"></canvas>
                        </div>
                        <div id="forecastPlaceholder"
                            class="d-flex flex-column align-items-center justify-content-center text-center text-secondary"
                            style="height: 260px;">
                            <div class="p-3 bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="bi bi-bar-chart text-muted fs-3"></i>
                            </div>
                            @if($performanceMetrics['mape_gwo'] === null)
                                <h6 class="fw-bold text-dark mb-1" id="forecastPlaceholderTitle" style="font-size: 13.5px;">
                                    Proyeksi Belum Tersedia</h6>
                                <p class="mb-0 small px-3 text-secondary" id="forecastPlaceholderText"
                                    style="max-width: 320px;">Model SVR belum dilatih. Harap lakukan training atau optimasi
                                    model terlebih dahulu untuk memproyeksikan estimasi pendapatan mendatang.</p>
                            @else
                                <h6 class="fw-bold text-dark mb-1" id="forecastPlaceholderTitle" style="font-size: 13.5px;">
                                    Memuat Proyeksi...</h6>
                                <p class="mb-0 small px-3 text-secondary" id="forecastPlaceholderText"
                                    style="max-width: 320px;">Sedang mengambil data proyeksi dari server.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Section: Table & Sidebar -->
            <div class="row g-4">
                <!-- Left Column: Table (8/12) -->
                <div class="col-12 col-lg-8">
                    <!-- Recent Incomes Card -->
                    <div class="card bg-white mb-0" style="border-radius: 12px; padding: 20px 24px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;">Data Pendapatan Terkini</h5>
                                <span class="text-secondary small d-block" style="font-size: 11px;">Realisasi pendapatan
                                    harian yang terakhir kali terinput ke sistem</span>
                            </div>
                            <a href="{{ route('operator.pendapatan.index') }}" class="btn btn-primary btn-sm rounded-2"
                                style="font-size: 11.5px; padding: 4px 10px;">
                                <i class="bi bi-pencil-square me-1"></i> Kelola Data
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead>
                                    <tr class="table-light">
                                        <th style="width: 50px; padding: 8px 12px;">No</th>
                                        <th style="padding: 8px 12px;">Tanggal</th>
                                        <th style="padding: 8px 12px;">Rayon</th>
                                        <th style="padding: 8px 12px;">Petugas / Jukir</th>
                                        <th style="text-align: right; padding: 8px 12px;">Jumlah Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incomes as $index => $income)
                                        <tr>
                                            <td style="padding: 10px 12px;">{{ $index + 1 }}</td>
                                            <td style="padding: 10px 12px;">{{ date('d-m-Y', strtotime($income->tanggal)) }}
                                            </td>
                                            <td style="padding: 10px 12px;">
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1"
                                                    style="font-size: 10px;">
                                                    {{ $income->rayon->nama_rayon ?? 'Tidak Diketahui' }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px 12px;">
                                                {{ $income->juruParkir->jumlah_juru_parkir ?? ($income->rayon->jumlah_juru_parkir ?? 0) }}
                                                Jukir
                                            </td>
                                            <td
                                                style="text-align: right; font-weight: 600; color: var(--primary-blue); padding: 10px 12px;">
                                                Rp {{ number_format($income->jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-secondary py-4" style="padding: 10px 12px;">
                                                Belum ada data pendapatan terinput.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar / Quick Actions & Rayon Stats (4/12) -->
                <div class="col-12 col-lg-4">
                    <!-- Quick Actions Panel -->
                    <div class="card mb-4 bg-white" style="border-radius: 12px; padding: 20px 24px;">
                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom" style="font-size: 14px;">Aksi Cepat</h5>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('operator.pendapatan.index') }}" class="quick-action-btn">
                                <i class="bi bi-file-earmark-plus me-3 text-primary fs-6"></i>
                                <div>
                                    <strong class="d-block small text-dark" style="font-size: 12.5px;">Input
                                        Pendapatan</strong>
                                    <span class="text-secondary d-block" style="font-size: 10px; line-height: 1.2;">Catat
                                        pendapatan harian rayon</span>
                                </div>
                            </a>
                            <a href="{{ route('operator.prediksi.index') }}" class="quick-action-btn">
                                <i class="bi bi-cpu me-3 text-warning fs-6"></i>
                                <div>
                                    <strong class="d-block small text-dark" style="font-size: 12.5px;">Latih Model
                                        SVR</strong>
                                    <span class="text-secondary d-block" style="font-size: 10px; line-height: 1.2;">Perbarui
                                        kalkulasi prediksi</span>
                                </div>
                            </a>
                            <a href="{{ route('operator.hari-libur.index') }}" class="quick-action-btn">
                                <i class="bi bi-calendar-event me-3 text-danger fs-6"></i>
                                <div>
                                    <strong class="d-block small text-dark" style="font-size: 12.5px;">Kelola Hari
                                        Libur</strong>
                                    <span class="text-secondary d-block" style="font-size: 10px; line-height: 1.2;">Atur
                                        kalender libur daerah</span>
                                </div>
                            </a>
                            <a href="{{ route('operator.laporan.index') }}" class="quick-action-btn">
                                <i class="bi bi-file-earmark-pdf me-3 text-success fs-6"></i>
                                <div>
                                    <strong class="d-block small text-dark" style="font-size: 12.5px;">Laporan
                                        Pendapatan</strong>
                                    <span class="text-secondary d-block" style="font-size: 10px; line-height: 1.2;">Cetak
                                        atau ekspor file laporan</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Rayons Stats Card -->
                    <div class="card bg-white" style="border-radius: 12px; padding: 20px 24px;">
                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom" style="font-size: 14px;">Daftar Rayon Aktif
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            @forelse($rayons as $rayon)
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong class="d-block text-dark small"
                                                style="font-size: 12.5px;">{{ $rayon->nama_rayon }}</strong>
                                            <span class="text-secondary d-block"
                                                style="font-size: 10.5px; line-height: 1.2;">{{ $rayon->kecamatan }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary-subtle text-secondary"
                                            style="font-size: 10px;">{{ $rayon->jumlah_juru_parkir }} Jukir</span>
                                        <small class="d-block text-muted mt-1"
                                            style="font-size: 9.5px;">{{ $rayon->pendapatans_count }} entri</small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-secondary small">Belum ada data rayon terdaftar.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- closes sk-content -->

    <!-- Load Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueTrendChart').getContext('2d');

            // Gradient fill for area chart
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 260);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

            const gradientPredictGwo = ctx.createLinearGradient(0, 0, 0, 260);
            gradientPredictGwo.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
            gradientPredictGwo.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

            const gradientPredictGs = ctx.createLinearGradient(0, 0, 0, 260);
            gradientPredictGs.addColorStop(0, 'rgba(197, 90, 17, 0.08)');
            gradientPredictGs.addColorStop(1, 'rgba(197, 90, 17, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Realisasi Pendapatan',
                            data: @json($chartActualValues),
                            borderColor: '#005BAA',
                            borderWidth: 2.5,
                            backgroundColor: gradientActual,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#005BAA',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 3.5,
                            pointHoverRadius: 5
                        }
                        @if($performanceMetrics['mape_gs'] !== null)
                            , {
                                label: 'Prediksi SVR-Grid Search',
                                data: @json($chartPredictGsValues),
                                borderColor: '#C55A11',
                                borderWidth: 2.5,
                                backgroundColor: gradientPredictGs,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#C55A11',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointRadius: 3.5,
                                pointHoverRadius: 5,
                                borderDash: [4, 4]
                            }
                        @endif
                        @if($performanceMetrics['mape_gwo'] !== null)
                            , {
                                label: 'Prediksi SVR-GWO',
                                data: @json($chartPredictGwoValues),
                                borderColor: '#F4C542',
                                borderWidth: 2.5,
                                backgroundColor: gradientPredictGwo,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#F4C542',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointRadius: 3.5,
                                pointHoverRadius: 5,
                                borderDash: [4, 4]
                            }
                        @endif
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
                                boxWidth: 8,
                                padding: 15,
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            padding: 8,
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                            bodyFont: { family: 'Inter', size: 11 },
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    let val = context.raw;
                                    return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                borderDash: [5, 5],
                                color: '#e2e8f0'
                            },
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                },
                                font: {
                                    family: 'Inter',
                                    size: 10.5
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            // --- FUTURE FORECAST CHART INITIALIZATION ---
            let forecastChart = null;
            const isModelTrained = @json($performanceMetrics['mape_gwo'] !== null);

            window.changeForecastPeriod = function (days) {
                const chartContainer = document.getElementById('forecastChartContainer');
                const placeholder = document.getElementById('forecastPlaceholder');
                const placeholderTitle = document.getElementById('forecastPlaceholderTitle');
                const placeholderText = document.getElementById('forecastPlaceholderText');

                if (!isModelTrained) {
                    chartContainer.classList.add('d-none');
                    placeholder.classList.remove('d-none');
                    if (placeholderTitle) placeholderTitle.innerText = "Proyeksi Belum Tersedia";
                    if (placeholderText) {
                        placeholderText.innerText = "Model SVR belum dilatih. Harap lakukan training atau optimasi model terlebih dahulu untuk memproyeksikan estimasi pendapatan mendatang.";
                    }
                    return;
                }

                // Show loading state initially
                chartContainer.classList.add('d-none');
                placeholder.classList.remove('d-none');
                if (placeholderTitle) placeholderTitle.innerText = "Memuat Proyeksi...";
                if (placeholderText) placeholderText.innerText = "Sedang mengambil data proyeksi dari server.";

                fetch(`{{ route('operator.dashboard.forecast') }}?days=${days}`)
                    .then(async r => {
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok) {
                            throw new Error(data.message || `HTTP error! status: ${r.status}`);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            placeholder.classList.add('d-none');
                            chartContainer.classList.remove('d-none');

                            const canvasForecast = document.getElementById('futureForecastChart');
                            if (!canvasForecast) return;

                            const ctxForecast = canvasForecast.getContext('2d');

                            const gradientForecast = ctxForecast.createLinearGradient(0, 0, 0, 260);
                            gradientForecast.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
                            gradientForecast.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                            if (forecastChart) {
                                forecastChart.destroy();
                            }

                            forecastChart = new Chart(ctxForecast, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        label: 'Estimasi Pendapatan Harian',
                                        data: data.values,
                                        borderColor: '#10B981',
                                        borderWidth: 2.5,
                                        backgroundColor: gradientForecast,
                                        fill: true,
                                        tension: 0.35,
                                        pointBackgroundColor: '#10B981',
                                        pointBorderColor: '#ffffff',
                                        pointBorderWidth: 1.5,
                                        pointRadius: 3.5,
                                        pointHoverRadius: 5
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            padding: 8,
                                            backgroundColor: '#1f2937',
                                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                                            bodyFont: { family: 'Inter', size: 11 },
                                            callbacks: {
                                                label: function (context) {
                                                    let val = context.raw;
                                                    return ' Estimasi: Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                                            ticks: {
                                                callback: function (value) {
                                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                                },
                                                font: { family: 'Inter', size: 10.5 }
                                            }
                                        },
                                        x: {
                                            grid: { display: false },
                                            ticks: { font: { family: 'Inter', size: 10 } }
                                        }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Forecasting error:", err);
                        chartContainer.classList.add('d-none');
                        placeholder.classList.remove('d-none');
                        if (placeholderTitle) placeholderTitle.innerText = "Proyeksi Belum Tersedia";
                        if (placeholderText) {
                            placeholderText.innerText = err.message || 'Gagal memuat proyeksi pendapatan.';
                        }
                    });
            }

            // Trigger load initially
            changeForecastPeriod(7);
        });
    </script>
@endsection