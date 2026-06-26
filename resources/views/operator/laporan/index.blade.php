@extends('layouts.app')

@section('title', 'Laporan Prediksi')
@section('subtitle', 'Halaman ini digunakan untuk melihat dan mengekspor laporan hasil prediksi pendapatan retribusi parkir.')

@section('content')
<div class="container-fluid p-0">

    <!-- Filter & Export Toolbar -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title border-0 pb-0 mb-3"><i class="bi bi-funnel me-2"></i>Filter & Ekspor Laporan</h5>
            
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rayon</label>
                    <select name="rayon_id" class="form-select form-select-sm">
                        <option value="0" {{ $rayonId == 0 ? 'selected' : '' }}>Semua Rayon</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->id }}" {{ $rayonId == $r->id ? 'selected' : '' }}>{{ $r->nama_rayon }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Laporan</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="harian" {{ request('type') == 'harian' ? 'selected' : '' }}>Laporan Harian (Rinci)</option>
                        <option value="bulanan" {{ request('type') == 'bulanan' ? 'selected' : '' }}>Laporan Bulanan (Rekap)</option>
                    </select>
                </div>
                
                <div class="col-12 mt-3 d-flex justify-content-between">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-4"><i class="bi bi-search me-1"></i> Tampilkan</button>
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('operator.laporan.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</a>
                        <a href="{{ route('operator.laporan.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Periode Laporan</span>
                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $summary['periode'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Total Baris Data</span>
                    <div class="h4 fw-bold mb-0 text-dark">{{ $summary['total_data'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Total Realisasi Aktual</span>
                    <div class="h4 fw-bold mb-0 text-dark" style="font-size: 18px; color: #10b981;">{{ $summary['total_aktual'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Total Prediksi SVR-GWO</span>
                    <div class="h4 fw-bold mb-0 text-dark" style="font-size: 18px; color: #005BAA;">{{ $summary['total_prediksi'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Grafik Tren Pendapatan dan Prediksi</h5>
            @if(count($chartActualValues) > 0)
                <div style="height: 280px; position: relative; width: 100%;">
                    <canvas id="laporanChart"></canvas>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up text-secondary d-block fs-1 mb-2"></i>
                    <span class="text-muted small d-block">Tidak ada data untuk dirender pada grafik selama periode ini.</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Table Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title border-0 pb-0 mb-3">Tabel Laporan Hasil Prediksi</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Rayon</th>
                            <th style="text-align: right;">Aktual (Rp)</th>
                            <th style="text-align: right;">Prediksi (Rp)</th>
                            <th style="text-align: right;">Error (Rp)</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($rep['tanggal'])) }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">
                                        {{ $rep['rayon'] }}
                                    </span>
                                </td>
                                <td style="text-align: right;">Rp {{ number_format($rep['aktual'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 600;">Rp {{ number_format($rep['prediksi'], 0, ',', '.') }}</td>
                                <td style="text-align: right; color: {{ $rep['error'] >= 0 ? '#10b981' : '#ef4444' }};">
                                    {{ $rep['error'] >= 0 ? '+' : '' }}Rp {{ number_format($rep['error'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <button class="btn-action" title="Detail" onclick="alert('Detail data tanggal {{ date('d-m-Y', strtotime($rep['tanggal'])) }}')"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">Tidak ada data transaksi laporan yang cocok dengan kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Placeholder -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="text-secondary small">Menampilkan 1 - {{ count($reports) }} dari {{ count($reports) }} data</div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><span class="page-link">«</span></li>
                        <li class="page-item active"><span class="page-link bg-dark border-dark text-white">1</span></li>
                        <li class="page-item disabled"><span class="page-link">»</span></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Evaluasi Model Cards -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">MAE</span>
                    <h5 class="fw-bold mb-0">{{ $metrics['mae'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">RMSE</span>
                    <h5 class="fw-bold mb-0">{{ $metrics['rmse'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">MAPE</span>
                    <h5 class="fw-bold mb-0 text-success">{{ $metrics['mape'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <span class="text-uppercase text-secondary fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">R² Score</span>
                    <h5 class="fw-bold mb-0">{{ $metrics['r2'] }}</h5>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
@if(count($chartActualValues) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('laporanChart').getContext('2d');
            
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 280);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

            const gradientPredict = ctx.createLinearGradient(0, 0, 0, 280);
            gradientPredict.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
            gradientPredict.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Realisasi Pendapatan (Aktual)',
                            data: @json($chartActualValues),
                            borderColor: '#005BAA',
                            borderWidth: 2,
                            backgroundColor: gradientActual,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#005BAA',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 2.5
                        },
                        {
                            label: 'Proyeksi Pendapatan (Prediksi SVR)',
                            data: @json($chartPredictValues),
                            borderColor: '#F4C542',
                            borderWidth: 2,
                            backgroundColor: gradientPredict,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#F4C542',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 2.5,
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
                                boxWidth: 10,
                                font: { family: 'Inter', size: 11, weight: '500' }
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
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            ticks: {
                                callback: function(value) {
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
        });
    </script>
@endif
@endsection

