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
    'gwoRun',
    'historyGwoRuns',
    'bestGwoId',
    'pipelineData',
    'params',
    'gwoMetricsObj',
    'gwoChartData',
    'gwoPredictions',
    'rayons',
    'rayonId',
    'readonly' => false
])

@php
    $rayonRomanMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];

    $gwoBestRayon = null;
    $gwoWorstRayon = null;
    $gwoAvgDailyDeviation = 0;
    $gwoTotalDiffPercent = 0;
    $gwoMaxActualDate = '-';
    $gwoMaxActualVal = 0;
    $gwoPredictedAtMaxActual = 0;
    $gwoMaxPredictedDate = '-';
    $gwoMaxPredictedVal = 0;

    if ($gwoRun) {
        $gwoRayonStats = $gwoRun->predictionResults()
            ->select(
                'rayon_name',
                DB::raw('AVG(percentage_error) as avg_mape'),
                DB::raw('AVG(error_value) as avg_error'),
                DB::raw('SUM(actual_value) as total_actual'),
                DB::raw('SUM(predicted_value) as total_predicted')
            )
            ->groupBy('rayon_name')
            ->get();

        $gwoBestRayon = $gwoRayonStats->sortBy('avg_mape')->first();
        $gwoWorstRayon = $gwoRayonStats->sortByDesc('avg_mape')->first();

        $gwoAvgDailyDeviation = $gwoRun->predictionResults()->avg('error_value') ?? 0;

        if ($gwoChartData->count() > 0) {
            $gwoMaxActualRow = $gwoChartData->sortByDesc('actual_value')->first();
            $gwoMaxPredictedRow = $gwoChartData->sortByDesc('predicted_value')->first();

            $gwoMaxActualDate = $gwoMaxActualRow ? Carbon\Carbon::parse($gwoMaxActualRow->tanggal)->translatedFormat('d F Y') : '-';
            $gwoMaxActualVal = $gwoMaxActualRow ? $gwoMaxActualRow->actual_value : 0;
            $gwoPredictedAtMaxActual = $gwoMaxActualRow ? $gwoMaxActualRow->predicted_value : 0;

            $gwoMaxPredictedDate = $gwoMaxPredictedRow ? Carbon\Carbon::parse($gwoMaxPredictedRow->tanggal)->translatedFormat('d F Y') : '-';
            $gwoMaxPredictedVal = $gwoMaxPredictedRow ? $gwoMaxPredictedRow->predicted_value : 0;

            $gwoTotalActualSum = $gwoChartData->sum('actual_value');
            $gwoTotalPredictedSum = $gwoChartData->sum('predicted_value');
            $gwoTotalDiff = abs($gwoTotalActualSum - $gwoTotalPredictedSum);
            $gwoTotalDiffPercent = $gwoTotalActualSum > 0 ? ($gwoTotalDiff / $gwoTotalActualSum) * 100 : 0;
        }
    }
@endphp

<!-- GWO METHOD CONTAINER -->
<div id="method-content-gwo" class="method-section d-none">
    <!-- GWO Stepper Header -->
    <div class="card mb-4 bg-white shadow-sm border border-light">
        <div class="card-body py-3">
            <div class="stepper-wrapper">
                <div class="stepper-item active" id="stepper-gwo-1" onclick="goToGwoStep(1)" style="cursor: pointer;">
                    <div class="step-number">1</div>
                    <div class="step-title">Validasi &amp; Riwayat</div>
                </div>
                <div class="stepper-line" id="stepper-line-gwo-1"></div>
                <div class="stepper-item" id="stepper-gwo-2" onclick="goToGwoStep(2)" style="cursor: pointer;">
                    <div class="step-number">2</div>
                    <div class="step-title">Konfigurasi GWO</div>
                </div>
                <div class="stepper-line" id="stepper-line-gwo-2"></div>
                <div class="stepper-item" id="stepper-gwo-3" onclick="goToGwoStep(3)" style="cursor: pointer;">
                    <div class="step-number">3</div>
                    <div class="step-title">Proses GWO</div>
                </div>
                <div class="stepper-line" id="stepper-line-gwo-3"></div>
                <div class="stepper-item" id="stepper-gwo-4" onclick="goToGwoStep(4)" style="cursor: pointer;">
                    <div class="step-number">4</div>
                    <div class="step-title">Hasil GWO</div>
                </div>
                <div class="stepper-line" id="stepper-line-gwo-4"></div>
                <div class="stepper-item" id="stepper-gwo-5" onclick="goToGwoStep(5)" style="cursor: pointer;">
                    <div class="step-number">5</div>
                    <div class="step-title">Perbandingan Model</div>
                </div>
            </div>
        </div>
    </div>

    <!-- GWO Step 1: Validasi Dataset & Riwayat SVR Standar -->
    <x-optimasi.steppers.shared.step1
        method="gwo"
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

    <!-- GWO Step 2: Form -->
    <x-optimasi.steppers.gwo.step2
        :gwoRun="$gwoRun"
        :historyGwoRuns="$historyGwoRuns"
        :bestGwoId="$bestGwoId"
        :readonly="$readonly"
    />

    <!-- GWO Step 3: Progress & Preprocessing detail -->
    <x-optimasi.steppers.gwo.step3
        :lastRun="$lastRun"
        :pipelineData="$pipelineData"
        :params="$params"
    />

    <!-- GWO Step 4: Hasil GWO -->
    <x-optimasi.steppers.gwo.step4
        :gwoRun="$gwoRun"
        :gwoMetricsObj="$gwoMetricsObj"
        :rayons="$rayons"
        :gwoChartData="$gwoChartData"
        :gwoPredictions="$gwoPredictions"
        :rayonId="$rayonId"
        :readonly="$readonly"
        :gwoTotalDiffPercent="$gwoTotalDiffPercent"
        :gwoMaxActualDate="$gwoMaxActualDate"
        :gwoMaxActualVal="$gwoMaxActualVal"
        :gwoPredictedAtMaxActual="$gwoPredictedAtMaxActual"
        :gwoMaxPredictedDate="$gwoMaxPredictedDate"
        :gwoMaxPredictedVal="$gwoMaxPredictedVal"
        :gwoBestRayon="$gwoBestRayon"
        :gwoWorstRayon="$gwoWorstRayon"
        :gwoAvgDailyDeviation="$gwoAvgDailyDeviation"
    />
</div>
