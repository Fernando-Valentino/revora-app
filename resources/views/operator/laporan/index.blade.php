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

    <!-- Chart & Future Prediction Card -->
    <div class="row g-4 mb-4">
        <!-- Left Column: Chart -->
        <div class="col-lg-8">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="card-title border-0 pb-0 mb-4 fw-bold text-dark" style="font-size: 15px;">
                        <i class="bi bi-graph-up text-primary-custom me-2"></i>Grafik Tren Pendapatan dan Prediksi
                    </h5>
                    @if(count($chartActualValues) > 0)
                        <div style="height: 310px; position: relative; width: 100%;">
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
        </div>

        <!-- Right Column: Future Prediction Projections -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border-radius: 16px;">
                <div class="card-body p-4 d-flex flex-column h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase text-secondary fw-bold mb-0" style="font-size: 11px; letter-spacing: 1px;">
                            <i class="bi bi-graph-up-arrow text-primary-custom me-1"></i> Proyeksi Pendapatan SVR
                        </h6>
                        <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill" style="font-size: 9px; font-weight: 600;">7 Hari Ke Depan</span>
                    </div>

                    @if($futureForecast)
                        <div class="mb-4">
                            <span class="text-muted small d-block mb-1">Estimasi Total Pendapatan</span>
                            <h3 class="fw-extrabold text-primary-custom mb-1" style="font-size: 26px;">{{ $futureForecast['total_predicted'] }}</h3>
                            <span class="text-secondary small">
                                Rata-rata: <strong>{{ $futureForecast['avg_predicted'] }}</strong> / hari
                            </span>
                        </div>

                        <!-- Mini List Harian -->
                        <div class="mb-4 flex-grow-1">
                            <span class="text-muted d-block small mb-2 fw-semibold">Proyeksi Harian ({{ $futureForecast['start_date'] }} - {{ $futureForecast['end_date'] }})</span>
                            <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                @foreach($futureForecast['detail_harian'] as $forecastDay)
                                    @php
                                        $dayOfWeek = (int) date('N', strtotime($forecastDay['tanggal']));
                                        $isWeekend = $dayOfWeek >= 6;
                                        $dayName = $isWeekend ? (date('N', strtotime($forecastDay['tanggal'])) == 6 ? 'Sabtu' : 'Minggu') : (date('N', strtotime($forecastDay['tanggal'])) == 1 ? 'Senin' : (date('N', strtotime($forecastDay['tanggal'])) == 2 ? 'Selasa' : (date('N', strtotime($forecastDay['tanggal'])) == 3 ? 'Rabu' : (date('N', strtotime($forecastDay['tanggal'])) == 4 ? 'Kamis' : 'Jumat'))));
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-2" style="background: rgba(241, 245, 249, 0.6); font-size: 11.5px; border: 1px solid #f1f5f9;">
                                        <span class="text-dark fw-medium">
                                            {{ $dayName }}, {{ date('d M', strtotime($forecastDay['tanggal'])) }}
                                            @if($isWeekend)
                                                <span class="badge bg-warning-subtle text-warning px-1.5 py-0.5 rounded-pill ms-1" style="font-size: 8px;">Weekend</span>
                                            @endif
                                        </span>
                                        <span class="fw-bold text-primary-custom">Rp {{ number_format($forecastDay['pendapatan'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Rekomendasi AI -->
                        <div class="p-3 rounded-3" style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.15);">
                            <h6 class="fw-bold text-info-custom d-flex align-items-center mb-2" style="font-size: 12px;">
                                <i class="bi bi-lightbulb-fill me-1.5 text-warning animate-pulse"></i> Rekomendasi Sistem (SVR-GWO):
                            </h6>
                            <ul class="mb-0 ps-3 text-secondary" style="font-size: 11px; line-height: 1.5;">
                                @foreach($futureForecast['recommendations'] as $rec)
                                    <li class="mb-1">{{ $rec }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-5 my-auto">
                            <i class="bi bi-robot text-muted d-block fs-1 mb-2"></i>
                            <span class="text-muted small d-block">Gagal memuat proyeksi masa depan. Pastikan server FastAPI Python Anda aktif di port 8000.</span>
                        </div>
                    @endif
                </div>
            </div>
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

    <!-- Analisis Akurasi Per Rayon -->
    @if(count($reports) > 0)
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-grid-3x3-gap-fill text-success me-2"></i>Analisis Akurasi Prediksi Per Rayon</h5>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" style="font-size: 10px;">Berdasarkan Periode yang Difilter</span>
            </div>

            <div class="row g-3 mb-4">
                <!-- Best Rayon -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 h-100 border border-success-subtle bg-success-subtle">
                        <span class="text-uppercase fw-semibold d-block text-secondary mb-1" style="font-size: 9px; letter-spacing: 0.4px;">Rayon Paling Presisi (Lowest Error)</span>
                        @if($bestRayon)
                            <div class="fw-bold text-success mb-1" style="font-size: 17px;">{{ $bestRayon->rayon_name }}</div>
                            <span class="d-block small text-dark">Rata-rata MAPE: <strong>{{ number_format(abs($bestRayon->avg_mape), 2, ',', '.') }}%</strong></span>
                            <span class="d-block mt-1" style="font-size: 10.5px; color: #555;">Akurasi peramalan di wilayah ini dinilai sangat andal.</span>
                        @else
                            <span class="text-muted small">Data tidak tersedia</span>
                        @endif
                    </div>
                </div>

                <!-- Worst Rayon -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 h-100 border border-danger-subtle bg-danger-subtle">
                        <span class="text-uppercase fw-semibold d-block text-secondary mb-1" style="font-size: 9px; letter-spacing: 0.4px;">Rayon dengan Deviasi Terbesar</span>
                        @if($worstRayon)
                            <div class="fw-bold text-danger mb-1" style="font-size: 17px;">{{ $worstRayon->rayon_name }}</div>
                            <span class="d-block small text-dark">Rata-rata MAPE: <strong>{{ number_format(abs($worstRayon->avg_mape), 2, ',', '.') }}%</strong></span>
                            <span class="d-block mt-1" style="font-size: 10.5px; color: #555;">Deviasi dipengaruhi fluktuasi transaksi harian yang kurang stabil.</span>
                        @else
                            <span class="text-muted small">Data tidak tersedia</span>
                        @endif
                    </div>
                </div>

                <!-- Avg Daily Deviation -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 h-100 border bg-light">
                        <span class="text-uppercase fw-semibold d-block text-secondary mb-1" style="font-size: 9px; letter-spacing: 0.4px;">Rata-Rata Selisih Uang (Deviasi Nominal Harian)</span>
                        <div class="fw-bold text-dark mb-1" style="font-size: 17px;">Rp {{ number_format(abs($avgDailyDeviation), 0, ',', '.') }} / hari</div>
                        <span class="d-block small text-secondary">Rata-rata selisih perkiraan dalam rupiah per hari.</span>
                        <span class="d-block mt-1" style="font-size: 10.5px; color: #555;">Digunakan sebagai acuan batas wajar selisih setoran juru parkir di lapangan.</span>
                    </div>
                </div>
            </div>

            @if($rayonStats->count() > 0)
            <!-- Tabel Breakdown Per Rayon -->
            <div class="border-top pt-3">
                <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;"><i class="bi bi-table me-2 text-primary"></i>Rincian Kinerja Seluruh Rayon</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th>Rayon</th>
                                <th style="text-align: right;">Total Aktual (Rp)</th>
                                <th style="text-align: right;">Total Prediksi (Rp)</th>
                                <th style="text-align: right;">Avg Error (Rp)</th>
                                <th style="text-align: right; width: 120px;">Rata-rata MAPE</th>
                                <th style="text-align: center; width: 110px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rayonStats->sortBy('avg_mape') as $rs)
                                @php
                                    $mapeVal = abs($rs->avg_mape);
                                    $statusClass = $mapeVal < 10 ? 'text-success' : ($mapeVal <= 20 ? 'text-primary' : 'text-danger');
                                    $statusText  = $mapeVal < 10 ? 'Sangat Akurat' : ($mapeVal <= 20 ? 'Baik' : 'Perlu Perhatian');
                                    $badgeClass  = $mapeVal < 10 ? 'bg-success-subtle text-success' : ($mapeVal <= 20 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger');
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">{{ $rs->rayon_name }}</span>
                                    </td>
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
    @endif

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

