@extends('layouts.app')

@section('title', 'Dashboard Kepala Dishub')

@section('content')
    <div class="container-fluid p-0">
        <!-- Welcome Header Banner -->
        <div class="card border-0 text-white mb-4 shadow-sm"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); border-radius: 16px; padding: 24px 28px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 20px; letter-spacing: -0.5px;">
                        <i class="bi bi-speedometer2 text-warning me-2"></i>Dashboard Eksekutif Kepala Dinas Perhubungan
                    </h4>
                    <p class="mb-0 opacity-75 small" style="font-size: 12.5px;">Tinjau performa pendapatan retribusi secara
                        makro, tren pertumbuhan bulanan, dan kebijakan strategis berbasis data prediksi SVR-GWO.</p>
                </div>
                <div>
                    <span
                        class="badge bg-warning bg-opacity-15 text-warning px-3 py-2 fw-semibold border border-warning border-opacity-25"
                        style="font-size: 11.5px; border-radius: 8px;">
                        <i class="bi bi-award me-1"></i> Kepala Dinas
                    </span>
                </div>
            </div>
        </div>

        {{-- Skeleton Placeholder --}}
        <div class="sk-wrapper">
            <x-ui.skeleton type="dashboard-card" />
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-8">
                    <div class="skeleton-card p-4">
                        <span class="skeleton skeleton-text lg" style="width: 240px; margin-bottom: 20px;"></span>
                        <x-ui.skeleton type="chart" height="280px" />
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="skeleton-card p-4 h-100">
                        <span class="skeleton skeleton-text lg" style="width: 150px; margin-bottom: 15px;"></span>
                        <x-ui.skeleton type="forecast" />
                    </div>
                </div>
            </div>
            <x-ui.skeleton type="table" :rows="5" />
        </div>

        {{-- Real Content --}}
        <div class="sk-content">
        <!-- 1. Metrics Grid (4 Premium Cards) -->
        <div class="row g-3 mb-4">
            <!-- Metric 1: Realisasi Pendapatan Terkini -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm bg-white"
                    style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <div>
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                style="font-size: 10px; letter-spacing: 0.5px;">Realisasi Terkini</span>
                            <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">
                                {{ $metrics['total_pendapatan_harian'] }}</h4>
                            <span class="text-muted small" style="font-size: 10.5px;">Jumlah setoran harian terakhir</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Estimasi SVR-GWO -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm bg-white"
                    style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3 me-3"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                style="font-size: 10px; letter-spacing: 0.5px;">Proyeksi Esok</span>
                            <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">
                                {{ $metrics['hasil_prediksi_terkini'] }}</h4>
                            <span class="text-muted small" style="font-size: 10.5px;">Prediksi SVR teroptimasi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 3: YTD Penerimaan Tahunan -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm bg-white"
                    style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3 me-3"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                style="font-size: 10px; letter-spacing: 0.5px;">Penerimaan YTD
                                ({{ $metrics['latest_year'] }})</span>
                            <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">
                                {{ $metrics['ytd_revenue'] }}</h4>
                            <span class="text-muted small" style="font-size: 10.5px;">Akumulasi tahun berjalan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Monthly Growth Rate -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm bg-white"
                    style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center">
                        @php
                            $isPositive = strpos($metrics['growth_rate'], '+') !== false;
                            $growthColorClass = $isPositive ? 'text-success' : 'text-danger';
                            $growthBgClass = $isPositive ? 'bg-success' : 'bg-danger';
                            $growthIcon = $isPositive ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
                        @endphp
                        <div class="d-flex align-items-center justify-content-center {{ $growthBgClass }} bg-opacity-10 {{ $growthColorClass }} rounded-3 me-3"
                            style="width: 48px; height: 48px;">
                            <i class="bi {{ $growthIcon }} fs-4"></i>
                        </div>
                        <div>
                            <span class="text-uppercase text-secondary fw-semibold d-block mb-1"
                                style="font-size: 10px; letter-spacing: 0.5px;">Tren Bulanan</span>
                            <h4 class="fw-bold mb-1 {{ $growthColorClass }}"
                                style="font-size: 19px; letter-spacing: -0.5px;">{{ $metrics['growth_rate'] }}</h4>
                            <span class="text-muted small" style="font-size: 10.5px;">Dibanding bulan lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Main Section: Chart & Sidebar -->
        <div class="row g-4 mb-4">
            <!-- Left Column: Chart & Analysis (8/12) -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm bg-white h-100" style="border-radius: 16px; padding: 24px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Grafik Tren Pendapatan & Model SVR
                            </h5>
                            <span class="text-secondary small d-block" style="font-size: 11px;">Perbandingan tren realisasi
                                pendapatan harian vs target prediksi SVR-GWO (10 hari transaksi terakhir)</span>
                        </div>
                    </div>

                    <div style="height: 280px; position: relative; width: 100%;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>

                    <!-- Strategic analysis (weekend vs weekdays) -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 13.5px;">
                            <i class="bi bi-bar-chart-steps me-2 text-primary"></i>Analisis Penerimaan Strategis (30 Hari
                            Terakhir)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <span class="text-secondary d-block mb-1"
                                        style="font-size: 10px; font-weight: 600;">KONTRIBUSI HARI KERJA (WEEKDAYS)</span>
                                    <h5 class="fw-bold text-dark mb-0">{{ $strategicStats['weekday_sum'] }} <span
                                            class="text-muted small fs-6">({{ $strategicStats['weekday_percent'] }})</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <span class="text-secondary d-block mb-1"
                                        style="font-size: 10px; font-weight: 600;">KONTRIBUSI AKHIR PEKAN (WEEKEND)</span>
                                    <h5 class="fw-bold text-dark mb-0">{{ $strategicStats['weekend_sum'] }} <span
                                            class="text-muted small fs-6">({{ $strategicStats['weekend_percent'] }})</span>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Progress bar comparing both -->
                        <div class="mt-3">
                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ str_replace('%', '', $strategicStats['weekday_percent']) }}%"
                                    aria-valuenow="{{ str_replace('%', '', $strategicStats['weekday_percent']) }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: {{ str_replace('%', '', $strategicStats['weekend_percent']) }}%"
                                    aria-valuenow="{{ str_replace('%', '', $strategicStats['weekend_percent']) }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 10.5px;">
                                <span><i class="bi bi-circle-fill text-primary me-1"></i>Hari Kerja (Weekdays)</span>
                                <span>Akhir Pekan (Weekend)<i class="bi bi-circle-fill text-warning ms-1"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Executive Strategic Insights (4/12) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm bg-white h-100"
                    style="border-radius: 16px; padding: 24px; background: linear-gradient(to bottom, #ffffff, #f8fafc);">
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Executive Policy Insights</h5>
                        <span class="text-secondary small d-block" style="font-size: 11px;">Rekomendasi strategis Dinas
                            berdasarkan pemodelan data</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <!-- Policy 1 -->
                        <div class="p-3 rounded-3 bg-white border border-light shadow-sm">
                            <h6 class="fw-bold text-dark mb-1.5 d-flex align-items-center" style="font-size: 12.5px;">
                                <i class="bi bi-shield-fill-check text-success me-2"></i>Keandalan Peramalan
                            </h6>
                            <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.5;">
                                Akurasi model saat ini berada pada tingkat <strong>{{ $metrics['akurasi_model'] }}</strong>.
                                Deviasi nominal harian dinilai sangat andal sebagai dasar perhitungan target PAD tahunan.
                            </p>
                        </div>

                        <!-- Policy 2 -->
                        <div class="p-3 rounded-3 bg-white border border-light shadow-sm">
                            <h6 class="fw-bold text-dark mb-1.5 d-flex align-items-center" style="font-size: 12.5px;">
                                <i class="bi bi-cash-stack text-primary me-2"></i>Penyetoran & Kebocoran
                            </h6>
                            <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.5;">
                                Rata-rata deviasi penyimpangan adalah <strong>{{ $analysis['avg_deviation'] }}</strong>.
                                Rentang ini merupakan batas wajar/toleransi kebocoran setoran retribusi parkir di lapangan.
                            </p>
                        </div>

                        <!-- Policy 3 -->
                        <div class="p-3 rounded-3 bg-white border border-light shadow-sm">
                            <h6 class="fw-bold text-dark mb-1.5 d-flex align-items-center" style="font-size: 12.5px;">
                                <i class="bi bi-calendar2-week text-warning me-2"></i>Potensi Akhir Pekan
                            </h6>
                            <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.5;">
                                Pendapatan akhir pekan memberikan kontribusi sebesar
                                <strong>{{ $strategicStats['weekend_percent'] }}</strong>. UPT disarankan menempatkan tim
                                wasdal ekstra untuk mengawal titik parkir akhir pekan.
                            </p>
                        </div>
                    </div>

                    <div class="mt-auto pt-3">
                        <a href="{{ route('kepala-dishub.laporan.index') }}"
                            class="btn btn-primary w-100 rounded-3 py-2 fw-medium" style="font-size: 12.5px;">
                            <i class="bi bi-file-earmark-bar-graph me-1.5"></i>Buka Laporan & Proyeksi SVR
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Latest Data Table -->
        <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3" style="font-size: 15px;"><i
                        class="bi bi-clock-history me-2 text-secondary"></i>Data Penerimaan Retribusi Terkini</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead>
                            <tr class="table-light">
                                <th style="width: 70px;">No</th>
                                <th>Tanggal</th>
                                <th>Wilayah/Rayon</th>
                                <th>Jumlah Petugas Jukir</th>
                                <th class="text-end">Total Setoran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomes as $index => $income)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-medium text-secondary">{{ date('d-m-Y', strtotime($income->tanggal)) }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-2"
                                            style="font-size: 10.5px; font-weight: 600;">
                                            {{ $income->rayon->nama_rayon ?? 'Tidak Diketahui' }}
                                        </span>
                                    </td>
                                    <td class="text-dark">
                                        <i
                                            class="bi bi-people-fill text-muted me-1.5"></i>{{ $income->rayon->jumlah_juru_parkir ?? 0 }}
                                        Juru Parkir
                                    </td>
                                    <td class="text-end fw-bold" style="font-size: 13px; color: var(--primary-blue);">
                                        Rp {{ number_format($income->jumlah, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada data pendapatan terinput.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> {{-- closes sk-content --}}
</div>

    <!-- Load Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueTrendChart').getContext('2d');

            // Gradient fill for area chart
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 280);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

            const gradientPredictGwo = ctx.createLinearGradient(0, 0, 0, 280);
            gradientPredictGwo.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
            gradientPredictGwo.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

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
                        },
                        {
                            label: 'Target SVR-GWO',
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
        });
    </script>
@endsection