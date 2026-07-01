@extends('layouts.app')

@section('title', 'Dashboard Kepala UPT')

@section('content')
<div class="container-fluid p-0">
    <!-- Welcome Header Banner -->
    <div class="card border-0 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="font-size: 20px; letter-spacing: -0.5px;">
                    <i class="bi bi-speedometer2 text-info me-2"></i>Dashboard Monitoring Operasional UPT
                </h4>
                <p class="mb-0 opacity-75 small" style="font-size: 12.5px;">Pantau kepatuhan setoran juru parkir, pencapaian target harian, dan analisis kontribusi per rayon secara real-time.</p>
            </div>
            <div>
                <span class="badge bg-info bg-opacity-15 text-info px-3 py-2 fw-semibold border border-info border-opacity-25" style="font-size: 11.5px; border-radius: 8px;">
                    <i class="bi bi-shield-check me-1"></i> Kepala UPT Parkir
                </span>
            </div>
        </div>
    </div>

    <!-- 1. Metrics Grid (4 Premium Cards) -->
    <div class="row g-3 mb-4">
        <!-- Metric 1: Realisasi Pendapatan Harian -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-white" style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Realisasi Harian</span>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">{{ $metrics['total_pendapatan_harian'] }}</h4>
                        <span class="text-muted small" style="font-size: 10.5px;">Seluruh rayon terinput</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Metric 2: Target SVR-GWO -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-white" style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3 me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Target SVR-GWO</span>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">{{ $metrics['hasil_prediksi_terkini'] }}</h4>
                        <span class="text-muted small" style="font-size: 10.5px;">Batas wajar estimasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Persentase Capaian Target -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-white" style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                <div class="d-flex align-items-center">
                    @php
                        $ach = (float) str_replace(['%', ','], ['', '.'], $metrics['target_achievement']);
                        $achBg = $ach >= 100 ? 'bg-success' : ($ach >= 90 ? 'bg-primary' : 'bg-warning');
                        $achText = $ach >= 100 ? 'text-success' : ($ach >= 90 ? 'text-primary' : 'text-warning');
                    @endphp
                    <div class="d-flex align-items-center justify-content-center {{ $achBg }} bg-opacity-10 {{ $achText }} rounded-3 me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-percent fs-4"></i>
                    </div>
                    <div>
                        <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Pencapaian Target</span>
                        <h4 class="fw-bold mb-1 {{ $achText }}" style="font-size: 19px; letter-spacing: -0.5px;">{{ $metrics['target_achievement'] }}</h4>
                        <span class="text-muted small" style="font-size: 10.5px;">Rasio realisasi vs SVR</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Metric 4: Rata-rata per Jukir -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-white" style="border-radius: 16px; padding: 20px; transition: all 0.2s ease;">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3 me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Setoran per Jukir</span>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 19px; letter-spacing: -0.5px;">{{ $metrics['avg_per_jukir'] }}</h4>
                        <span class="text-muted small" style="font-size: 10.5px;">Dari {{ $metrics['total_jukir'] }} total jukir</span>
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
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Grafik Tren & Realisasi Pendapatan</h5>
                        <span class="text-secondary small d-block" style="font-size: 11px;">Menampilkan perbandingan tren realisasi pendapatan harian vs target prediksi SVR-GWO (10 hari transaksi terakhir)</span>
                    </div>
                </div>
                
                <div style="height: 280px; position: relative; width: 100%;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 13.5px;">
                        <i class="bi bi-journal-text me-2 text-primary"></i>Rekomendasi & Analisis Performa Model
                    </h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">RATA-RATA REALISASI</span>
                                <strong class="text-dark d-block" style="font-size: 13.5px;">{{ $analysis['avg_actual'] }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">RATA-RATA PROYEKSI</span>
                                <strong class="d-block text-primary" style="font-size: 13.5px;">{{ $analysis['avg_predict'] }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <span class="text-secondary d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">NOMINAL DEVIASI (SELISIH)</span>
                                <strong class="text-danger d-block" style="font-size: 13.5px;">{{ $analysis['avg_deviation'] }} ({{ $analysis['percentage_deviation'] }})</strong>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-light text-dark rounded-3" style="font-size: 11.5px; border-left: 4px solid var(--primary-blue); line-height: 1.5;">
                        <i class="bi bi-info-circle-fill me-2 text-primary"></i><strong>Rekomendasi Operasional:</strong> {{ $analysis['keterangan_akurasi'] }} Rata-rata penyimpangan harian sebesar <strong>{{ $analysis['avg_deviation'] }}</strong> dapat dijadikan acuan batas toleransi wajar kebocoran setoran di lapangan.
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Rayon Performance Sidebar (4/12) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-white h-100" style="border-radius: 16px; padding: 24px;">
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Kinerja Setoran Per Rayon</h5>
                    <span class="text-secondary small d-block" style="font-size: 11px;">Kontribusi pendapatan terhadap total setoran 10 hari terakhir</span>
                </div>
                
                <div class="d-flex flex-column gap-3">
                    @foreach($rayonPerformance as $rayon)
                        <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark" style="font-size: 12.5px;">{{ $rayon['nama'] }}</span>
                                <span class="badge {{ $rayon['class'] }} px-2 py-0.5 rounded-pill" style="font-size: 9.5px; font-weight: 600;">
                                    {{ $rayon['status'] }}
                                </span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="progress mb-2" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-{{ $rayon['badge'] }}" role="progressbar" style="width: {{ $rayon['percentage'] }}%" aria-valuenow="{{ $rayon['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center style-details" style="font-size: 11px;">
                                <span class="text-secondary">{{ $rayon['jukir'] }} Jukir Terdata</span>
                                <span class="fw-bold text-dark">Rp {{ number_format($rayon['total'], 0, ',', '.') }} ({{ number_format($rayon['percentage'], 1) }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Latest Data Table -->
    <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h5 class="fw-bold text-dark mb-3" style="font-size: 15px;"><i class="bi bi-clock-history me-2 text-secondary"></i>Data Realisasi Setoran Terkini</h5>
            
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
                                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-2" style="font-size: 10.5px; font-weight: 600;">
                                        {{ $income->rayon->nama_rayon ?? 'Tidak Diketahui' }}
                                    </span>
                                </td>
                                <td class="text-dark">
                                    <i class="bi bi-people-fill text-muted me-1.5"></i>{{ $income->rayon->jumlah_juru_parkir ?? 0 }} Juru Parkir
                                </td>
                                <td class="text-end fw-bold" style="font-size: 13px; color: var(--primary-blue);">
                                    Rp {{ number_format($income->jumlah, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">Belum ada data pendapatan terinput.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                        grid: {
                            borderDash: [5, 5],
                            color: '#e2e8f0'
                        },
                        ticks: {
                            callback: function(value) {
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
