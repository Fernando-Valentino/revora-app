@props([
    'lastRun',
    'pipelineData' => null,
    'params' => null,
])

<!-- GWO Step 3: Progress & Preprocessing detail -->
<div id="gwo-step-content-3" class="step-opt-content d-none">
    <div class="card mb-4 bg-white">
        <div class="card-body p-4 text-center">
            <h5 class="card-title text-start mb-4"><i class="bi bi-activity me-2 text-primary-custom"></i>Proses Tuning Parameter SVR + GWO</h5>

            <div class="py-4">
                <!-- Spinner Container -->
                <div id="gwo-spinner-container">
                    <div class="spinner-border text-primary mb-3" style="width: 50px; height: 50px; border-width: 4px;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <!-- Success Check Container -->
                <div id="gwo-success-container" class="d-none">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success p-4 rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                </div>
                <h6 class="fw-bold text-dark mb-2" id="gwo-process-title">Sedang Menyiapkan GWO...</h6>
                <p class="text-secondary small mx-auto mb-4" style="max-width: 420px;" id="gwo-process-desc">Algoritma Grey Wolf Optimizer sedang diinisialisasi.</p>
            </div>

            <!-- Timer Box -->
            <div class="py-3 bg-light rounded-3 mb-4 mx-auto" id="gwo-timer-box" style="max-width: 400px; border: 1px dashed #cbd5e1;">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <span class="d-block text-muted small fw-semibold">Waktu Berjalan</span>
                        <span class="fs-4 fw-bold text-dark" id="gwo-elapsed-timer">0s</span>
                    </div>
                    <div style="border-left: 1px solid #e2e8f0; height: 40px; margin-top: 5px;"></div>
                    <div>
                        <span class="d-block text-muted small fw-semibold">Perkiraan Waktu</span>
                        <span class="fs-4 fw-bold text-primary" id="gwo-estimated-timer">~45s</span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Iterasi GWO -->
            <div class="mx-auto mb-4" id="gwo-progress-bar-container" style="max-width: 500px;">
                <div class="d-flex justify-content-between text-sm mb-1">
                    <span class="fw-bold text-dark" id="gwo-iter-label">Iterasi GWO: 0 / 30</span>
                    <span class="text-secondary" id="gwo-iter-pct">0%</span>
                </div>
                <div class="progress" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="gwo-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="text-start border-top pt-4 mx-auto" style="max-width: 500px;">
                <h6 class="fw-bold text-dark mb-3">Progress Pipeline Preprocessing:</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="progress-step" id="gwo-pipe-1">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">1. Pembersihan Data (Data Cleaning)</span>
                    </div>
                    <div class="progress-step" id="gwo-pipe-2">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">2. Rekayasa Fitur (Feature Engineering)</span>
                    </div>
                    <div class="progress-step" id="gwo-pipe-3">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">3. Transformasi Data</span>
                    </div>
                    <div class="progress-step" id="gwo-pipe-4">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">4. Normalisasi Data</span>
                    </div>
                    <div class="progress-step" id="gwo-pipe-5">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">5. Pembagian Data (Split Data 80:20)</span>
                    </div>
                    <div class="progress-step" id="gwo-pipe-6">
                        <span class="step-icon me-2 text-muted"><i class="bi bi-circle"></i></span>
                        <span class="step-label">6. Pencarian Parameter Optimal (Grey Wolf Optimizer)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Preprocessing tabs inside GWO Step 3 -->
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
                    <div class="nav flex-column nav-pills me-2 col-12 col-md-4 col-lg-3" id="gwo-v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="gwo-gwo-v-pills-cleaning-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-cleaning" type="button" role="tab" aria-controls="gwo-v-pills-cleaning" aria-selected="true"><span class="tab-step-number">1</span> 1. Pembersihan Data</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-fe-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-fe" type="button" role="tab" aria-controls="gwo-v-pills-fe" aria-selected="false"><span class="tab-step-number">2</span> 2. Rekayasa Fitur</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-target-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-target" type="button" role="tab" aria-controls="gwo-v-pills-target" aria-selected="false"><span class="tab-step-number">3</span> 3. Transformasi Data</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-normalisasi-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-normalisasi" type="button" role="tab" aria-controls="gwo-v-pills-normalisasi" aria-selected="false"><span class="tab-step-number">4</span> 4. Normalisasi Data</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-split-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-split" type="button" role="tab" aria-controls="gwo-v-pills-split" aria-selected="false"><span class="tab-step-number">5</span> 5. Pembagian Data (Split)</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-training-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-training" type="button" role="tab" aria-controls="gwo-v-pills-training" aria-selected="false"><span class="tab-step-number">6</span> 6. Pelatihan Model SVR</button>
                        <button class="nav-link" id="gwo-gwo-v-pills-prediction-tab" data-bs-toggle="pill" data-bs-target="#gwo-v-pills-prediction" type="button" role="tab" aria-controls="gwo-v-pills-prediction" aria-selected="false"><span class="tab-step-number">7</span> 7. Prediksi Pendapatan</button>
                    </div>

                    <!-- Tabs Content -->
                    <div class="tab-content flex-grow-1 col-12 col-md-8 col-lg-9 border p-4 rounded-3" id="gwo-gwo-v-pills-tabContent" style="background-color: #ffffff;">
                        @php
                            $rayonRomanMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
                        @endphp
                        <!-- Tab 1: Pembersihan Data -->
                        <div class="tab-pane fade show active" id="gwo-v-pills-cleaning" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-cleaning-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Langkah 1: Pembersihan Data (Data Cleaning)</h5>
                                <span class="badge bg-success-subtle text-success py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">FILTER & IMPUTATION</span>
                            </div>
                            <p class="text-secondary text-sm">Sebelum pembersihan, dataset asli (data mentah) dibaca dari database. Jika terdapat pencatatan dengan pendapatan bernilai Rp 0, sistem mendeteksi dan membersihkan distorsi tersebut dengan aturan bisnis berikut:</p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 border border-danger-subtle h-100 bg-white shadow-sm" style="border-left: 4px solid #dc2626 !important;">
                                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-trash3-fill me-1"></i>1. Penghapusan Baris Pendapatan 0 pada Hari Kerja Biasa</h6>
                                        <p class="text-secondary text-xs mb-0">Baris data bernilai <strong>Rp 0 pada hari kerja biasa</strong> (bukan libur nasional) akan <strong>dihapus (dropped)</strong> karena diindikasikan sebagai kesalahan pencatatan atau juru parkir absen menyetor.</p>
                                    </div>
                                </div>
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
                                        <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="gwo-cleaning-snapshot-tabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active fw-bold text-xs py-1.5 px-3 border-0 bg-transparent text-secondary" id="gwo-raw-snapshot-tab" data-bs-toggle="tab" data-bs-target="#gwo-raw-snapshot-pane" type="button" role="tab" aria-controls="gwo-raw-snapshot-pane" aria-selected="true" style="transition: all 0.2s;">
                                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>1. Sebelum Pembersihan (Raw Data)
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link fw-bold text-xs py-1.5 px-3 border-0 bg-transparent text-secondary" id="gwo-cleaned-snapshot-tab" data-bs-toggle="tab" data-bs-target="#gwo-cleaned-snapshot-pane" type="button" role="tab" aria-controls="gwo-cleaned-snapshot-pane" aria-selected="false" style="transition: all 0.2s;">
                                                    <i class="bi bi-shield-check me-1"></i>2. Setelah Pembersihan (Cleaned Data)
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content" id="gwo-grid-cleaning-snapshot-tabsContent">
                                        <!-- Tab 1.1: Sebelum Pembersihan -->
                                        <div class="tab-pane fade show active" id="gwo-raw-snapshot-pane" role="tabpanel" aria-labelledby="gwo-raw-snapshot-tab">
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
                                                                <td class="text-center"><span class="badge {{ $isWeekend ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">{{ $isWeekend ? '1' : '0' }}</span></td>
                                                                <td class="text-center"><span class="badge {{ $isLibur ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">{{ $isLibur ? '1' : '0' }}</span></td>
                                                                <td class="text-end fw-semibold {{ $isZero ? 'text-danger' : 'text-dark' }}">Rp {{ number_format($snap['Total_Pendapatan'], 0, ',', '.') }}</td>
                                                                <td class="text-end text-secondary">{{ $snap['Jumlah Jukir'] ?? ($snap['Jumlah_Jukir'] ?? 80) }} Jukir</td>
                                                                <td class="text-center">{!! $statusBadge !!}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- Tab 1.2: Setelah Pembersihan -->
                                        <div class="tab-pane fade" id="gwo-cleaned-snapshot-pane" role="tabpanel" aria-labelledby="gwo-cleaned-snapshot-tab">
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
                                                                <td class="text-center"><span class="badge {{ $isWeekend ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">{{ $isWeekend ? '1' : '0' }}</span></td>
                                                                <td class="text-center"><span class="badge {{ $isLibur ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border-0 py-1 px-2">{{ $isLibur ? '1' : '0' }}</span></td>
                                                                <td class="text-end fw-semibold text-success">Rp {{ number_format($snap['Total_Pendapatan'], 0, ',', '.') }}</td>
                                                                <td class="text-end text-secondary">{{ $snap['Jumlah Jukir'] ?? ($snap['Jumlah_Jukir'] ?? 80) }} Jukir</td>
                                                                <td class="text-center"><span class="badge bg-success-subtle text-success border-0 py-1 px-2.5"><i class="bi bi-check-circle-fill me-1"></i>Bersih</span></td>
                             </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-0 mt-3 text-xs border-0"><i class="bi bi-info-circle me-1"></i> Detail data snapshot pembersihan tidak ditemukan. Silakan jalankan generate ulang model.</div>
                            @endif
                        </div>

                        <!-- Tab 2: Rekayasa Fitur -->
                        <div class="tab-pane fade" id="gwo-v-pills-fe" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-fe-tab">
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
                                    <div class="card-header bg-light py-2"><span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Hasil Ekstraksi & Rekayasa Fitur (Sample Data)</span></div>
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
                                <div class="alert alert-warning py-2 mb-0 text-xs border-0"><i class="bi bi-info-circle me-1"></i> Detail data rekayasa fitur tidak ditemukan. Silakan jalankan generate ulang model.</div>
                            @endif
                        </div>

                        <!-- Tab 3: Transformasi Data -->
                        <div class="tab-pane fade" id="gwo-v-pills-target" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-target-tab">
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
                                    <div class="card-header bg-light py-2"><span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Hasil Transformasi Target y → ln(y + 1)</span></div>
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
                                                        <td class="text-center"><span class="badge bg-primary-subtle text-primary border-0 py-1 px-2" style="font-size: 10px;">ln({{ number_format($snap['Total_Pendapatan']) }} + 1)</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-0 text-xs border-0"><i class="bi bi-info-circle me-1"></i> Detail data transformasi target tidak ditemukan. Silakan jalankan generate ulang model.</div>
                            @endif
                        </div>

                        <!-- Tab 4: Normalisasi Data -->
                        <div class="tab-pane fade" id="gwo-v-pills-normalisasi" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-normalisasi-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Langkah 4: Normalisasi Data</h5>
                                <span class="badge bg-info-subtle text-info py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">SCALING & NORMALIZATION</span>
                            </div>
                            <p class="text-secondary text-sm">Menyamakan skala nilai seluruh fitur masukan (X) dan target (y) agar fungsi kernel dan batas toleransi kesalahan SVR dapat dikalkulasi secara optimal:</p>

                            <ul class="list-group list-group-flush mb-4 text-sm bg-white p-3 border rounded shadow-sm" style="border-left: 4px solid #0dcaf0 !important;">
                                <li class="list-group-item bg-transparent px-0 py-2.5"><strong>RobustScaler (pada Fitur Masukan X)</strong>: Menskalakan fitur menggunakan rentang Median dan Interquartile Range (IQR). Sangat direkomendasikan karena kebal terhadap outlier (nilai ekstrem) pendapatan parkir pada hari libur besar.</li>
                                <li class="list-group-item bg-transparent px-0 py-2.5"><strong>MinMaxScaler (pada Target y)</strong>: MinMaxScaler target logaritmik pendapatan ke rentang <code>[0, 1]</code>.</li>
                            </ul>

                            @if($pipelineData && isset($pipelineData['normalized_snapshot']))
                                <div class="card border border-light shadow-sm">
                                    <div class="card-header bg-light py-2"><span class="fw-semibold text-secondary text-xs"><i class="bi bi-table me-1"></i>Sampel Data Normalisasi X & Target y (Robust & MinMaxScaler scaled)</span></div>
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
                                <div class="alert alert-warning py-2 mb-0 text-xs border-0"><i class="bi bi-info-circle me-1"></i> Detail data normalisasi tidak ditemukan. Silakan jalankan generate ulang model.</div>
                            @endif
                        </div>

                        <!-- Tab 5: Pembagian Data -->
                        <div class="tab-pane fade" id="gwo-v-pills-split" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-split-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Langkah 5: Pembagian Data (Split Data 80:20)</h5>
                                <span class="badge bg-danger-subtle text-danger py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">DATA SPLITTING (80:20)</span>
                            </div>
                            <p class="text-secondary text-sm">Dataset dibagi menjadi data training (80%) untuk melatih model dan data testing (20%) untuk menguji performa prediksi peramalan.</p>

                            <div class="p-3 bg-white border rounded-3 mb-4 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                                <h6 class="fw-bold text-dark mb-2 text-sm"><i class="bi bi-clock-fill me-1 text-primary-custom"></i>Aturan Time-Series Split (Kronologis)</h6>
                                <p class="text-secondary text-xs mb-0">Pembagian data <strong>WAJIB dilakukan secara urut waktu (kronologis)</strong>, bukan secara acak (random train-test split).</p>
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
                                            <div class="card-header bg-primary text-white py-2"><span class="fw-semibold text-xs"><i class="bi bi-clock-history me-1"></i>Batas Akhir Data Training</span></div>
                                            <div class="card-body p-3">
                                                <table class="table table-sm table-borderless mb-0 text-xs text-secondary">
                                                    <tr><td>Tanggal Akhir</td><td>: <strong>{{ Carbon\Carbon::parse($pipelineData['split_snapshot']['train_end_date'])->translatedFormat('d F Y') }}</strong></td></tr>
                                                    <tr><td>Ukuran Latih</td><td>: {{ number_format($pipelineData['split_snapshot']['train_count']) }} baris data</td></tr>
                                                    <tr><td>Persentase</td><td>: 80% dari total dataset</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100 border border-light shadow-sm">
                                            <div class="card-header bg-warning text-dark py-2"><span class="fw-semibold text-xs"><i class="bi bi-clock me-1"></i>Batas Awal Data Testing</span></div>
                                            <div class="card-body p-3">
                                                <table class="table table-sm table-borderless mb-0 text-xs text-secondary">
                                                    <tr><td>Tanggal Awal</td><td>: <strong>{{ Carbon\Carbon::parse($pipelineData['split_snapshot']['test_start_date'])->translatedFormat('d F Y') }}</strong></td></tr>
                                                    <tr><td>Ukuran Uji</td><td>: {{ number_format($pipelineData['split_snapshot']['test_count']) }} baris data</td></tr>
                                                    <tr><td>Persentase</td><td>: 20% dari total dataset</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-0 text-xs border-0"><i class="bi bi-info-circle me-1"></i> Detail pembagian data tidak ditemukan. Silakan jalankan generate ulang model.</div>
                            @endif
                        </div>

                        <!-- Tab 6: Pelatihan Model SVR -->
                        <div class="tab-pane fade" id="gwo-v-pills-training" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-training-tab">
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
                        <div class="tab-pane fade" id="gwo-v-pills-prediction" role="tabpanel" aria-labelledby="gwo-gwo-v-pills-prediction-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Langkah 7: Prediksi Pendapatan</h5>
                                <span class="badge bg-dark text-white py-1 px-2 text-uppercase fw-bold" style="font-size: 10px;">PREDICTION & DATABASE INSERT</span>
                            </div>
                            <p class="text-secondary text-sm">Setelah model SVR selesai dilatih pada data training, langkah terakhir adalah memprediksi nilai pendapatan pada data testing (20% data terbaru) dan menyimpannya kembali ke database:</p>

                            <ul class="list-group list-group-flush text-sm mb-0 bg-white p-3 border rounded shadow-sm">
                                <li class="list-group-item bg-transparent px-0 py-2.5"><strong>Inverse Scaling & Transformation</strong>: Hasil keluaran model SVR yang masih dalam skala normalisasi `[0, 1]` dan logaritmik ditransformasikan kembali ke skala rupiah asli menggunakan fungsi inverse scaling (MinMaxScaler inverse) diikuti oleh fungsi eksponensial <code>expm1(y) = e^y - 1</code>.</li>
                                <li class="list-group-item bg-transparent px-0 py-2.5"><strong>Evaluasi Kinerja Model</strong>: Mengkalkulasi metrik evaluasi standar seperti MAE, RMSE, MAPE, dan R² score pada data testing untuk mengukur keakuratan peramalan model.</li>
                                <li class="list-group-item bg-transparent px-0 py-2.5"><strong>Penyimpanan Database</strong>: Menyimpan seluruh riwayat model running parameter, metrik pengujian, dan hasil peramalan per tanggal dan per rayon ke dalam database agar dapat ditampilkan secara visual dan diexport.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
