@extends('layouts.app')

@section('title', 'Laporan Analisis Prediksi Pendapatan')
@section('subtitle', 'Halaman ini digunakan untuk melihat, menganalisis, dan mengekspor laporan perbandingan realisasi vs prediksi pendapatan retribusi.')

@section('content')
<div class="container-fluid p-0">

    <!-- Filter & Cetak Laporan -->
    <div class="card mb-4 bg-white shadow-sm border border-light" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title border-0 pb-0 mb-3 text-dark fw-bold" style="font-size: 14.5px;"><i class="bi bi-funnel me-2 text-primary"></i>Filter Rentang & Rayon Laporan</h5>
            
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary" style="font-size: 11px;">TANGGAL MULAI</label>
                    <input type="date" name="start_date" class="form-control" style="font-size: 12.5px;" value="{{ $startDate }}" />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary" style="font-size: 11px;">TANGGAL AKHIR</label>
                    <input type="date" name="end_date" class="form-control" style="font-size: 12.5px;" value="{{ $endDate }}" />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary" style="font-size: 11px;">RAYON PEMANTAUAN</label>
                    <select name="rayon_id" class="form-select" style="font-size: 12.5px;">
                        <option value="0" {{ $rayonId == 0 ? 'selected' : '' }}>Semua Rayon (Gabungan)</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->id }}" {{ $rayonId == $r->id ? 'selected' : '' }}>{{ $r->nama_rayon }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 py-2" style="font-size: 12px;"><i class="bi bi-search me-1"></i> Tampilkan</button>
                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary py-2" style="font-size: 12px;"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards Header and Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="text-secondary fw-bold text-uppercase m-0" style="font-size: 11px; letter-spacing: 0.5px;"><i class="bi bi-collection-fill me-2 text-primary"></i>Ringkasan Kinerja Periode</h6>
        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2" style="font-size: 11px; border-radius: 20px;">
            <i class="bi bi-calendar-event me-1"></i> Periode: <strong>{{ $summary['periode'] }}</strong>
        </span>
    </div>

    <div class="row g-3 mb-4">
        <!-- Card 1: Jumlah Data -->
        <div class="col-md-3">
            <div class="card h-100 border-start border-4 border-primary shadow-sm position-relative" style="border-radius: 8px; overflow: hidden;">
                <div class="card-body py-3">
                    <span class="text-secondary text-uppercase fw-semibold d-block" style="font-size: 9px; letter-spacing: 0.5px;">Jumlah Data Laporan</span>
                    <div class="fw-bold text-dark h5 mb-0 mt-1">{{ $summary['total_data'] }}</div>
                    <!-- Watermarked Icon -->
                    <div class="position-absolute text-primary opacity-25" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 28px;">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Realisasi Aktual -->
        <div class="col-md-3">
            <div class="card h-100 border-start border-4 border-success shadow-sm position-relative" style="border-radius: 8px; overflow: hidden;">
                <div class="card-body py-3">
                    <span class="text-secondary text-uppercase fw-semibold d-block" style="font-size: 9px; letter-spacing: 0.5px;">Total Realisasi (Aktual)</span>
                    <div class="fw-bold text-success h5 mb-0 mt-1" style="font-size: 16px;">{{ $summary['total_aktual'] }}</div>
                    <!-- Watermarked Icon -->
                    <div class="position-absolute text-success opacity-25" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 28px;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Proyeksi Target -->
        <div class="col-md-3">
            <div class="card h-100 border-start border-4 border-info shadow-sm position-relative" style="border-radius: 8px; overflow: hidden;">
                <div class="card-body py-3">
                    <span class="text-secondary text-uppercase fw-semibold d-block" style="font-size: 9px; letter-spacing: 0.5px;">Total Proyeksi (Target SVR)</span>
                    <div class="fw-bold text-info h5 mb-0 mt-1" style="font-size: 16px;">{{ $summary['total_prediksi'] }}</div>
                    <!-- Watermarked Icon -->
                    <div class="position-absolute text-info opacity-25" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 28px;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4: Rata-Rata Error (MAPE) -->
        <div class="col-md-3">
            <div class="card h-100 border-start border-4 border-dark shadow-sm bg-dark text-white position-relative" style="border-radius: 8px; overflow: hidden;">
                <div class="card-body py-3">
                    <span class="text-white-50 text-uppercase fw-semibold d-block" style="font-size: 9px; letter-spacing: 0.5px;">Rata-Rata Error (MAPE)</span>
                    <div class="fw-bold text-warning h5 mb-0 mt-1">{{ $summary['mape'] }}</div>
                    <!-- Watermarked Icon -->
                    <div class="position-absolute text-warning opacity-25" style="right: 15px; top: 50%; transform: translateY(-50%); font-size: 28px;">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Future Prediction Card -->
    <div class="row g-4 mb-4">
        <!-- Left Column: Chart & Analysis -->
        <div class="col-lg-8">
            <div class="card h-100 bg-white shadow-sm border border-light" style="border-radius: 12px; padding: 20px 24px;">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark m-0" style="font-size: 14px;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Visualisasi Tren Realisasi vs Prediksi SVR</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('kepala-upt.laporan.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm" style="font-size: 11.5px;"><i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF Laporan</a>
                        </div>
                    </div>
                    
                    @if(count($chartActualValues) > 0)
                        <div style="height: 300px; position: relative; width: 100%;">
                            <canvas id="laporanChart"></canvas>
                        </div>

                        <!-- dynamic analysis area -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3" style="font-size: 13.5px;"><i class="bi bi-journal-text me-2 text-primary"></i>Hasil Analisis Laporan & Performa Periode Ini</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 h-100 border">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; letter-spacing: 0.3px; font-weight: 500;">RATA-RATA PENDAPATAN HARIAN</span>
                                        <strong class="text-dark d-block" style="font-size: 13.5px;">{{ $analysis['avg_actual'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 h-100 border">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; letter-spacing: 0.3px; font-weight: 500;">RATA-RATA TARGET PROYEKSI SVR</span>
                                        <strong class="d-block" style="font-size: 13.5px; color: #005BAA;">{{ $analysis['avg_predict'] }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 h-100 border">
                                        <span class="text-secondary d-block mb-1" style="font-size: 10px; letter-spacing: 0.3px; font-weight: 500;">SELISIH UANG HARIAN (DEVIASI)</span>
                                        <strong class="text-danger d-block" style="font-size: 13.5px;">{{ $analysis['avg_deviation'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            @php
                                $alertType = 'alert-danger text-danger-emphasis bg-danger-subtle border-danger-subtle';
                                $iconStyle = 'bi-exclamation-triangle-fill text-danger';
                                if (strpos($analysis['status_akurasi'], 'Sangat Akurat') !== false) {
                                    $alertType = 'alert-success text-success-emphasis bg-success-subtle border-success-subtle';
                                    $iconStyle = 'bi-patch-check-fill text-success';
                                } elseif (strpos($analysis['status_akurasi'], 'Baik') !== false) {
                                    $alertType = 'alert-primary text-primary-emphasis bg-primary-subtle border-primary-subtle';
                                    $iconStyle = 'bi-check-circle-fill text-primary';
                                }
                            @endphp

                            <div class="mt-3 p-3 rounded-3 alert {{ $alertType }} border" style="font-size: 12px; line-height: 1.6;">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi {{ $iconStyle }} fs-5 mt-0.5 flex-shrink-0"></i>
                                    <div>
                                        <strong>Rekomendasi Keputusan Pimpinan (Status: {{ $analysis['status_akurasi'] }}):</strong>
                                        <p class="mb-1 mt-1">{{ $analysis['keterangan_akurasi'] }}</p>
                                        <span class="small text-secondary-emphasis">Berdasarkan data filter, rata-rata selisih nominal uang harian antara realisasi retribusi parkir dan proyeksi model adalah sebesar <strong>{{ $analysis['avg_deviation'] }}</strong> per hari.</span>
                                    </div>
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

        <!-- Right Column: Future Prediction Projections -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border-radius: 16px; border: 1px solid #e2e8f0 !important; padding: 20px 24px;">
                <div class="card-body p-0 d-flex flex-column h-100">
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
    <div class="card bg-white shadow-sm border border-light" style="border-radius: 12px;">
        <div class="card-body p-4">
            <h5 class="card-title border-0 pb-0 mb-3 text-dark fw-bold" style="font-size: 14px;"><i class="bi bi-table me-2 text-primary"></i>Tabel Rincian Harian Laporan</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead>
                        <tr class="table-light">
                            <th style="width: 65px;">No</th>
                            <th>Tanggal</th>
                            <th>Rayon</th>
                            <th style="text-align: right;">Realisasi Aktual (Rp)</th>
                            <th style="text-align: right;">Prediksi Target SVR (Rp)</th>
                            <th style="text-align: right;">Selisih Error (Rp)</th>
                            <th style="text-align: right; width: 120px;">% Kesalahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr>
                                <td>{{ $rep['no'] }}</td>
                                <td>{{ $rep['tanggal'] }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1" style="font-size: 10px;">
                                        {{ $rep['rayon'] }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 500;">Rp {{ number_format($rep['aktual'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 600; color: #005BAA;">Rp {{ number_format($rep['prediksi'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 500; color: {{ $rep['error'] >= 0 ? '#10b981' : '#ef4444' }};">
                                    {{ $rep['error'] >= 0 ? '+' : '' }}Rp {{ number_format($rep['error'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 600;" class="{{ $rep['aktual'] > 0 && abs($rep['error'])/$rep['aktual']*100 > 20 ? 'text-warning' : 'text-success' }}">
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
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="3">Total Periode Ini</td>
                                <td style="text-align: right; color: #10b981;">{{ $total_period['aktual'] }}</td>
                                <td style="text-align: right; color: #005BAA;">{{ $total_period['prediksi'] }}</td>
                                <td style="text-align: right; color: #1f2937;">{{ $total_period['error'] }}</td>
                                <td style="text-align: right; color: #1f2937;">Rerata: {{ $total_period['pct_error'] }}</td>
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
@if(count($chartActualValues) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('laporanChart').getContext('2d');
            
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 300);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

            const gradientPredict = ctx.createLinearGradient(0, 0, 0, 300);
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
