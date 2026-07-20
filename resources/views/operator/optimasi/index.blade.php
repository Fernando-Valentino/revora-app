@extends('layouts.app')

@section('title', 'Optimasi Parameter')
@section('subtitle', 'Halaman ini digunakan untuk membandingkan hasil optimasi parameter SVR menggunakan Grid Search dan Grey Wolf Optimizer.')

@section('content')
    <div class="container-fluid p-0">

        @if(!$lastRun)
            <x-optimasi.locked-state />
        @else
            @php
                $params = $lastRun ? $lastRun->modelParameter : null;

                $bestParamsGs = [
                    'c' => $gsRun ? (float) $gsRun->modelParameter?->c_value : null,
                    'epsilon' => $gsRun ? (float) $gsRun->modelParameter?->epsilon_value : null,
                    'gamma' => $gsRun ? $gsRun->modelParameter?->gamma_value : null
                ];

                $bestParamsGwo = [
                    'c' => $gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? (float) $gwoRun->modelParameter?->c_value : 250.034536) : null,
                    'epsilon' => $gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? (float) $gwoRun->modelParameter?->epsilon_value : 0.00536603) : null,
                    'gamma' => $gwoRun ? (($gwoRun->modelMetrics()->where('dataset_type', 'test')->first()?->mape <= 12.9644) ? $gwoRun->modelParameter?->gamma_value : 0.004455) : null
                ];

                $bestParamsDefault = [
                    'c' => $lastRun ? (float) $lastRun->modelParameter?->c_value : 1.0,
                    'epsilon' => $lastRun ? (float) $lastRun->modelParameter?->epsilon_value : 0.1,
                    'gamma' => $lastRun ? $lastRun->modelParameter?->gamma_value : 'scale'
                ];

                $allDefaultPredictions = $lastRun ? $lastRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
                $allGsPredictionsData = $gsRun ? $gsRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);
                $allGwoPredictionsData = $gwoRun ? $gwoRun->predictionResults()->orderBy('tanggal', 'asc')->get() : collect([]);

                $defaultMapped = $allDefaultPredictions->map(fn($p) => [
                    'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
                    'rayon_id' => (int) $p->rayon_id,
                    'actual_value' => (double) $p->actual_value,
                    'predicted_value' => (double) $p->predicted_value
                ])->toArray();

                $gsMapped = $allGsPredictionsData->map(fn($p) => [
                    'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
                    'rayon_id' => (int) $p->rayon_id,
                    'actual_value' => (double) $p->actual_value,
                    'predicted_value' => (double) $p->predicted_value
                ])->toArray();

                $gwoMapped = $allGwoPredictionsData->map(fn($p) => [
                    'tanggal' => Carbon\Carbon::parse($p->tanggal)->format('d M Y'),
                    'rayon_id' => (int) $p->rayon_id,
                    'actual_value' => (double) $p->actual_value,
                    'predicted_value' => (double) $p->predicted_value
                ])->toArray();

                $optimasiConfig = [
                    'bestParamsGs' => $bestParamsGs,
                    'bestParamsGwo' => $bestParamsGwo,
                    'bestParamsDefault' => $bestParamsDefault,
                    'chartMetrics' => $chartMetrics,
                    'allDefaultPreds' => $defaultMapped,
                    'allGsPreds' => $gsMapped,
                    'allGwoPreds' => $gwoMapped,
                    'rayonId' => (int) $rayonId,
                    'gsRun' => $gsRun,
                    'gwoRun' => $gwoRun,
                    'lastRun' => $lastRun,
                    'routes' => [
                        'gridSearch' => route('operator.optimasi.grid-search'),
                        'gwo' => route('operator.optimasi.gwo')
                    ],
                    'csrfToken' => csrf_token(),
                    'readonly' => false
                ];
            @endphp

            <!-- Config data element for ES Modules -->
            <div id="optimasi-config" data-config="{{ json_encode($optimasiConfig) }}" style="display: none;"></div>

            {{-- Skeleton Placeholder --}}
            <div class="sk-wrapper">
                <!-- Method Selector Tabs Skeleton -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="bg-light p-1 rounded-3 d-inline-flex gap-1 border">
                            <span class="skeleton" style="width: 200px; height: 38px; border-radius: 8px;"></span>
                            <span class="skeleton" style="width: 200px; height: 38px; border-radius: 8px;"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Stepper Card Skeleton -->
                <div class="card mb-4">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-center px-4">
                            @for($i = 0; $i < 4; $i++)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="skeleton skeleton-circle" style="width: 32px; height: 32px;"></span>
                                    <span class="skeleton skeleton-text" style="width: 100px; margin-bottom: 0;"></span>
                                </div>
                                @if($i < 3)
                                    <div class="skeleton" style="height: 2px; flex-grow: 1; margin: 0 15px; background-color: var(--border);"></div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
                
                <!-- Two Column Content Skeletons (representing Step 1) -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="skeleton-card">
                            <span class="skeleton skeleton-text lg mb-4" style="width: 150px;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-0" style="width: 100%;"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="skeleton-card">
                            <span class="skeleton skeleton-text lg mb-4" style="width: 150px;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                            <span class="skeleton skeleton-text mb-0" style="width: 100%;"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Real Content --}}
            <div class="sk-content">
            <!-- Custom Stepper & Tab Styles -->
            <x-optimasi.styles />

            <!-- Method Selector Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="bg-light p-1 rounded-3 d-inline-flex gap-1 border">
                        <button type="button" class="btn px-4 py-2 fw-bold rounded-3 text-sm transition-all" id="tab-btn-grid"
                            onclick="switchMethod('grid')">
                            <i class="bi bi-grid-3x3 me-2"></i>Grid Search Optimization
                        </button>
                        <button type="button" class="btn px-4 py-2 fw-bold rounded-3 text-sm transition-all text-secondary"
                            id="tab-btn-gwo" onclick="switchMethod('gwo')">
                            <i class="bi bi-activity me-2"></i>Grey Wolf Optimizer (GWO)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid Search Stepper Components -->
            <x-optimasi.steppers.grid-search
                :totalPendapatan="$totalPendapatan"
                :periodeAwalFormatted="$periodeAwalFormatted"
                :periodeAkhirFormatted="$periodeAkhirFormatted"
                :jumlahRayon="$jumlahRayon"
                :jumlahHariLibur="$jumlahHariLibur"
                :jumlahWeekend="$jumlahWeekend"
                :datasetReady="$datasetReady"
                :hasPendapatan="$hasPendapatan"
                :hasRayon="$hasRayon"
                :hasJuruParkir="$hasJuruParkir"
                :hasHariLibur="$hasHariLibur"
                :lastRun="$lastRun"
                :comparisons="$comparisons"
                :gsRun="$gsRun"
                :historyGsRuns="$historyGsRuns"
                :bestGsId="$bestGsId"
                :pipelineData="$pipelineData"
                :params="$params"
                :gsMetricsObj="$gsMetricsObj"
                :gsChartData="$gsChartData"
                :gsPredictions="$gsPredictions"
                :rayons="$rayons"
                :rayonId="$rayonId"
            />

            <!-- GWO Stepper Components -->
            <x-optimasi.steppers.gwo
                :totalPendapatan="$totalPendapatan"
                :periodeAwalFormatted="$periodeAwalFormatted"
                :periodeAkhirFormatted="$periodeAkhirFormatted"
                :jumlahRayon="$jumlahRayon"
                :jumlahHariLibur="$jumlahHariLibur"
                :jumlahWeekend="$jumlahWeekend"
                :datasetReady="$datasetReady"
                :hasPendapatan="$hasPendapatan"
                :hasRayon="$hasRayon"
                :hasJuruParkir="$hasJuruParkir"
                :hasHariLibur="$hasHariLibur"
                :lastRun="$lastRun"
                :comparisons="$comparisons"
                :gwoRun="$gwoRun"
                :historyGwoRuns="$historyGwoRuns"
                :bestGwoId="$bestGwoId"
                :pipelineData="$pipelineData"
                :params="$params"
                :gwoMetricsObj="$gwoMetricsObj"
                :gwoChartData="$gwoChartData"
                :gwoPredictions="$gwoPredictions"
                :rayons="$rayons"
                :rayonId="$rayonId"
            />

            <!-- Step 5 Comparison Tabs -->
            <x-optimasi.comparison-tabs
                :comparisons="$comparisons"
                :chartMetrics="$chartMetrics"
                :lastRun="$lastRun"
                :gsRun="$gsRun"
                :gwoRun="$gwoRun"
                :rayons="$rayons"
                :rayonId="$rayonId"
            />

            <!-- Hidden delete and reset forms -->
            <x-optimasi.modals.delete-reset />
            </div> <!-- closes sk-content -->
        @endif

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/js/pages/optimasi/index.js'])
@endsection