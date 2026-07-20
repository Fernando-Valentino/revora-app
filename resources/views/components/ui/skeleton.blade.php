@props([
    'type'  => 'card',   {{-- card | text | chart | table | metric | stat --}}
    'rows'  => 5,
    'lines' => 3,
    'height' => null,
])

@if($type === 'metric')
    {{-- 4-column metric cards skeleton (for MAE/RMSE/MAPE/R2) --}}
    <div class="row g-3 mb-4">
        @for($i = 0; $i < 4; $i++)
        <div class="col-md-3">
            <div class="skeleton-metric-card">
                <span class="skeleton skeleton-text sm" style="width: 80px;"></span>
                <span class="skeleton skeleton-text sm" style="width: 60px;"></span>
                <span class="skeleton skeleton-text xl" style="width: 120px; margin-bottom: 0;"></span>
            </div>
        </div>
        @endfor
    </div>

@elseif($type === 'stat')
    {{-- 4-column stat cards skeleton (for summary: total data, aktual, prediksi, MAPE) --}}
    <div class="row g-3 mb-4">
        @for($i = 0; $i < 4; $i++)
        <div class="col-md-3">
            <div class="skeleton-card d-flex align-items-center gap-3">
                <span class="skeleton skeleton-circle" style="width: 42px; height: 42px; flex-shrink: 0;"></span>
                <div style="flex: 1;">
                    <span class="skeleton skeleton-text sm" style="width: 70%;"></span>
                    <span class="skeleton skeleton-text lg" style="width: 90%; margin-bottom: 0;"></span>
                </div>
            </div>
        </div>
        @endfor
    </div>

@elseif($type === 'chart')
    {{-- Chart area skeleton --}}
    <div class="skeleton skeleton-chart" style="height: {{ $height ?? '290px' }};"></div>

@elseif($type === 'table')
    {{-- Table skeleton with n rows --}}
    <div class="card border-0 filter-card p-4 mb-4">
        <div class="card-body p-0">
            <span class="skeleton skeleton-text lg" style="width: 200px; margin-bottom: 18px;"></span>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            @for($c = 0; $c < 5; $c++)
                            <th><span class="skeleton skeleton-text" style="width: {{ rand(60, 100) }}%;"></span></th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @for($r = 0; $r < $rows; $r++)
                        <tr class="skeleton-table-row">
                            @for($c = 0; $c < 5; $c++)
                            <td><span class="skeleton skeleton-text sm" style="width: {{ rand(50, 95) }}%;"></span></td>
                            @endfor
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($type === 'forecast')
    {{-- Right-panel forecast skeleton --}}
    <div class="d-flex flex-column gap-3">
        <span class="skeleton skeleton-text xl" style="width: 70%;"></span>
        <span class="skeleton skeleton-text sm" style="width: 50%;"></span>
        @for($i = 0; $i < 5; $i++)
        <div class="skeleton-row">
            <span class="skeleton skeleton-text sm" style="flex: 2;"></span>
            <span class="skeleton skeleton-text sm" style="flex: 1;"></span>
        </div>
        @endfor
        <span class="skeleton" style="height: 55px; border-radius: 8px; margin-top: 4px;"></span>
    </div>

@elseif($type === 'dashboard-card')
    {{-- Dashboard metric grid skeleton --}}
    <div class="row g-3 mb-4">
        @for($i = 0; $i < 4; $i++)
        <div class="col-md-3">
            <div class="skeleton-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="flex: 1;">
                        <span class="skeleton skeleton-text sm" style="width: 60%;"></span>
                        <span class="skeleton skeleton-text xl" style="width: 80%; margin-bottom: 0;"></span>
                    </div>
                    <span class="skeleton skeleton-circle" style="width: 40px; height: 40px;"></span>
                </div>
                <span class="skeleton skeleton-text sm" style="width: 55%;"></span>
            </div>
        </div>
        @endfor
    </div>

@else
    {{-- Default: generic card skeleton with text lines --}}
    <div class="skeleton-card mb-4">
        <span class="skeleton skeleton-text lg" style="width: 45%; margin-bottom: 16px;"></span>
        @for($i = 0; $i < $lines; $i++)
        <span class="skeleton skeleton-text" style="width: {{ 100 - ($i * 10) }}%;"></span>
        @endfor
    </div>
@endif
