@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid p-0">
    <!-- 1. Metrics Grid (Bootstrap Row) -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Total Pendapatan Harian</span>
                    <div class="h2 fw-bold text-dark mb-1">{{ $metrics['total_pendapatan_harian'] }}</div>
                    <span class="text-secondary small"><i class="bi bi-info-circle me-1"></i>Realisasi hari terkini (Seluruh Rayon)</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Hasil Prediksi Terkini</span>
                    <div class="h2 fw-bold text-dark mb-1">{{ $metrics['hasil_prediksi_terkini'] }}</div>
                    <span class="text-secondary small"><i class="bi bi-graph-up me-1"></i>Proyeksi model SVR-GWO</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Akurasi Model (MAPE)</span>
                    <div class="h2 fw-bold text-dark mb-1">{{ $metrics['akurasi_model'] }}</div>
                    <span class="text-secondary small"><i class="bi bi-check-circle me-1"></i>Pelatihan model terakhir</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Large Chart Section -->
    <div class="card mb-4 bg-white" style="border-radius: 12px; padding: 20px 24px;">
        <div class="card-body p-0">
            <h5 class="fw-bold mb-2 text-dark" style="font-size: 14px;">Grafik Tren Pendapatan Retribusi Parkir</h5>
            <span class="text-secondary small d-block mb-3" style="font-size: 11px;">Menampilkan perbandingan tren realisasi vs prediksi SVR-GWO (10 hari transaksi terakhir)</span>
            <div style="height: 320px; position: relative; width: 100%;">
                <canvas id="revenueTrendChart"></canvas>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;"><i class="bi bi-journal-text me-2 text-primary"></i>Hasil Analisis Tren & Performa Model (Dasar Keputusan UPT)</h6>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.3px; font-weight: 500;">RATA-RATA REALISASI</span>
                            <strong class="text-dark d-block" style="font-size: 14px;">{{ $analysis['avg_actual'] }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.3px; font-weight: 500;">RATA-RATA PROYEKSI</span>
                            <strong class="d-block" style="font-size: 14px; color: #005BAA;">{{ $analysis['avg_predict'] }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.3px; font-weight: 500;">PENYIMPANGAN NOMINAL</span>
                            <strong class="text-danger d-block" style="font-size: 14px;">{{ $analysis['avg_deviation'] }} ({{ $analysis['percentage_deviation'] }})</strong>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary d-block mb-1" style="font-size: 10.5px; letter-spacing: 0.3px; font-weight: 500;">REKOMENDASI MODEL</span>
                            <strong class="text-success d-block" style="font-size: 14px;">{{ $analysis['status_akurasi'] }}</strong>
                        </div>
                    </div>
                </div>
                <div class="mt-3 p-3 bg-light text-dark rounded-3" style="font-size: 12px; border-left: 4px solid #005BAA;">
                    <i class="bi bi-info-circle-fill me-2 text-primary"></i><strong>Penjelasan Pengambilan Keputusan:</strong> {{ $analysis['keterangan_akurasi'] }} Berdasarkan data 10 hari transaksi terakhir, rata-rata nominal deviasi (selisih uang) harian adalah sebesar <strong>{{ $analysis['avg_deviation'] }}</strong>. Hasil ini dinilai andal untuk mendukung penetapan sasaran retribusi parkir harian di lapangan.
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Latest Data Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Data Pendapatan Terkini</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead>
                        <tr class="table-light">
                            <th style="width: 65px;">No</th>
                            <th>Tanggal</th>
                            <th>Rayon</th>
                            <th>Juru Parkir</th>
                            <th style="text-align: right;">Jumlah Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $index => $income)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d-m-Y', strtotime($income->tanggal)) }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">
                                        {{ $income->rayon->nama_rayon ?? 'Tidak Diketahui' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $income->juruParkir->jumlah_juru_parkir ?? ($income->rayon->jumlah_juru_parkir ?? 0) }} Jukir
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary-blue);">
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
        const gradientActual = ctx.createLinearGradient(0, 0, 0, 320);
        gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
        gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

        const gradientPredictGwo = ctx.createLinearGradient(0, 0, 0, 320);
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
