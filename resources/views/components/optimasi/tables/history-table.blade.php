@props([
    'runs',
    'bestId' => null,
    'method' => 'grid_search',
    'readonly' => false
])

<div>
    @if($runs->isEmpty())
        <div class="text-center py-4 text-secondary">
            <i class="bi bi-folder2-open fs-2 text-muted mb-2 d-block"></i>
            Belum ada riwayat proses optimasi {{ $method === 'gwo' ? 'GWO' : 'Grid Search' }}.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Waktu</th>
                        <th>Parameter Optimal (C, &epsilon;, &gamma;)</th>
                        <th>MAE</th>
                        <th>RMSE</th>
                        <th>MAPE</th>
                        <th>R&sup2; Score</th>
                        <th>Lama Proses</th>
                        <th class="text-center">Status</th>
                        @if(!$readonly)
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($runs as $run)
                        @php
                            $param = $run->modelParameter;
                            $metric = $run->modelMetrics()->where('dataset_type', 'test')->first();
                            $isActive = ($bestId && $run->id === $bestId);

                            $cVal = '-';
                            if ($param) {
                                $cVal = $param->c_value;
                                if (is_numeric($cVal)) {
                                    $formatted = number_format((float) $cVal, 6, ',', '.');
                                    $cVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                }
                            }
                            $epsVal = '-';
                            if ($param) {
                                $epsVal = $param->epsilon_value;
                                if (is_numeric($epsVal)) {
                                    $formatted = number_format((float) $epsVal, 8, ',', '.');
                                    $epsVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                }
                            }
                            $gamVal = '-';
                            if ($param) {
                                $gamVal = $param->gamma_value;
                                if (is_numeric($gamVal)) {
                                    $formatted = number_format((float) $gamVal, 6, ',', '.');
                                    $gamVal = strpos($formatted, ',') !== false ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
                                }
                            }

                            $maeVal = $metric ? 'Rp ' . number_format($metric->mae, 0, ',', '.') : '-';
                            $rmseVal = $metric ? 'Rp ' . number_format($metric->rmse, 0, ',', '.') : '-';
                            $mapeVal = $metric ? number_format($metric->mape, 2, ',', '.') . '%' : '-';
                            $r2Val = $metric ? number_format($metric->r2_score, 2, ',', '.') : '-';

                            $start = $run->started_at ? \Carbon\Carbon::parse($run->started_at) : null;
                            $end = $run->finished_at ? \Carbon\Carbon::parse($run->finished_at) : null;
                            $durasi = '-';
                            if ($start && $end) {
                                $diffSecs = $start->diffInSeconds($end);
                                if ($diffSecs >= 60) {
                                    $mins = floor($diffSecs / 60);
                                    $secs = $diffSecs % 60;
                                    $durasi = $mins . ' m ' . $secs . ' s';
                                } elseif ($diffSecs > 0) {
                                    $durasi = $diffSecs . ' detik';
                                } else {
                                    $diffMs = $start->diffInMilliseconds($end);
                                    $durasi = $diffMs . ' ms';
                                }
                            }
                            $methodName = $method === 'gwo' ? 'Grey Wolf Optimizer (GWO)' : 'Grid Search';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                {{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">C: {{ $cVal }}</span>
                                <span class="badge bg-light text-dark border">&epsilon;: {{ $epsVal }}</span>
                                <span class="badge bg-light text-dark border">&gamma;: {{ $gamVal }}</span>
                            </td>
                            <td>{{ $maeVal }}</td>
                            <td>{{ $rmseVal }}</td>
                            <td class="fw-bold text-success">{{ $mapeVal }}</td>
                            <td>{{ $r2Val }}</td>
                            <td class="fw-semibold text-secondary">{{ $durasi }}</td>
                            <td class="text-center">
                                @if($isActive)
                                    <span class="badge bg-success text-white rounded-3 px-2 py-1" style="font-size: 11px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-3 px-2 py-1" style="font-size: 11px;">
                                        <i class="bi bi-clock-history me-1"></i>Riwayat
                                    </span>
                                @endif
                            </td>
                            @if(!$readonly)
                                <td class="text-center">
                                    <button type="button" class="btn btn-link text-danger p-0 border-0"
                                        onclick="confirmDeleteOptimasiRun({{ $run->id }}, '{{ \Carbon\Carbon::parse($run->started_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}', '{{ $methodName }}')"
                                        title="Hapus Riwayat">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
