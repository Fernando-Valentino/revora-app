@props([
    'predictions',
    'method',
    'bestRayon' => null,
    'worstRayon' => null,
    'avgDailyDeviation' => 0
])

<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-custom-nowrap" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Rayon</th>
                    <th class="text-end">Realisasi Aktual</th>
                    <th class="text-end">Hasil Prediksi SVR</th>
                    <th class="text-end">Nilai Error</th>
                    <th class="text-end">Persentase Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predictions as $index => $pred)
                    <tr>
                        <td>{{ $predictions->firstItem() + $index }}</td>
                        <td>{{ Carbon\Carbon::parse($pred->tanggal)->translatedFormat('d F Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $pred->rayon_name }}</span></td>
                        <td class="text-end fw-semibold">Rp {{ number_format($pred->actual_value, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-primary-custom">Rp {{ number_format($pred->predicted_value, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">Rp {{ number_format($pred->error_value, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">{{ number_format($pred->percentage_error, 2, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data prediksi yang cocok dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination container -->
    @if($predictions->hasPages())
        <div class="pagination-container mt-4 d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="text-secondary small">
                Menampilkan {{ $predictions->firstItem() ?? 0 }} - {{ $predictions->lastItem() ?? 0 }} dari {{ $predictions->total() }} data
            </div>
            <div>
                {!! $predictions->appends(request()->query())->links('components.pagination') !!}
            </div>
        </div>
    @endif

    <!-- Analisis Ringkas Rayon -->
    @if($bestRayon || $worstRayon || $avgDailyDeviation)
        <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 {{ $method === 'gwo' ? 'border-dark' : 'border-warning' }} shadow-sm">
            <h6 class="fw-bold text-dark mb-2">
                <i class="bi {{ $method === 'gwo' ? 'bi-activity text-dark' : 'bi-grid-3x3-gap-fill text-warning' }} me-1"></i>
                Kesimpulan Hasil Prediksi Per Rayon ({{ $method === 'gwo' ? 'GWO' : 'Grid Search' }})
            </h6>
            <ul class="mb-0 ps-3 text-secondary small" style="line-height: 1.8;">
                @if($bestRayon)
                    <li>Rayon paling akurat: <strong class="text-success">{{ $bestRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($bestRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                @endif
                @if($worstRayon)
                    <li>Rayon dengan error terbesar: <strong class="text-danger">{{ $worstRayon->rayon_name }}</strong> dengan MAPE <strong>{{ number_format(abs($worstRayon->avg_mape), 2, ',', '.') }}%</strong>.</li>
                @endif
                @if($avgDailyDeviation)
                    <li>Rata-rata selisih prediksi harian: <strong>Rp {{ number_format(abs($avgDailyDeviation), 0, ',', '.') }}</strong> per hari.</li>
                @endif
            </ul>
        </div>
    @endif
</div>
