@props([
    'totalPendapatan',
    'periodeAwalFormatted',
    'periodeAkhirFormatted',
    'jumlahRayon',
    'jumlahHariLibur',
    'jumlahWeekend',
    'datasetReady',
    'hasPendapatan',
    'hasRayon',
    'hasJuruParkir',
    'hasHariLibur',
    'lastRun',
    'comparisons',
    'gsRun',
    'historyGsRuns',
    'bestGsId',
    'pipelineData',
    'params',
    'gsMetricsObj',
    'gsChartData',
    'gsPredictions',
    'rayons',
    'rayonId',
    'readonly' => false
])

@php
    $rayonRomanMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];

    $gsBestRayon = null;
    $gsWorstRayon = null;
    $gsAvgDailyDeviation = 0;
    $gsTotalDiffPercent = 0;
    $gsMaxActualDate = '-';
    $gsMaxActualVal = 0;
    $gsPredictedAtMaxActual = 0;
    $gsMaxPredictedDate = '-';
    $gsMaxPredictedVal = 0;

    if ($gsRun) {
        $gsRayonStats = $gsRun->predictionResults()
            ->select(
                'rayon_name',
                DB::raw('AVG(percentage_error) as avg_mape'),
                DB::raw('AVG(error_value) as avg_error'),
                DB::raw('SUM(actual_value) as total_actual'),
                DB::raw('SUM(predicted_value) as total_predicted')
            )
            ->groupBy('rayon_name')
            ->get();

        $gsBestRayon = $gsRayonStats->sortBy('avg_mape')->first();
        $gsWorstRayon = $gsRayonStats->sortByDesc('avg_mape')->first();

        $gsAvgDailyDeviation = $gsRun->predictionResults()->avg('error_value') ?? 0;

        if ($gsChartData->count() > 0) {
            $gsMaxActualRow = $gsChartData->sortByDesc('actual_value')->first();
            $gsMaxPredictedRow = $gsChartData->sortByDesc('predicted_value')->first();

            $gsMaxActualDate = $gsMaxActualRow ? Carbon\Carbon::parse($gsMaxActualRow->tanggal)->translatedFormat('d F Y') : '-';
            $gsMaxActualVal = $gsMaxActualRow ? $gsMaxActualRow->actual_value : 0;
            $gsPredictedAtMaxActual = $gsMaxActualRow ? $gsMaxActualRow->predicted_value : 0;

            $gsMaxPredictedDate = $gsMaxPredictedRow ? Carbon\Carbon::parse($gsMaxPredictedRow->tanggal)->translatedFormat('d F Y') : '-';
            $gsMaxPredictedVal = $gsMaxPredictedRow ? $gsMaxPredictedRow->predicted_value : 0;

            $gsTotalActualSum = $gsChartData->sum('actual_value');
            $gsTotalPredictedSum = $gsChartData->sum('predicted_value');
            $gsTotalDiff = abs($gsTotalActualSum - $gsTotalPredictedSum);
            $gsTotalDiffPercent = $gsTotalActualSum > 0 ? ($gsTotalDiff / $gsTotalActualSum) * 100 : 0;
        }
    }
@endphp

<!-- GRID SEARCH METHOD CONTAINER -->
<div id="method-content-grid" class="method-section">
    <!-- Grid Search Stepper Header -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body py-3">
            <div class="stepper-wrapper">
                <div class="stepper-item active" id="stepper-grid-1" onclick="goToGridStep(1)" style="cursor: pointer;">
                    <div class="step-number">1</div>
                    <div class="step-title">Validasi &amp; Riwayat</div>
                </div>
                <div class="stepper-line" id="stepper-line-grid-1"></div>
                <div class="stepper-item" id="stepper-grid-2" onclick="goToGridStep(2)" style="cursor: pointer;">
                    <div class="step-number">2</div>
                    <div class="step-title">Konfigurasi Grid Search</div>
                </div>
                <div class="stepper-line" id="stepper-line-grid-2"></div>
                <div class="stepper-item" id="stepper-grid-3" onclick="goToGridStep(3)" style="cursor: pointer;">
                    <div class="step-number">3</div>
                    <div class="step-title">Proses Grid Search</div>
                </div>
                <div class="stepper-line" id="stepper-line-grid-3"></div>
                <div class="stepper-item" id="stepper-grid-4" onclick="goToGridStep(4)" style="cursor: pointer;">
                    <div class="step-number">4</div>
                    <div class="step-title">Hasil Grid Search</div>
                </div>
                <div class="stepper-line" id="stepper-line-grid-4"></div>
                <div class="stepper-item" id="stepper-grid-5" onclick="goToGridStep(5)" style="cursor: pointer;">
                    <div class="step-number">5</div>
                    <div class="step-title">Perbandingan Model</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Step 1: Validasi Dataset & Riwayat SVR Standar -->
    <x-optimasi.steppers.shared.step1
        method="grid"
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
    />

    <!-- Grid Step 2: Form -->
    <x-optimasi.steppers.grid-search.step2
        :gsRun="$gsRun"
        :historyGsRuns="$historyGsRuns"
        :bestGsId="$bestGsId"
        :readonly="$readonly"
    />

    <!-- Grid Step 3: Progress -->
    <x-optimasi.steppers.grid-search.step3
        :lastRun="$lastRun"
        :pipelineData="$pipelineData"
        :params="$params"
    />

    <!-- Grid Step 4: Hasil Grid Search -->
    <x-optimasi.steppers.grid-search.step4
        :gsRun="$gsRun"
        :gsMetricsObj="$gsMetricsObj"
        :rayons="$rayons"
        :gsChartData="$gsChartData"
        :gsPredictions="$gsPredictions"
        :rayonId="$rayonId"
        :readonly="$readonly"
        :gsTotalDiffPercent="$gsTotalDiffPercent"
        :gsMaxActualDate="$gsMaxActualDate"
        :gsMaxActualVal="$gsMaxActualVal"
        :gsPredictedAtMaxActual="$gsPredictedAtMaxActual"
        :gsMaxPredictedDate="$gsMaxPredictedDate"
        :gsMaxPredictedVal="$gsMaxPredictedVal"
        :gsBestRayon="$gsBestRayon"
        :gsWorstRayon="$gsWorstRayon"
        :gsAvgDailyDeviation="$gsAvgDailyDeviation"
    />
</div>
