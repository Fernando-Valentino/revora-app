@props([
    'reports',
    'bestRayon',
    'worstRayon',
    'avgDailyDeviation',
    'avgPeriodDeviation' => null,
    'rayonStats',
    'type' => 'harian',
    'summary',
])

{{-- Akurasi Per Rayon list view --}}
@if(count($reports) > 0 && $rayonStats && $rayonStats->count() > 0)
<div class="report-card mb-4">
    <h2 class="section-title">Akurasi per Rayon</h2>
    <p class="section-desc">Diurutkan berdasarkan tingkat akurasi tertinggi (persentase kesalahan model terkecil).</p>
    
    <div class="rayon-list" style="margin-bottom: 0;">
        @foreach($rayonStats->sortBy('avg_mape') as $rs)
            @php
                $mapeVal = abs($rs->avg_mape);
                $accuracyBarPct = max(0, min(100, 100 - $mapeVal));
                
                $fillColor = '#1A7F4E';
                $statusText = 'Sangat Baik';
                $statusClass = 'good';
                
                if ($mapeVal > 20) {
                    $fillColor = '#C22B2B';
                    $statusText = 'Perlu Perhatian';
                    $statusClass = 'bad';
                } elseif ($mapeVal > 10) {
                    $fillColor = '#3FA772';
                    $statusText = 'Baik';
                    $statusClass = 'mid';
                }
            @endphp
            <div class="rayon-row">
                <span class="name">{{ $rs->rayon_name }}</span>
                <div class="bar-wrap">
                    <div class="bar-track">
                        <span class="bar-fill" style="width: {{ $accuracyBarPct }}%; background: {{ $fillColor }};"></span>
                    </div>
                    <span class="pct num">{{ number_format($mapeVal, 1, ',', '.') }}%</span>
                </div>
                <span class="status {{ $statusClass }}">{{ $statusText }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif



