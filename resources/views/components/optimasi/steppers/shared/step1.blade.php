@props([
    'method', // 'grid' or 'gwo'
    'totalPendapatan',
    'periodeAwalFormatted',
    'periodeAkhirFormatted',
    'jumlahRayon',
    'jumlahHariLibur',
    'jumlahWeekend',
    'datasetReady',
    'hasPendapatan',
    'hasRayon',
    'hasJuruParkir',
    'hasHariLibur',
    'lastRun',
    'comparisons'
])

@php
    $nextStepJs = $method === 'gwo' ? 'goToGwoStep(2)' : 'goToGridStep(2)';
    $pipePrefix = $method === 'gwo' ? 'gwo-pipe-svr-' : 'grid-pipe-svr-';
@endphp

<div id="{{ $method }}-step-content-1" class="step-opt-content">
    <div class="row g-4 mb-4">
        <!-- Ringkasan Dataset -->
        <div class="col-md-6">
            <x-ui.card title="Ringkasan Dataset">
                <x-slot name="headerActions">
                    <span class="text-primary-custom"><i class="bi bi-info-circle-fill"></i></span>
                </x-slot>
                
                <x-ui.table :headers="[]" class="table-borderless table-sm text-sm">
                    <tr>
                        <td class="fw-semibold text-secondary" style="width: 50%;">Total Data Pendapatan</td>
                        <td class="text-end fw-bold text-dark">{{ number_format($totalPendapatan, 0, ',', '.') }} Baris</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">Periode Data Awal</td>
                        <td class="text-end fw-bold text-dark">{{ $periodeAwalFormatted }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">Periode Data Akhir</td>
                        <td class="text-end fw-bold text-dark">{{ $periodeAkhirFormatted }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">Jumlah Rayon</td>
                        <td class="text-end fw-bold text-dark">{{ $jumlahRayon }} Rayon</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">Data Libur &amp; Weekend</td>
                        <td class="text-end">
                            <x-ui.badge type="primary" class="me-1">{{ $jumlahHariLibur }} Libur</x-ui.badge>
                            <x-ui.badge type="warning">{{ $jumlahWeekend }} Weekend</x-ui.badge>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-secondary">Status Dataset</td>
                        <td class="text-end">
                            @if($datasetReady)
                                <x-ui.badge type="success"><i class="bi bi-patch-check-fill me-1"></i>Siap Diproses</x-ui.badge>
                            @else
                                <x-ui.badge type="danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Siap</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                </x-ui.table>
            </x-ui.card>
        </div>

        <!-- Validasi Kelengkapan Dataset -->
        <div class="col-md-6">
            <x-ui.card title="Validasi Kelengkapan Dataset">
                <x-slot name="headerActions">
                    <span class="text-primary-custom"><i class="bi bi-shield-check-fill"></i></span>
                </x-slot>
                
                <ul class="list-group list-group-flush text-sm mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                        <span><i class="bi {{ $hasPendapatan ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data pendapatan</span>
                        <x-ui.badge :type="$hasPendapatan ? 'success' : 'danger'">{{ $hasPendapatan ? 'Lengkap' : 'Tidak Ada' }}</x-ui.badge>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                        <span><i class="bi {{ $hasRayon ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data rayon</span>
                        <x-ui.badge :type="$hasRayon ? 'success' : 'danger'">{{ $hasRayon ? 'Lengkap' : 'Tidak Ada' }}</x-ui.badge>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                        <span><i class="bi {{ $hasJuruParkir ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data juru parkir</span>
                        <x-ui.badge :type="$hasJuruParkir ? 'success' : 'danger'">{{ $hasJuruParkir ? 'Lengkap' : 'Tidak Ada' }}</x-ui.badge>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2 px-0">
                        <span><i class="bi {{ $hasHariLibur ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data hari libur dan weekend</span>
                        <x-ui.badge :type="$hasHariLibur ? 'success' : 'danger'">{{ $hasHariLibur ? 'Lengkap' : 'Tidak Ada' }}</x-ui.badge>
                    </li>
                </ul>

                <x-ui.alert :type="$datasetReady ? 'success' : 'danger'" icon="{{ $datasetReady ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}" class="mb-0 py-2 px-3 small border-0 shadow-xs">
                    <span class="text-dark">
                        @if($datasetReady)
                            <strong>Dataset siap digunakan</strong> untuk optimasi parameter.
                        @else
                            <strong>Dataset belum lengkap.</strong> Silakan lengkapi data pada menu Master Data Retribusi terlebih dahulu.
                        @endif
                    </span>
                </x-ui.alert>
            </x-ui.card>
        </div>
    </div>

    <!-- Riwayat Preprocessing & Pelatihan SVR Standar -->
    <x-ui.card>
        <x-slot name="headerActions">
            <x-ui.badge type="success"><i class="bi bi-check-circle-fill me-1"></i>Selesai Dijalankan</x-ui.badge>
        </x-slot>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title text-dark border-0 pb-0 mb-0">
                <i class="bi bi-cpu-fill me-2 text-primary-custom"></i>Riwayat Proses SVR Standar (Sebelum Optimasi)
            </h5>
        </div>

        <p class="text-secondary small mb-4">Berikut adalah parameter baseline, performa pengujian SVR Standar (Default) yang telah dilatih sebelumnya, serta langkah-langkah preprocessing data yang otomatis diselesaikan:</p>

        <div class="row g-4 mb-4">
            <!-- Detail Baseline SVR Standar -->
            <div class="col-md-5">
                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-wide-connected me-1"></i>Baseline SVR Standar</h6>
                    <x-ui.table :headers="[]" class="table-borderless table-sm text-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-secondary" style="width: 40%;">Parameter C</td>
                            <td class="text-secondary text-center" style="width: 5%;">:</td>
                            <td class="text-dark fw-semibold">1.0 (Default)</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary">Epsilon (&epsilon;)</td>
                            <td class="text-secondary text-center">:</td>
                            <td class="text-dark fw-semibold">0.1 (Default)</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary">Gamma (&gamma;)</td>
                            <td class="text-secondary text-center">:</td>
                            <td class="text-dark fw-semibold">scale (Default)</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary">MAPE Test</td>
                            <td class="text-secondary text-center">:</td>
                            <td><x-ui.badge type="success" class="fw-bold">{{ $comparisons[0]['mape'] ?? '-' }}</x-ui.badge></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary">Akurasi Test</td>
                            <td class="text-secondary text-center">:</td>
                            <td><x-ui.badge type="primary" class="fw-bold">{{ $comparisons[0]['akurasi'] ?? '-' }}</x-ui.badge></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary">Waktu Latih</td>
                            <td class="text-secondary text-center">:</td>
                            <td class="text-secondary small">
                                {{ $lastRun ? Carbon\Carbon::parse($lastRun->finished_at)->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                            </td>
                        </tr>
                    </x-ui.table>
                </div>
            </div>

            <!-- 7 Preprocessing checklist -->
            <div class="col-md-7">
                <div class="p-4 bg-light-subtle rounded-3 border border-light h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-patch-check-fill me-1"></i>Daftar Tahapan Preprocessing &amp; Prediksi SVR</h6>
                    <div class="progress-steps-list">
                        @for($i = 1; $i <= 7; $i++)
                            @php
                                $labels = [
                                    1 => '1. Pembersihan Data (Data Cleaning)',
                                    2 => '2. Rekayasa Fitur (Feature Engineering)',
                                    3 => '3. Transformasi Data',
                                    4 => '4. Normalisasi Data',
                                    5 => '5. Pembagian Data (Split Data 80:20)',
                                    6 => '6. Pelatihan Model SVR',
                                    7 => '7. Prediksi Pendapatan'
                                ];
                            @endphp
                            <div class="progress-step success-step" id="{{ $pipePrefix . $i }}">
                                <span class="step-icon me-2"><i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i></span>
                                <span class="step-label">{{ $labels[$i] }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Navigation Footer for Step 1 -->
    <div class="d-flex justify-content-end mb-4 mt-4">
        <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm shadow-sm" onclick="{{ $nextStepJs }}">
            Lanjut ke Konfigurasi {{ $method === 'gwo' ? 'GWO' : 'Grid Search' }} <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
