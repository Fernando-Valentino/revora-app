@props([
    'summary',
    'metrics',
    'avgPctError' => 0.0,
    'avgPeriodDeviation' => 0.0,
    'type' => 'harian',
    'bestRayon' => null,
    'worstRayon' => null,
])

@php
    // Calculate accuracy percentage
    $accuracyPct = max(0, min(100, round(100 - (double)$avgPctError)));
    
    // Map type to Indonesian period label
    $periodUnit = 'hari';
    if ($type === 'mingguan') {
        $periodUnit = 'minggu';
    } elseif ($type === 'bulanan') {
        $periodUnit = 'bulan';
    } elseif ($type === 'tahunan') {
        $periodUnit = 'tahun';
    }
@endphp

{{-- Skeleton Placeholder --}}
<div class="sk-wrapper">
    <div class="hero">
        <span class="skeleton skeleton-text sm" style="width: 150px; margin-bottom: 10px;"></span>
        <div class="d-flex align-items-baseline gap-2 mb-2">
            <span class="skeleton" style="width: 120px; height: 40px; border-radius: 6px;"></span>
        </div>
        <div class="skeleton" style="width: 100%; max-width: 600px; height: 6px; border-radius: 4px; margin-bottom: 15px;"></div>
        <span class="skeleton skeleton-text" style="width: 80%;"></span>
    </div>
    <x-ui.skeleton type="stat" />
</div>

{{-- Real Content --}}
<div class="sk-content">
    <div class="report-card mb-4">
        <!-- HERO: one number, one sentence -->
        <div class="hero" style="margin-bottom: 24px;">
            @if(isset($summary['total_aktual_val']) && $summary['total_aktual_val'] > 0)
                <div class="eyebrow">Kualitas prediksi &middot; {{ $summary['periode'] }}</div>
                <div class="row">
                    <div class="figure num">{{ $accuracyPct }}<small>% akurat</small></div>
                </div>
                <div class="hero-bar">
                    <i style="width: {{ $accuracyPct }}%;"></i>
                </div>
                <p>
                    Dari {{ $summary['total_data'] }} data yang diuji, prediksi sistem rata-rata meleset sekitar 
                    <strong>Rp {{ number_format($avgPeriodDeviation, 0, ',', '.') }}</strong> per {{ $periodUnit }} — 
                    dalam batas wajar dan bisa dipakai untuk menyusun target pendapatan mendatang.
                </p>
            @else
                <div class="eyebrow">Estimasi Proyeksi Pendapatan &middot; {{ $summary['periode'] }}</div>
                <div class="row">
                    <div class="figure num" style="font-size: 26px; color: #005BAA; font-weight: 800;">{{ $summary['total_prediksi'] }}</div>
                </div>
                <p style="margin-top: 8px;">
                    Sistem mendeteksi bahwa periode ini sepenuhnya merupakan <strong>proyeksi masa depan</strong> (belum ada data transaksi aktual di database). Menampilkan estimasi pendapatan hasil peramalan rekursif model SVR.
                </p>
            @endif
        </div>

        <!-- QUICK FACTS -->
        <div class="facts" style="margin-bottom: 0;">
            <div class="fact">
                <div class="label">Rayon paling akurat</div>
                <div class="value good">
                    @if($bestRayon)
                        {{ $bestRayon->rayon_name }} <span class="small text-muted fw-normal" style="font-size: 11.5px;">({{ number_format(100 - $bestRayon->avg_mape, 1, ',', '.') }}% akurasi)</span>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="fact">
                <div class="label">Perlu perhatian</div>
                <div class="value bad">
                    @if($worstRayon)
                        {{ $worstRayon->rayon_name }} <span class="small text-muted fw-normal" style="font-size: 11.5px;">(Error {{ number_format($worstRayon->avg_mape, 1, ',', '.') }}%)</span>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="fact">
                <div class="label">Proyeksi {{ $type === 'harian' ? '7 Hari' : ($type === 'mingguan' ? '4 Minggu' : ($type === 'bulanan' ? '6 Bulan' : '2 Tahun')) }} Ke Depan</div>
                <div class="value num text-primary" id="fact-future-projection-value" style="font-weight: 700; color: #005BAA !important;">
                    -
                </div>
            </div>
        </div>
    </div>
</div>

