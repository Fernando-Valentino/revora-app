<div class="modal fade" id="accuracyCriteriaModal" tabindex="-1" aria-labelledby="accuracyCriteriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="accuracyCriteriaModalLabel" style="font-size: 15px;">
                    <i class="bi bi-info-circle-fill text-warning"></i> Klasifikasi Tingkat Akurasi Model Prediksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc; max-height: calc(100vh - 180px); overflow-y: auto;">
                <p class="text-secondary small mb-3">
                    Tabel referensi berikut digunakan untuk mengukur seberapa baik kinerja model peramalan (SVR, SVR-Grid Search, dan SVR-GWO) berdasarkan metrik evaluasi yang digunakan.
                </p>
                
                <div class="table-responsive rounded-3 shadow-sm border bg-white">
                    <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr class="text-secondary fw-semibold">
                                <th class="ps-3" style="width: 100px;">Metrik</th>
                                <th style="width: 150px;">Nilai / Range</th>
                                <th class="pe-3">Kriteria Akurasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- MAPE -->
                            <tr>
                                <td rowspan="4" class="fw-bold ps-3 text-dark bg-light-subtle" style="border-right: 1px solid #dee2e6; background-color: #f8fafc;">MAPE</td>
                                <td class="font-monospace">&lt; 10%</td>
                                <td class="pe-3"><span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Sangat Akurat (Excellent)</span></td>
                            </tr>
                            <tr>
                                <td class="font-monospace">10% - 20%</td>
                                <td class="pe-3"><span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 rounded-pill"><i class="bi bi-check-circle me-1"></i> Baik (Good)</span></td>
                            </tr>
                            <tr>
                                <td class="font-monospace">20% - 50%</td>
                                <td class="pe-3"><span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1.5 rounded-pill"><i class="bi bi-exclamation-circle me-1"></i> Cukup (Reasonable)</span></td>
                            </tr>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <td class="font-monospace">&gt; 50%</td>
                                <td class="pe-3"><span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 rounded-pill"><i class="bi bi-x-circle me-1"></i> Buruk (Inaccurate)</span></td>
                            </tr>

                            <!-- R2 Score -->
                            <tr>
                                <td rowspan="3" class="fw-bold ps-3 text-dark bg-light-subtle" style="border-right: 1px solid #dee2e6; background-color: #f8fafc;">R² Score</td>
                                <td class="font-monospace">0.67 - 1.00</td>
                                <td class="pe-3"><span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-shield-check me-1"></i> Model Kuat (Strong)</span></td>
                            </tr>
                            <tr>
                                <td class="font-monospace">0.33 - 0.67</td>
                                <td class="pe-3"><span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 rounded-pill"><i class="bi bi-shield me-1"></i> Model Moderat</span></td>
                            </tr>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <td class="font-monospace">&lt; 0.33</td>
                                <td class="pe-3"><span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 rounded-pill"><i class="bi bi-shield-slash me-1"></i> Model Lemah</span></td>
                            </tr>

                            <!-- RMSE -->
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td class="fw-bold ps-3 text-dark bg-light-subtle" style="border-right: 1px solid #dee2e6; background-color: #f8fafc;">RMSE</td>
                                <td class="font-monospace">
                                    &lt; 10% dari Mean
                                    <div class="text-muted" style="font-size: 10.5px; font-family: inherit; font-weight: normal;">Selisih prediksi &lt; 10% dari rata-rata pendapatan harian</div>
                                </td>
                                <td class="pe-3"><span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-star-fill me-1"></i> Sangat Baik</span></td>
                            </tr>

                            <!-- MAE -->
                            <tr>
                                <td class="fw-bold ps-3 text-dark bg-light-subtle" style="border-right: 1px solid #dee2e6; background-color: #f8fafc;">MAE</td>
                                <td>
                                    Mendekati 0
                                    <div class="text-muted" style="font-size: 10.5px; font-weight: normal;">Semakin kecil, semakin sedikit selisih Rp prediksi per hari</div>
                                </td>
                                <td class="pe-3"><span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-award-fill me-1"></i> Presisi Tinggi</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer bg-light py-2.5 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
