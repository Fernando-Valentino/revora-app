@props(['route' => '#'])

<div class="card shadow-sm border border-light bg-white rounded-3 overflow-hidden mb-4">
    <div class="card-body p-5 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning p-4 rounded-circle mb-4"
            style="width: 80px; height: 80px;">
            <i class="bi bi-lock-fill fs-1 text-warning"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">Optimasi Parameter Terkunci</h4>
        <p class="text-secondary mx-auto mb-4" style="max-width: 540px; line-height: 1.6;">
            Fitur optimasi parameter (Grid Search &amp; Grey Wolf Optimizer) membutuhkan model SVR standar yang
            telah berhasil dilatih terlebih dahulu. Silakan masuk ke menu Prediksi untuk melakukan proses training
            model SVR standar.
        </p>
        <a href="{{ $route }}" class="btn btn-dark px-4 py-2.5 rounded-3 fw-bold text-sm">
            <i class="bi bi-cpu me-2"></i>Jalankan SVR Standar Terlebih Dahulu
        </a>
    </div>
</div>
