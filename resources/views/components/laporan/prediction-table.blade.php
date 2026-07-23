@props([
    'reports',
    'metrics',
])

{{-- Skeleton Placeholder --}}
<div class="sk-wrapper">
    <x-ui.skeleton type="table" :rows="6" />
</div>

{{-- Real Content --}}
<div class="sk-content">
    <div class="report-card">
        <details class="tech-details" style="border-top: none; padding-top: 0; margin-top: 0;">
            <summary class="tech-summary">Lihat detail teknis &amp; rincian data <span class="chev">⌄</span></summary>
            
            <div class="tech-body">
                <!-- Technical Metrics Grid -->
                <div class="tech-grid">
                    <div class="tech-metric">
                        <div class="l">MAE</div>
                        <div class="v num">{{ $metrics['mae'] }}</div>
                    </div>
                    <div class="tech-metric">
                        <div class="l">RMSE</div>
                        <div class="v num">{{ $metrics['rmse'] }}</div>
                    </div>
                    <div class="tech-metric">
                        <div class="l">MAPE</div>
                        <div class="v num">{{ $metrics['mape'] }}</div>
                    </div>
                    <div class="tech-metric">
                        <div class="l">R&sup2; Score</div>
                        <div class="v num">{{ $metrics['r2'] }}</div>
                    </div>
                </div>

                <!-- Prediction Table -->
                <table class="table-minimal" id="laporanTable">
                    <thead>
                        <tr>
                            <th>Tanggal / Periode</th>
                            <th>Rayon</th>
                            <th style="text-align: right;">Aktual</th>
                            <th style="text-align: right;">Prediksi</th>
                            <th style="text-align: right;">Selisih (Error)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr>
                                <td data-order="{{ $rep['tanggal'] }}">{{ $rep['tanggal_formatted'] ?? $rep['tanggal'] }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill" style="font-size: 10px; font-weight: 500;">
                                        {{ $rep['rayon'] }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 500; font-variant-numeric: tabular-nums;">
                                    @if($rep['aktual'] > 0)
                                        Rp {{ number_format($rep['aktual'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #005BAA; font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format($rep['prediksi'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 500; color: {{ $rep['error'] >= 0 ? '#1A7F4E' : '#C22B2B' }}; font-variant-numeric: tabular-nums;">
                                    @if($rep['aktual'] > 0)
                                        {{ $rep['error'] >= 0 ? '+' : '' }}Rp {{ number_format($rep['error'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary">Tidak ada data transaksi laporan yang cocok dengan kriteria filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</div>


