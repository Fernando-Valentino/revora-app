@props([
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
])

<!-- STEP 1 CONTENT -->
<div id="step-content-1" class="step-content-section d-none">
    <!-- Dataset Ringkasan & Validasi Section -->
    <div class="row g-4 mb-4">
        <!-- Ringkasan Dataset -->
        <div class="col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle-fill me-2 text-primary-custom"></i>Ringkasan Dataset</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0 text-sm">
                            <tbody>
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
                                    <td class="fw-semibold text-secondary">Data Libur & Weekend</td>
                                    <td class="text-end">
                                        <span class="badge badge-holiday me-1">{{ $jumlahHariLibur }} Libur</span>
                                        <span class="badge badge-weekend">{{ $jumlahWeekend }} Weekend</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-secondary">Status Dataset</td>
                                    <td class="text-end">
                                        @if($datasetReady)
                                            <span class="badge badge-active"><i class="bi bi-patch-check-fill me-1"></i>Siap Diproses</span>
                                        @else
                                            <span class="badge badge-inactive"><i class="bi bi-exclamation-triangle-fill me-1"></i>Belum Siap</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validasi Kelengkapan Dataset -->
        <div class="col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title"><i class="bi bi-shield-check-fill me-2 text-primary-custom"></i>Validasi Kelengkapan Dataset</h5>
                        <ul class="list-group list-group-flush text-sm">
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2">
                                <span><i class="bi {{ $hasPendapatan ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data pendapatan</span>
                                <span class="badge {{ $hasPendapatan ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasPendapatan ? 'Lengkap' : 'Tidak Ada' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2">
                                <span><i class="bi {{ $hasRayon ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data rayon</span>
                                <span class="badge {{ $hasRayon ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasRayon ? 'Lengkap' : 'Tidak Ada' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2">
                                <span><i class="bi {{ $hasJuruParkir ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data juru parkir</span>
                                <span class="badge {{ $hasJuruParkir ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasJuruParkir ? 'Lengkap' : 'Tidak Ada' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-light py-2">
                                <span><i class="bi {{ $hasHariLibur ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i> Data hari libur dan weekend</span>
                                <span class="badge {{ $hasHariLibur ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border-0">{{ $hasHariLibur ? 'Lengkap' : 'Tidak Ada' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-3 p-2 rounded {{ $datasetReady ? 'alert alert-success' : 'alert alert-danger' }} mb-0 py-2 px-3 small border-0 d-flex align-items-center">
                        <i class="bi {{ $datasetReady ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' }} me-2 fs-5"></i>
                        <span class="text-dark">
                            @if($datasetReady)
                                <strong>Dataset siap digunakan</strong> untuk SVR standar.
                            @else
                                <strong>Dataset belum lengkap.</strong> Silakan lengkapi data pada menu Master Data Retribusi terlebih dahulu.
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Footer for Step 1 -->
    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-dark px-4 py-2.5 rounded-3 fw-semibold shadow-sm" onclick="goToStep(2)" {{ !$datasetReady ? 'disabled' : '' }}>
            Lanjut ke Konfigurasi SVR <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
