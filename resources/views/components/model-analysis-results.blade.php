@props([
    'mape',
    'r2',
    'rmse',
    'mae' => null,
    'meanActual',
    'target',
    'recTitle' => null
])

@php
    $rmsePercentage = $meanActual > 0 ? ($rmse / $meanActual) * 100 : 0;

    // 1. Klasifikasi MAPE
    if ($mape < 10) {
        $mapeCategory = "Sangat Akurat";
        $mapeDesc = "Angka prediksi model ini hampir sama persis dengan pendapatan yang sebenarnya terjadi.";
        $mapeColor = "text-success border-success bg-success-subtle";
        $mapeAlertClass = "alert-success text-success-emphasis bg-success-subtle border-success-subtle";
        $mapeIcon = "bi-patch-check-fill text-success";
    } elseif ($mape <= 20) {
        $mapeCategory = "Baik";
        $mapeDesc = "Prediksi model ini sudah cukup mendekati kenyataan dan aman dipakai untuk perencanaan.";
        $mapeColor = "text-primary border-primary bg-primary-subtle";
        $mapeAlertClass = "alert-primary text-primary-emphasis bg-primary-subtle border-primary-subtle";
        $mapeIcon = "bi-check-circle-fill text-primary";
    } elseif ($mape <= 50) {
        $mapeCategory = "Cukup";
        $mapeDesc = "Prediksi model ini masih meleset cukup jauh, sebaiknya ditingkatkan lagi akurasinya.";
        $mapeColor = "text-warning border-warning bg-warning-subtle";
        $mapeAlertClass = "alert-warning text-warning-emphasis bg-warning-subtle border-warning-subtle";
        $mapeIcon = "bi-exclamation-triangle-fill text-warning";
    } else {
        $mapeCategory = "Buruk";
        $mapeDesc = "Selisih antara prediksi dan kenyataan terlalu besar, model ini belum layak dipakai untuk perencanaan.";
        $mapeColor = "text-danger border-danger bg-danger-subtle";
        $mapeAlertClass = "alert-danger text-danger-emphasis bg-danger-subtle border-danger-subtle";
        $mapeIcon = "bi-x-circle-fill text-danger";
    }

    // 2. Klasifikasi R2 Score
    if ($r2 >= 0.67) {
        $r2Category = "Model Kuat";
        $r2Desc = "Model ini sudah pintar membaca pola naik-turunnya pendapatan parkir.";
        $r2Icon = "bi-graph-up text-success";
    } elseif ($r2 >= 0.33) {
        $r2Category = "Model Moderat";
        $r2Desc = "Model ini sudah cukup mengerti polanya, tapi masih ada sebagian pola yang belum tertangkap dengan baik.";
        $r2Icon = "bi-graph-up text-primary";
    } else {
        $r2Category = "Model Lemah";
        $r2Desc = "Model ini masih kesulitan mengenali pola data, perlu disetel ulang parameternya.";
        $r2Icon = "bi-graph-up text-danger";
    }
    
    // 3. Klasifikasi RMSE
    if ($rmsePercentage < 10) {
        $rmseCategory = "Sangat Baik";
        $rmseDesc = "Selisih prediksi tergolong kecil, hanya sekitar " . number_format($rmsePercentage, 2, ',', '.') . "% dari rata-rata pendapatan aktual.";
        $rmseColor = "text-success";
        $rmseIcon = "bi-shield-check-fill text-success";
    } else {
        $rmseCategory = "Perlu Perbaikan";
        $rmseDesc = "Selisih prediksi masih cukup besar, sekitar " . number_format($rmsePercentage, 2, ',', '.') . "% dari rata-rata pendapatan aktual, sebaiknya parameter model disetel ulang.";
        $rmseColor = "text-warning";
        $rmseIcon = "bi-exclamation-octagon-fill text-warning";
    }
    
    // 4. MAE
    if ($mae !== null) {
        $maeCategory = "Presisi Tinggi";
        $maeDesc = "Rata-rata selisih prediksi dengan kenyataan setiap harinya sekitar Rp " . number_format($mae, 0, ',', '.') . ".";
        $maeIcon = "bi-pin-map-fill text-primary";
    }

    // 5. Rekomendasi
    $recommendations = [];
    if ($target === 'svr_default') {
        if ($mape < 10 && $r2 >= 0.67) {
            $recommendations[] = "<strong>Pertahankan pengaturan saat ini</strong> — hasil model sudah sangat bagus, tidak perlu diubah.";
        } else {
            $recommendations[] = "<strong>Coba tingkatkan akurasi model</strong> dengan menjalankan fitur Grid Search atau GWO di menu Optimasi.";
            $recommendations[] = "<strong>Lengkapi data terbaru</strong> supaya model belajar dari informasi yang lebih up-to-date.";
        }
        $recommendations[] = "<strong>Latih ulang model</strong> setiap kali ada data transaksi baru yang masuk.";
    } elseif ($target === 'grid_search') {
        if ($mape < 10 && $r2 >= 0.67) {
            $recommendations[] = "<strong>Pertahankan pengaturan saat ini</strong> — hasil Grid Search sudah sangat bagus.";
        } else {
            $recommendations[] = "<strong>Coba tingkatkan hasilnya</strong> dengan memperluas pilihan parameter pencarian, atau coba metode GWO untuk hasil yang lebih presisi.";
        }
        $recommendations[] = "<strong>Latih ulang model</strong> setiap kali ada data transaksi baru yang masuk.";
    } elseif ($target === 'gwo') {
        if ($mape < 10 && $r2 >= 0.67) {
            $recommendations[] = "<strong>Pertahankan pengaturan saat ini</strong> — GWO berhasil menemukan pengaturan yang sangat pas.";
        } else {
            $recommendations[] = "<strong>Perluas rentang pencarian GWO</strong> atau tambah jumlah iterasinya supaya hasilnya lebih presisi.";
        }
        $recommendations[] = "<strong>Latih ulang model</strong> setiap kali ada data transaksi baru yang masuk.";
    } elseif ($target === 'svr_default_upt') {
        if ($mape < 20 && $r2 >= 0.33) {
            $recommendations[] = "<strong>Model Siap Dipakai:</strong> Hasilnya menunjukkan tingkat kesalahan yang rendah dan model cukup mengerti polanya. Model ini layak dijadikan acuan oleh UPT untuk menyusun target pendapatan harian.";
            $recommendations[] = "Gunakan angka proyeksi ini sebagai target kerja resmi untuk juru parkir di lapangan.";
        } else {
            $recommendations[] = "<strong>Sebaiknya Dilatih Ulang:</strong> Akurasi model saat ini masih kurang ideal. Operator disarankan menjalankan pelatihan ulang (menyetel ulang parameter C, Epsilon, Gamma) menggunakan metode Grey Wolf Optimizer (GWO) agar hasilnya lebih presisi.";
        }
    } elseif ($target === 'svr_default_dishub') {
        if ($mape < 20 && $r2 >= 0.33) {
            $recommendations[] = "<strong>Model Siap Dipakai:</strong> Hasilnya menunjukkan tingkat kesalahan yang rendah dan model cukup mengerti polanya. Model ini layak dijadikan acuan oleh Dishub untuk menyusun target anggaran retribusi.";
            $recommendations[] = "Gunakan angka proyeksi ini sebagai acuan resmi dalam menetapkan target dan kebijakan anggaran retribusi daerah.";
        } else {
            $recommendations[] = "<strong>Sebaiknya Dilatih Ulang:</strong> Akurasi model saat ini masih kurang ideal. Operator disarankan menjalankan pelatihan ulang (menyetel ulang parameter C, Epsilon, Gamma) menggunakan metode Grey Wolf Optimizer (GWO) agar hasilnya lebih presisi.";
        }
    }
@endphp

<div class="row g-3">
    <div class="col-md-7">
        <h6 class="fw-bold text-secondary text-uppercase mb-2 shadow-none border-0 pb-0" style="font-size: 11px; letter-spacing: 0.5px;">Keterangan Hasil Analisis</h6>
        <div class="d-flex flex-column gap-3">
            <!-- MAPE Card -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                <div class="fs-4"><i class="bi {{ $mapeIcon }}"></i></div>
                <div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">MAPE: {{ number_format($mape, 2, ',', '.') }}% — <span class="{{ explode(' ', $mapeColor)[0] }}">{{ $mapeCategory }}</span></div>
                    <div class="text-secondary small">{!! $mapeDesc !!}</div>
                </div>
            </div>
            
            <!-- R2 Score Card -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                <div class="fs-4"><i class="bi {{ $r2Icon }}"></i></div>
                <div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">R² Score: {{ number_format($r2, 4, ',', '.') }} — <span class="text-dark">{{ $r2Category }}</span></div>
                    <div class="text-secondary small">{!! $r2Desc !!}</div>
                </div>
            </div>

            <!-- RMSE Card -->
            <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                <div class="fs-4"><i class="bi {{ $rmseIcon }}"></i></div>
                <div>
                    <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">RMSE: Rp {{ number_format($rmse, 0, ',', '.') }} — <span class="{{ $rmseColor }}">{{ $rmseCategory }}</span></div>
                    <div class="text-secondary small">{!! $rmseDesc !!}</div>
                </div>
            </div>

            <!-- MAE Card -->
            @if($mae !== null)
                <div class="p-3 rounded-3 border border-light bg-light-subtle d-flex gap-3">
                    <div class="fs-4"><i class="bi {{ $maeIcon }}"></i></div>
                    <div>
                        <div class="fw-bold text-dark mb-1" style="font-size: 13.5px;">MAE: Rp {{ number_format($mae, 0, ',', '.') }} — <span class="text-primary-custom">{{ $maeCategory }}</span></div>
                        <div class="text-secondary small">{!! $maeDesc !!}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-5">
        <div class="p-3 rounded-3 h-100 {{ $mapeAlertClass }} border border-0">
            <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center" style="font-size: 11px; letter-spacing: 0.5px;">
                <i class="bi bi-lightbulb-fill me-2 fs-5"></i>{{ $recTitle ?? (in_array($target, ['svr_default_upt', 'svr_default_dishub']) ? 'Kesimpulan & Rekomendasi' : 'Rekomendasi Tindakan') }}
            </h6>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-3" style="font-size: 12.5px; line-height: 1.6;">
                @foreach($recommendations as $rec)
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check2-circle mt-0.5 flex-shrink-0"></i>
                        <span>{!! $rec !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
