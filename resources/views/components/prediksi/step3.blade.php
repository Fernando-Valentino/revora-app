@props([
    'lastRun',
    'datasetReady',
    'pipelineData' => null,
    'params' => null,
])

<!-- STEP 3 CONTENT -->
<div id="step-content-3" class="step-content-section d-none">
    <div class="row g-4 mb-4">
        <!-- Panel Eksekusi (7 Langkah) -->
        <div class="col-md-5">
            <div class="card h-100 bg-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-dark"><i class="bi bi-cpu-fill me-2 text-primary-custom"></i>Status & Eksekusi Model</h5>
                        <p class="text-muted small mb-3">Sistem akan memanggil Python API untuk melakukan pemrosesan data, rekayasa fitur, dan pelatihan model SVR secara berurutan.</p>
                        
                        <div class="progress-steps-list">
                            @php
                                $isExecuted = isset($lastRun) && $lastRun->status == 'success';
                                $stepClass = $isExecuted ? 'success-step' : '';
                                $iconHtml = $isExecuted ? '<i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i>' : '<i class="bi bi-circle"></i>';
                                $iconClass = $isExecuted ? 'step-icon me-2' : 'step-icon me-2 text-muted';
                            @endphp
                            <div class="progress-step {{ $stepClass }}" id="step-1">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">1. Pembersihan Data (Data Cleaning)</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-2">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">2. Rekayasa Fitur (Feature Engineering)</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-3">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">3. Transformasi Data</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-4">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">4. Normalisasi Data</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-5">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">5. Pembagian Data (Split Data 80:20)</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-6">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">6. Pelatihan Model SVR</span>
                            </div>
                            <div class="progress-step {{ $stepClass }}" id="step-7">
                                <span class="{{ $iconClass }}">{!! $iconHtml !!}</span>
                                <span class="step-label">7. Prediksi Pendapatan</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button id="btnJalankanSvrProses" class="btn btn-dark w-100 py-3 rounded-3 fw-bold fs-6 shadow-sm" style="letter-spacing: 0.5px;" {{ !$datasetReady ? 'disabled' : '' }}>
                            <i class="bi bi-play-fill me-1"></i> Generate Prediksi SVR
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Informasi Eksekusi -->
        <div class="col-md-7">
            <div class="card h-100 bg-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-dark"><i class="bi bi-info-square-fill me-2 text-primary-custom"></i>Informasi Eksekusi Model</h5>
                        <div class="alert alert-info border-0 bg-light-subtle rounded-3 py-3 px-3 mb-3 text-sm">
                            <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                            Proses pelatihan akan memakan waktu kurang lebih 5-10 detik. Pastikan layanan Python API berjalan pada port <code>8001</code>.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm text-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="width: 40%;">Metode Model</td>
                                        <td>: Support Vector Regression (SVR)</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary">Tipe Kernel</td>
                                        <td>: Radial Basis Function (RBF)</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary">Rasio Split Data</td>
                                        <td>: 80% Training (Masa Lalu), 20% Testing (Masa Depan)</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary">Status Terakhir</td>
                                        <td>: 
                                            @if($lastRun)
                                                <span class="badge bg-success-subtle text-success border-0"><i class="bi bi-check-circle-fill me-1"></i>Sudah Dijalankan</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border-0"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Dijalankan</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($lastRun)
                                        <tr>
                                            <td class="fw-semibold text-secondary">Eksekusi Terakhir</td>
                                            <td>: {{ Carbon\Carbon::parse($lastRun->finished_at)->translatedFormat('d F Y') }}, {{ Carbon\Carbon::parse($lastRun->finished_at)->format('H:i:s') }} WIB</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-secondary">Total Data Efektif</td>
                                            <td>: {{ number_format($lastRun->train_rows + $lastRun->test_rows, 0, ',', '.') }} baris <span class="text-muted" style="font-size:11px;">(setelah lag, mentah: {{ number_format($lastRun->total_rows, 0, ',', '.') }} baris)</span></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($lastRun)
                        <div class="p-3 bg-success-subtle rounded text-dark small border-0 mt-3 d-flex align-items-center">
                            <i class="bi bi-patch-check-fill text-success me-2 fs-5"></i>
                            <div>
                                <strong>Model SVR Siap.</strong> Hasil evaluasi model terakhir dapat diakses langsung pada Langkah 4 (Hasil Evaluasi).
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-light rounded text-muted small border-start border-4 border-warning mt-3 d-flex align-items-center">
                            <i class="bi bi-exclamation-circle-fill text-warning me-2 fs-5"></i>
                            <div>
                                Silakan klik tombol <strong>Generate Prediksi SVR</strong> untuk melatih model dan melihat hasil evaluasi.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Alur Preprocessing Card -->
    <div class="card mb-4 bg-white shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-dark"><i class="bi bi-diagram-3-fill me-2 text-primary-custom"></i>Detail Alur Preprocessing & Pipeline Data</h5>
            @if(!$lastRun)
                <div class="text-center py-5">
                    <i class="bi bi-lock-fill text-muted mb-3 d-block" style="font-size: 40px;"></i>
                    <h6 class="fw-semibold text-secondary">Detail Alur Preprocessing Belum Tersedia</h6>
                    <p class="text-muted small mb-0 px-4">Detail alur preprocessing dan pipeline data hanya dapat ditampilkan setelah model SVR berhasil dilatih. Silakan klik tombol <strong>Generate Prediksi SVR</strong> di atas terlebih dahulu.</p>
                </div>
            @else
                <p class="text-secondary small mb-4">Klik setiap tahapan di bawah ini untuk melihat detail data sebelum/sesudah transformasi, rumusan, dan contoh visualisasi.</p>
                
                <div class="d-flex align-items-start flex-column flex-md-row gap-3">
                <!-- Tabs List -->
                <div class="nav flex-column nav-pills me-2 col-12 col-md-4 col-lg-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active" id="v-pills-cleaning-tab" data-bs-toggle="pill" data-bs-target="#v-pills-cleaning" type="button" role="tab" aria-controls="v-pills-cleaning" aria-selected="true">
                        <span class="tab-step-number">1</span> 1. Pembersihan Data
                    </button>
                    <button class="nav-link" id="v-pills-fe-tab" data-bs-toggle="pill" data-bs-target="#v-pills-fe" type="button" role="tab" aria-controls="v-pills-fe" aria-selected="false">
                        <span class="tab-step-number">2</span> 2. Rekayasa Fitur
                    </button>
                    <button class="nav-link" id="v-pills-target-tab" data-bs-toggle="pill" data-bs-target="#v-pills-target" type="button" role="tab" aria-controls="v-pills-target" aria-selected="false">
                        <span class="tab-step-number">3</span> 3. Transformasi Data
                    </button>
                    <button class="nav-link" id="v-pills-normalisasi-tab" data-bs-toggle="pill" data-bs-target="#v-pills-normalisasi" type="button" role="tab" aria-controls="v-pills-normalisasi" aria-selected="false">
                        <span class="tab-step-number">4</span> 4. Normalisasi Data
                    </button>
                    <button class="nav-link" id="v-pills-split-tab" data-bs-toggle="pill" data-bs-target="#v-pills-split" type="button" role="tab" aria-controls="v-pills-split" aria-selected="false">
                        <span class="tab-step-number">5</span> 5. Pembagian Data (Split)
                    </button>
                    <button class="nav-link" id="v-pills-training-tab" data-bs-toggle="pill" data-bs-target="#v-pills-training" type="button" role="tab" aria-controls="v-pills-training" aria-selected="false">
                        <span class="tab-step-number">6</span> 6. Pelatihan Model SVR
                    </button>
                    <button class="nav-link" id="v-pills-prediction-tab" data-bs-toggle="pill" data-bs-target="#v-pills-prediction" type="button" role="tab" aria-controls="v-pills-prediction" aria-selected="false">
                        <span class="tab-step-number">7</span> 7. Prediksi Pendapatan
                    </button>
                </div>
                
                <!-- Tabs Content -->
                <div class="tab-content flex-grow-1 col-12 col-md-8 col-lg-9 border p-4 rounded-3" id="v-pills-tabContent" style="background-color: #ffffff;">
                                            @php
                        $rayonRomanMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
                    @endphp

                    <!-- Tab 1: Pembersihan Data -->
                    <div class="tab-pane fade show active" id="v-pills-cleaning" role="tabpanel" aria-labelledby="v-pills-cleaning-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 1: Pembersihan Data (Data Cleaning)</h5>
                            <span class="badge bg-success-subtle text-success py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">FILTER & IMPUTATION</span>
                        </div>
                        <p class="text-secondary text-sm">Sebelum pembersihan, dataset asli (data mentah) dibaca dari database. Jika terdapat pencatatan dengan pendapatan bernilai Rp 0, sistem mendeteksi dan membersihkan distorsi tersebut dengan aturan bisnis berikut:</p>
                        
                        <div class="row g-3 mb-4">
                            <!-- Drop Rule -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border border-danger-subtle h-100 bg-white shadow-sm" style="border-left: 4px solid #dc2626 !important;">
                                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-trash3-fill me-1"></i>1. Penghapusan Baris Pendapatan 0 pada Hari Kerja Biasa</h6>
                                    <p class="text-secondary text-xs mb-0">Baris data bernilai <strong>Rp 0 pada hari kerja biasa</strong> (bukan libur nasional) akan <strong>dihapus (dropped)</strong> karena diindikasikan sebagai kesalahan pencatatan atau juru parkir absen menyetor.</p>
                                </div>
                            </div>
                            <!-- Impute Rule -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border border-success-subtle h-100 bg-white shadow-sm" style="border-left: 4px solid #16a34a !important;">
                                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-magic me-1"></i>2. Imputasi Median pada Hari Libur Nasional</h6>
                                    <p class="text-secondary text-xs mb-0">Jika pendapatan Rp 0 terjadi pada <strong>Hari Libur Nasional</strong>, data tidak dihapus, melainkan diisi (imputasi) menggunakan <strong>nilai median pendapatan hari libur</strong> yang bernilai positif.</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($pipelineData && isset($pipelineData['raw_snapshot']) && isset($pipelineData['cleaned_snapshot']))
                            <div class="card border border-light shadow-sm">
                                <div class="card-header bg-light py-1.5 px-3">
                                    <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="cleaning-snapshot-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active fw-bold text-xs py-1.5 px-3 border-0 bg-transparent text-secondary" id="raw-snapshot-tab" data-bs-toggle="tab" data-bs-target="#raw-snapshot-pane" type="button" role="tab" aria-controls="raw-snapshot-pane" aria-selected="true" style="transition: all 0.2s;">
                                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>1. Sebelum Pembersihan (Raw Data)
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link fw-bold text-xs py-1.5 px-3 border-0 bg-transparent text-secondary" id="cleaned-snapshot-tab" data-bs-toggle="tab" data-bs-target="#cleaned-snapshot-pane" type="button" role="tab" aria-controls="cleaned-snapshot-pane" aria-selected="false" style="transition: all 0.2s;">
                                                <i class="bi bi-shield-check me-1"></i>2. Setelah Pembersihan (Cleaned Data)
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content" id="cleaning-snapshot-tabsContent">
                                    <!-- Tab 1.1: Sebelum Pembersihan -->
                                    <div class="tab-pane fade show active" id="raw-snapshot-pane" role="tabpanel" aria-labelledby="raw-snapshot-tab">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0 table-hover table-preview-custom">
                                                <thead class="table-light">
                                                    <tr class="text-secondary text-xs">
                                                        <th>Tanggal</th>
                                                        <th>Rayon</th>
                                                        <th class="text-center">Weekend</th>
                                                        <th class="text-center">Libur</th>
                                                        <th class="text-end">Pendapatan Awal</th>
                                                        <th class="text-end">Jukir</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-xs">
                                                    @foreach($pipelineData['raw_snapshot'] as $snap)
                                                        @php
                                                            $isZero = $snap['Total_Pendapatan'] == 0;
                                                            $isLibur = $snap['Libur_Nasional'] == 1;
                                                            $isWeekend = $snap['Weekend'] == 1;
                                                            $rowBg = '';
                                                            $statusBadge = '';
                                                            if ($isZero) {
                                                                if ($isLibur) {
                                                                    $rowBg = 'table-warning-custom bg-warning-subtle text-warning-emphasis';
                                                                    $statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-magic me-1"></i>Akan Diimputasi</span>';
                                                                } else {
                                                                    $rowBg = 'table-danger-custom bg-danger-subtle text-danger-emphasis';
                                                                    $statusBadge = '<span class="badge bg-danger"><i class="bi bi-trash-fill me-1"></i>Akan Dihapus</span>';
                                                                }
                                                            } else {
                                                                $statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Valid</span>';
                                                            }
                                                        @endphp
                                                        <tr class="{{ $rowBg }}">
                                                            <td><code>{{ $snap['Tanggal'] }}</code></td>
                                                            <td><span class="badge bg-light text-dark border">Rayon {{ $rayonRomanMap[$snap['Rayon']] ?? $snap['Rayon'] }}</span></td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $isWeekend ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">
                                                                    {{ $isWeekend ? '1' : '0' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $isLibur ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">
                                                                    {{ $isLibur ? '1' : '0' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end fw-semibold {{ $isZero ? 'text-danger' : 'text-dark' }}">
                                                                Rp {{ number_format($snap['Total_Pendapatan'], 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-end text-secondary">{{ $snap['Jumlah Jukir'] ?? ($snap['Jumlah_Jukir'] ?? 80) }} Jukir</td>
                                                            <td class="text-center">{!! $statusBadge !!}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- Tab 1.2: Setelah Pembersihan -->
                                    <div class="tab-pane fade" id="cleaned-snapshot-pane" role="tabpanel" aria-labelledby="cleaned-snapshot-tab">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0 table-hover table-preview-custom">
                                                <thead class="table-light">
                                                    <tr class="text-secondary text-xs">
                                                        <th>Tanggal</th>
                                                        <th>Rayon</th>
                                                        <th class="text-center">Weekend</th>
                                                        <th class="text-center">Libur</th>
                                                        <th class="text-end">Pendapatan Bersih</th>
                                                        <th class="text-end">Jukir</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-xs">
                                                    @foreach($pipelineData['cleaned_snapshot'] as $snap)
                                                        @php
                                                            $isLibur = $snap['Libur_Nasional'] == 1;
                                                            $isWeekend = $snap['Weekend'] == 1;
                                                        @endphp
                                                        <tr>
                                                            <td><code>{{ $snap['Tanggal'] }}</code></td>
                                                            <td><span class="badge bg-light text-dark border">Rayon {{ $rayonRomanMap[$snap['Rayon']] ?? $snap['Rayon'] }}</span></td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $isWeekend ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">
                                                                    {{ $isWeekend ? '1' : '0' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $isLibur ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">
                                                                    {{ $isLibur ? '1' : '0' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end fw-semibold text-success">
                                                                Rp {{ number_format($snap['Total_Pendapatan'], 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-end text-secondary">{{ $snap['Jumlah Jukir'] ?? ($snap['Jumlah_Jukir'] ?? 80) }} Jukir</td>
                                                            <td class="text-center">
                                                                <span class="badge bg-success-subtle text-success border-0 py-1 px-2.5">
                                                                    <i class="bi bi-check-circle-fill me-1"></i>Bersih
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0 mt-3 text-xs border-0">
                                <i class="bi bi-info-circle me-1"></i> Detail data snapshot pembersihan tidak ditemukan. Silakan jalankan generate ulang model.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tab 2: Rekayasa Fitur -->
                    <div class="tab-pane fade" id="v-pills-fe" role="tabpanel" aria-labelledby="v-pills-fe-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 2: Rekayasa Fitur (Feature Engineering)</h5>
                            <span class="badge bg-primary-subtle text-primary-custom py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">FEATURE ENGINEERING</span>
                        </div>
                        <p class="text-secondary text-sm">Menambah dimensi fitur masukan agar model SVR mampu menangkap pengaruh tren waktu (temporal), efek hari libur, serta karakteristik spasial (rayon):</p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card h-100 mb-0 border bg-white p-3 shadow-sm" style="border-top: 3px solid #0d6efd !important;">
                                    <h6 class="fw-bold text-dark text-sm mb-2"><i class="bi bi-calendar3 text-primary-custom me-1"></i>Fitur Temporal & Siklikal</h6>
                                    <p class="text-muted text-xs mb-0">Ekstraksi hari, bulan, tahun, nomor minggu, serta transformasi sine & cosine pada hari-dalam-minggu dan tanggal-kalender untuk menangkap kontinuitas siklus waktu.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 mb-0 border bg-white p-3 shadow-sm" style="border-top: 3px solid #0dcaf0 !important;">
                                    <h6 class="fw-bold text-dark text-sm mb-2"><i class="bi bi-clock-history text-primary-custom me-1"></i>Fitur Lag & Rolling Mean</h6>
                                    <p class="text-muted text-xs mb-0">Menyisipkan variabel historis <code>Lag_1</code>, <code>Lag_7</code>, <code>Lag_14</code>, dan <code>Lag_21</code> (pendapatan pada hari-hari sebelumnya) serta rolling mean 7 dan 30 hari untuk mendeteksi tren terkini.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 mb-0 border bg-white p-3 shadow-sm" style="border-top: 3px solid #212529 !important;">
                                    <h6 class="fw-bold text-dark text-sm mb-2"><i class="bi bi-geo-alt-fill text-primary-custom me-1"></i>Rayon Dummy & Interaksi</h6>
                                    <p class="text-muted text-xs mb-0">One-hot encoding data Rayon menjadi 5 kolom dummy terpisah (Rayon_1 s.d Rayon_5) serta perkalian interaksi <code>Weekend * Rayon_X</code> untuk melihat perbedaan efek libur di tiap rayon.</p>
                                </div>
                            </div>
                        </div>

                        @if($pipelineData && isset($pipelineData['fe_snapshot']))
                            <div class="card border border-light shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Hasil Ekstraksi & Rekayasa Fitur (Sample Data)</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 table-hover table-preview-custom">
                                        <thead class="table-light">
                                            <tr class="text-secondary text-xs">
                                                <th>Tanggal</th>
                                                <th>Rayon</th>
                                                <th class="text-center">Wk</th>
                                                <th class="text-center">Lb</th>
                                                <th class="text-center">Sin Hari</th>
                                                <th class="text-center">Cos Hari</th>
                                                <th class="text-end">Lag 1</th>
                                                <th class="text-end">Lag 7</th>
                                                <th class="text-end">Roll Mean 7</th>
                                                <th class="text-end">Roll Mean 30</th>
                                                <th class="text-center">Dummy R1</th>
                                                <th class="text-center">Wk×R1</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @foreach($pipelineData['fe_snapshot'] as $snap)
                                                <tr>
                                                    <td><code>{{ $snap['Tanggal'] }}</code></td>
                                                    <td><span class="badge bg-light text-dark border">Rayon {{ $rayonRomanMap[$snap['Rayon_asli']] ?? $snap['Rayon_asli'] }}</span></td>
                                                    <td class="text-center">{{ $snap['Weekend'] }}</td>
                                                    <td class="text-center">{{ $snap['Libur_Nasional'] }}</td>
                                                    <td class="text-center text-secondary">{{ number_format($snap['Hari_Minggu_sin'], 4, ',', '.') }}</td>
                                                    <td class="text-center text-secondary">{{ number_format($snap['Hari_Minggu_cos'], 4, ',', '.') }}</td>
                                                    <td class="text-end fw-semibold">Rp {{ number_format($snap['Lag_1'], 0, ',', '.') }}</td>
                                                    <td class="text-end text-secondary">Rp {{ number_format($snap['Lag_7'], 0, ',', '.') }}</td>
                                                    <td class="text-end text-success">Rp {{ number_format($snap['Rolling_Mean_7'], 0, ',', '.') }}</td>
                                                    <td class="text-end text-success text-secondary">Rp {{ number_format($snap['Rolling_Mean_30'], 0, ',', '.') }}</td>
                                                    <td class="text-center">{{ $snap['Rayon_1'] }}</td>
                                                    <td class="text-center text-secondary">{{ $snap['Weekend_Rayon_1'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0 text-xs border-0">
                                <i class="bi bi-info-circle me-1"></i> Detail data rekayasa fitur tidak ditemukan. Silakan jalankan generate ulang model.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tab 3: Transformasi Data -->
                    <div class="tab-pane fade" id="v-pills-target" role="tabpanel" aria-labelledby="v-pills-target-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 3: Transformasi Data</h5>
                            <span class="badge bg-warning-subtle text-warning py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">DATA TRANSFORMATION</span>
                        </div>
                        <p class="text-secondary text-sm">Distribusi data retribusi parkir harian memiliki tingkat kemiringan (skewness) tinggi dengan pencilan ekstrem. Dilakukan transformasi target untuk menstabilkan variansi model:</p>
                        
                        <div class="bg-white p-3 rounded-3 border mb-4 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
                            <h6 class="fw-bold text-dark mb-2 text-sm"><i class="bi bi-calculator me-1"></i>Rumus Transformasi Logaritmik:</h6>
                            <div class="text-center py-2 bg-light rounded my-2">
                                <code class="fs-5 text-dark">y_transformed = ln(y + 1)</code>
                            </div>
                            <p class="text-secondary text-xs mb-0">Menggunakan fungsi <code>log1p</code> untuk menangani nilai nol dengan aman. Setelah prediksi selesai didapatkan dari model SVR, nilai dikembalikan ke rupiah asli menggunakan fungsi inverse eksponensial <code>expm1</code>: <code>y_original = exp(y_predicted) - 1</code>.</p>
                        </div>

                        @if($pipelineData && isset($pipelineData['transformed_snapshot']))
                            <div class="card border border-light shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Hasil Transformasi Target y → ln(y + 1)</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 table-hover table-preview-custom">
                                        <thead class="table-light">
                                            <tr class="text-secondary text-xs">
                                                <th>Tanggal</th>
                                                <th>Rayon</th>
                                                <th class="text-end">Pendapatan Riil (y)</th>
                                                <th class="text-end">Log Transformed (y_transformed)</th>
                                                <th class="text-center">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @foreach($pipelineData['transformed_snapshot'] as $snap)
                                                <tr>
                                                    <td><code>{{ $snap['Tanggal'] }}</code></td>
                                                    <td><span class="badge bg-light text-dark border">Rayon {{ $rayonRomanMap[$snap['Rayon_asli']] ?? $snap['Rayon_asli'] }}</span></td>
                                                    <td class="text-end fw-semibold text-dark">Rp {{ number_format($snap['Total_Pendapatan'], 0, ',', '.') }}</td>
                                                    <td class="text-end text-primary fw-mono fw-semibold">{{ number_format($snap['Total_Pendapatan_log'], 6, ',', '.') }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-subtle text-primary border-0 py-1 px-2" style="font-size: 10px;">
                                                            ln({{ number_format($snap['Total_Pendapatan']) }} + 1)
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0 text-xs border-0">
                                <i class="bi bi-info-circle me-1"></i> Detail data transformasi target tidak ditemukan. Silakan jalankan generate ulang model.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tab 4: Normalisasi Data -->
                    <div class="tab-pane fade" id="v-pills-normalisasi" role="tabpanel" aria-labelledby="v-pills-normalisasi-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 4: Normalisasi Data</h5>
                            <span class="badge bg-info-subtle text-info py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">SCALING & NORMALIZATION</span>
                        </div>
                        <p class="text-secondary text-sm">Menyamakan skala nilai seluruh fitur masukan (X) dan target (y) agar fungsi kernel dan batas toleransi kesalahan SVR dapat dikalkulasi secara optimal:</p>
                        
                        <ul class="list-group list-group-flush mb-4 text-sm bg-white p-3 border rounded shadow-sm" style="border-left: 4px solid #0dcaf0 !important;">
                            <li class="list-group-item bg-transparent px-0 py-2.5">
                                <strong>RobustScaler (pada Fitur Masukan X)</strong>: Menskalakan fitur menggunakan rentang Median dan Interquartile Range (IQR). Sangat direkomendasikan karena kebal terhadap outlier (nilai ekstrem) pendapatan parkir pada hari libur besar.
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2.5">
                                <strong>MinMaxScaler (pada Target y)</strong>: Menskalakan target logaritmik pendapatan ke rentang <code>[0, 1]</code> untuk menjaga kestabilan nilai gradien bobot penalti SVR.
                            </li>
                        </ul>

                        @if($pipelineData && isset($pipelineData['normalized_snapshot']))
                            <div class="card border border-light shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Sampel Data Normalisasi X & Target y (Robust & MinMaxScaler scaled)</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 table-hover table-preview-custom">
                                        <thead class="table-light">
                                            <tr class="text-secondary text-xs">
                                                <th>Tanggal</th>
                                                <th>Rayon</th>
                                                <th class="text-end">Pendapatan Riil</th>
                                                <th class="text-end">y_log</th>
                                                <th class="text-end text-success">y_scaled [0, 1]</th>
                                                <th class="text-end text-primary">Scaled Lag 1</th>
                                                <th class="text-end text-primary">Scaled Rolling 7</th>
                                                <th class="text-end text-primary">Scaled Trend</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @foreach($pipelineData['normalized_snapshot'] as $snap)
                                                <tr>
                                                    <td><code>{{ $snap['tanggal'] }}</code></td>
                                                    <td><span class="badge bg-light text-dark border">Rayon {{ $rayonRomanMap[$snap['rayon']] ?? $snap['rayon'] }}</span></td>
                                                    <td class="text-end">Rp {{ number_format($snap['y_original'], 0, ',', '.') }}</td>
                                                    <td class="text-end text-secondary">{{ number_format($snap['y_log'], 4, ',', '.') }}</td>
                                                    <td class="text-end fw-bold text-success">{{ number_format($snap['y_scaled'], 6, ',', '.') }}</td>
                                                    <td class="text-end text-primary">{{ number_format($snap['x_scaled_lag1'], 6, ',', '.') }}</td>
                                                    <td class="text-end text-primary">{{ number_format($snap['x_scaled_rolling7'], 6, ',', '.') }}</td>
                                                    <td class="text-end text-primary">{{ number_format($snap['x_scaled_trend'], 6, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0 text-xs border-0">
                                <i class="bi bi-info-circle me-1"></i> Detail data normalisasi tidak ditemukan. Silakan jalankan generate ulang model.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tab 5: Pembagian Data -->
                    <div class="tab-pane fade" id="v-pills-split" role="tabpanel" aria-labelledby="v-pills-split-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 5: Pembagian Data (Split Data 80:20)</h5>
                            <span class="badge bg-danger-subtle text-danger py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">DATA SPLITTING (80:20)</span>
                        </div>
                        <p class="text-secondary text-sm">Dataset dibagi menjadi data training (80%) untuk melatih model dan data testing (20%) untuk menguji performa prediksi peramalan.</p>
                        
                        <div class="p-3 bg-white border rounded-3 mb-4 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                            <h6 class="fw-bold text-dark mb-2 text-sm"><i class="bi bi-clock-fill me-1 text-primary-custom"></i>Aturan Time-Series Split (Kronologis)</h6>
                            <p class="text-secondary text-xs mb-0">Pembagian data <strong>WAJIB dilakukan secara urut waktu (kronologis)</strong>, bukan secara acak (random train-test split). Hal ini krusial untuk mencegah terjadinya kebocoran data (data leakage) dari masa depan ke masa lalu, sehingga menjamin validitas hasil pengujian peramalan.</p>
                        </div>

                        @if($pipelineData && isset($pipelineData['split_snapshot']))
                            <h6 class="fw-bold text-secondary text-xs mb-2">Visualisasi Pembagian Dataset (Time Series Split):</h6>
                            <div class="progress mb-4 border border-light" style="height: 35px; border-radius: 8px;">
                                <div class="progress-bar bg-primary fw-bold text-xs d-flex flex-column justify-content-center align-items-center shadow-none" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                                    <span>Data Training (80%)</span>
                                    <span class="text-white-50" style="font-size: 9px;">{{ number_format($pipelineData['split_snapshot']['train_count']) }} baris ({{ Carbon\Carbon::parse($pipelineData['split_snapshot']['train_start_date'])->translatedFormat('d M Y') }} - {{ Carbon\Carbon::parse($pipelineData['split_snapshot']['train_end_date'])->translatedFormat('d M Y') }})</span>
                                </div>
                                <div class="progress-bar bg-warning text-dark fw-bold text-xs d-flex flex-column justify-content-center align-items-center shadow-none" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                    <span>Data Testing (20%)</span>
                                    <span class="text-muted" style="font-size: 9px;">{{ number_format($pipelineData['split_snapshot']['test_count']) }} baris ({{ Carbon\Carbon::parse($pipelineData['split_snapshot']['test_start_date'])->translatedFormat('d M Y') }} - {{ Carbon\Carbon::parse($pipelineData['split_snapshot']['test_end_date'])->translatedFormat('d M Y') }})</span>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 border border-light shadow-sm">
                                        <div class="card-header bg-primary text-white py-2">
                                            <span class="fw-semibold text-xs"><i class="bi bi-clock-history me-1"></i>Batas Akhir Data Training</span>
                                        </div>
                                        <div class="card-body p-3">
                                            <table class="table table-sm table-borderless mb-0 text-xs text-secondary">
                                                <tr>
                                                    <td>Tanggal Akhir</td>
                                                    <td>: <strong>{{ Carbon\Carbon::parse($pipelineData['split_snapshot']['train_end_date'])->translatedFormat('d F Y') }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Ukuran Latih</td>
                                                    <td>: {{ number_format($pipelineData['split_snapshot']['train_count']) }} baris data</td>
                                                </tr>
                                                <tr>
                                                    <td>Persentase</td>
                                                    <td>: 80% dari total dataset</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border border-light shadow-sm">
                                        <div class="card-header bg-warning text-dark py-2">
                                            <span class="fw-semibold text-xs"><i class="bi bi-clock me-1"></i>Batas Awal Data Testing</span>
                                        </div>
                                        <div class="card-body p-3">
                                            <table class="table table-sm table-borderless mb-0 text-xs text-secondary">
                                                <tr>
                                                    <td>Tanggal Awal</td>
                                                    <td>: <strong>{{ Carbon\Carbon::parse($pipelineData['split_snapshot']['test_start_date'])->translatedFormat('d F Y') }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Ukuran Uji</td>
                                                    <td>: {{ number_format($pipelineData['split_snapshot']['test_count']) }} baris data</td>
                                                </tr>
                                                <tr>
                                                    <td>Persentase</td>
                                                    <td>: 20% dari total dataset</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0 text-xs border-0">
                                <i class="bi bi-info-circle me-1"></i> Detail pembagian data tidak ditemukan. Silakan jalankan generate ulang model.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tab 6: Pelatihan Model SVR -->
                    <div class="tab-pane fade" id="v-pills-training" role="tabpanel" aria-labelledby="v-pills-training-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 6: Pelatihan Model SVR</h5>
                            <span class="badge bg-dark text-white py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">SVR MODEL TRAINING</span>
                        </div>
                        <p class="text-secondary text-sm">Model di-fitting menggunakan algoritma Support Vector Regression (SVR) standar pada data training yang telah dinormalisasi:</p>
                        
                        <ul class="list-group list-group-flush text-sm mb-0 bg-white p-3 border rounded shadow-sm">
                            <li class="list-group-item bg-transparent px-0 py-2"><strong>Tipe Kernel</strong>: Radial Basis Function (RBF) untuk pola non-linear.</li>
                            <li class="list-group-item bg-transparent px-0 py-2"><strong>Regularisasi (C)</strong>: <code>{{ $params->c_value ?? '1.0' }}</code></li>
                            <li class="list-group-item bg-transparent px-0 py-2"><strong>Epsilon Toleransi (&epsilon;)</strong>: <code>{{ $params->epsilon_value ?? '0.1' }}</code></li>
                            <li class="list-group-item bg-transparent px-0 py-2"><strong>Gamma (&gamma;)</strong>: <code>{{ $params->gamma_value ?? 'scale' }}</code></li>
                        </ul>
                    </div>

                    <!-- Tab 7: Prediksi Pendapatan -->
                    <div class="tab-pane fade" id="v-pills-prediction" role="tabpanel" aria-labelledby="v-pills-prediction-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Langkah 7: Prediksi Pendapatan</h5>
                            <span class="badge bg-dark text-white py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">PREDICTION & DATABASE INSERT</span>
                        </div>
                        <p class="text-secondary text-sm">Setelah model SVR selesai dilatih pada data training, langkah terakhir adalah memprediksi nilai pendapatan pada data testing (20% data terbaru) dan menyimpannya kembali ke database:</p>
                        
                        <ul class="list-group list-group-flush text-sm mb-0 bg-white p-3 border rounded shadow-sm">
                            <li class="list-group-item bg-transparent px-0 py-2.5">
                                <strong>Inverse Scaling & Transformation</strong>: Hasil keluaran model SVR yang masih dalam skala normalisasi `[0, 1]` dan logaritmik ditransformasikan kembali ke skala rupiah asli menggunakan fungsi inverse scaling (MinMaxScaler inverse) diikuti oleh fungsi eksponensial <code>expm1(y) = e^y - 1</code>.
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2.5">
                                <strong>Evaluasi Kinerja Model</strong>: Mengkalkulasi metrik evaluasi standar seperti MAE, RMSE, MAPE, dan R² score pada data testing untuk mengukur keakuratan peramalan model.
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2.5">
                                <strong>Penyimpanan Database</strong>: Menyimpan seluruh riwayat model running parameter, metrik pengujian, dan hasil peramalan per tanggal dan per rayon ke dalam database agar dapat ditampilkan secara visual dan diexport.
                            </li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Navigation Footer for Step 3 -->
    <div class="d-flex justify-content-between mb-4">
        <button class="btn btn-outline-secondary px-4 py-2.5 rounded-3 fw-semibold" onclick="goToStep(2)">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Konfigurasi SVR
        </button>
        <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-semibold shadow-sm" onclick="goToStep(4)">
            Lihat Hasil Evaluasi <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
