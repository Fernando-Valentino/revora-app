@extends('layouts.app')

@section('title', 'Optimasi Parameter')
@section('subtitle', 'Halaman ini digunakan untuk melihat hasil perbandingan optimasi parameter model prediksi.')

@section('content')
<div class="container-fluid p-0">
    @php
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
            'bestParamsGs' => [
                'c' => $gsRun ? (float) $gsRun->modelParameter?->c_value : null,
                'epsilon' => $gsRun ? (float) $gsRun->modelParameter?->epsilon_value : null,
                'gamma' => $gsRun ? $gsRun->modelParameter?->gamma_value : null
            ],
            'bestParamsGwo' => [
                'c' => $gwoRun ? (float) $gwoRun->modelParameter?->c_value : null,
                'epsilon' => $gwoRun ? (float) $gwoRun->modelParameter?->epsilon_value : null,
                'gamma' => $gwoRun ? $gwoRun->modelParameter?->gamma_value : null
            ],
            'bestParamsDefault' => [
                'c' => $lastRun ? (float) $lastRun->modelParameter?->c_value : 1.0,
                'epsilon' => $lastRun ? (float) $lastRun->modelParameter?->epsilon_value : 0.1,
                'gamma' => $lastRun ? $lastRun->modelParameter?->gamma_value : 'scale'
            ],
            'chartMetrics' => $chartMetrics,
            'allDefaultPreds' => $defaultMapped,
            'allGsPreds' => $gsMapped,
            'allGwoPreds' => $gwoMapped,
            'rayonId' => (int) $rayonId,
            'gsRun' => $gsRun,
            'gwoRun' => $gwoRun,
            'lastRun' => $lastRun,
            'routes' => [],
            'csrfToken' => csrf_token(),
            'readonly' => true
        ];
    @endphp

    <!-- Config data element for ES Modules -->
    <div id="optimasi-config" data-config="{{ json_encode($optimasiConfig) }}" style="display: none;"></div>

    <x-optimasi.comparison-tabs 
        :comparisons="$comparisons"
        :chartMetrics="$chartMetrics"
        :lastRun="$lastRun"
        :gsRun="$gsRun"
        :gwoRun="$gwoRun"
        :rayons="$rayons"
        :rayonId="$rayonId"
        :readonly="true"
        :gridBest="$grid_best"
        :gwoBest="$gwo_best"
    />
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/js/pages/optimasi/index.js'])
@endsection
