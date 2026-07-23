@extends('layouts.app')

@section('title', 'Laporan Analisis Prediksi Pendapatan')
@section('subtitle', 'Halaman ini digunakan untuk melihat, menganalisis, dan mengekspor laporan perbandingan realisasi vs prediksi pendapatan retribusi.')

@section('content')
<x-laporan.styles />

<div class="container-fluid p-0 report-container">

    <x-laporan.filter-toolbar
        :summary="$summary"
        :metrics="$metrics"
        :rayons="$rayons"
        :rayonId="$rayonId"
        :startDate="$startDate"
        :endDate="$endDate"
        :type="$type ?? 'harian'"
        :readonly="true"
        :pdfRoute="route('kepala-dishub.laporan.export-pdf', request()->query())"
        :excelRoute="null"
    />

    <div id="report-content-wrapper">
        <x-laporan.summary-cards :summary="$summary" :metrics="$metrics" :avgPctError="$avgPctError" :avgPeriodDeviation="$avgPeriodDeviation" :type="$type ?? 'harian'" :bestRayon="$bestRayon" :worstRayon="$worstRayon" />

        <x-laporan.chart-section
            :chartLabels="$chartLabels"
            :chartActualValues="$chartActualValues"
            :chartPredictValues="$chartPredictValues"
            :rayonId="$rayonId"
            :type="$type ?? 'harian'"
            :forecastRoute="route('kepala-dishub.laporan.forecast')"
        />

        <x-laporan.rayon-analysis
            :reports="$reports"
            :bestRayon="$bestRayon"
            :worstRayon="$worstRayon"
            :avgDailyDeviation="$avgDailyDeviation"
            :avgPeriodDeviation="$avgPeriodDeviation ?? null"
            :rayonStats="$rayonStats"
            :type="$type ?? 'harian'"
            :summary="$summary"
        />

        <x-laporan.prediction-table :reports="$reports" :metrics="$metrics" />
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let laporanChartInstance = null;
    let forecastChartInstance = null;

    function initLaporanChart() {
        const canvas = document.getElementById('laporanChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (laporanChartInstance) laporanChartInstance.destroy();
        let labels = [], actualValues = [], predictValues = [];
        try {
            labels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
            actualValues = JSON.parse(canvas.getAttribute('data-actual') || '[]');
            predictValues = JSON.parse(canvas.getAttribute('data-predict') || '[]');
        } catch (e) { console.error(e); }
        if (labels.length === 0) return;
        const gradientActual = ctx.createLinearGradient(0, 0, 0, 260);
        gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.08)');
        gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');
        const gradientPredict = ctx.createLinearGradient(0, 0, 0, 260);
        gradientPredict.addColorStop(0, 'rgba(244, 197, 66, 0.04)');
        gradientPredict.addColorStop(1, 'rgba(244, 197, 66, 0.0)');
        laporanChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Realisasi (Aktual)',
                        data: actualValues,
                        borderColor: '#005BAA',
                        borderWidth: 2,
                        backgroundColor: gradientActual,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#005BAA',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Prediksi',
                        data: predictValues,
                        borderColor: '#F4C542',
                        borderWidth: 2,
                        backgroundColor: gradientPredict,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#F4C542',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderDash: [5, 4]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 8, boxHeight: 8, font: { family: 'Inter', size: 11, weight: '500' } }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 11 },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { borderDash: [6, 6], color: '#e2e8f0', drawBorder: false },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                            },
                            font: { family: 'Inter', size: 10 }
                        }
                    },
                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter', size: 9.5 } } }
                }
            }
        });
    }

    window.changeForecastModel = function (model) {
        initFutureForecast();
    };

    function initFutureForecast() {
        const forecastCard = document.getElementById('future-forecast-card-body');
        if (!forecastCard) return;
        const rayonId = forecastCard.getAttribute('data-rayon-id') || 0;
        const type = forecastCard.getAttribute('data-type') || 'harian';
        const forecastUrl = forecastCard.getAttribute('data-forecast-url');
        const modelSelect = document.getElementById('forecastModel');
        const modelType = modelSelect ? modelSelect.value : '';

        const loadingState = document.getElementById('forecast-loading-state');
        const contentState = document.getElementById('forecast-content-state');
        const errorState = document.getElementById('forecast-error-state');
        const errorMsg = document.getElementById('forecast-error-msg');
        loadingState.style.setProperty('display', 'block', 'important');
        contentState.style.setProperty('display', 'none', 'important');
        errorState.style.setProperty('display', 'none', 'important');
        fetch(`${forecastUrl}?rayon_id=${rayonId}&type=${type}&model_type=${modelType}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    const data = res.data;
                    document.getElementById('forecast-title').innerText = data.title || 'Prediksi Ke Depan';
                    document.getElementById('forecast-total-predicted').innerText = data.total_predicted;
                    document.getElementById('forecast-avg-predicted').innerText = data.avg_predicted;
                    
                    const infoAlert = document.getElementById('forecastInfoAlert');
                    const infoText = document.getElementById('forecastInfoText');
                    if (infoAlert && infoText) {
                        infoAlert.classList.remove('d-none');
                        let modelNameMap = {
                            'baseline': 'SVR Baseline (Standar)',
                            'grid_search': 'SVR Grid Search Tuning',
                            'gwo': 'SVR Grey Wolf Optimizer (GWO)'
                        };
                        let selectedModelName = modelNameMap[data.model_type] || data.model_type;
                        let bestModelName = modelNameMap[data.best_model_type] || data.best_model_type;
                        let isBestText = (data.model_type === data.best_model_type) 
                            ? `<strong>terpilih otomatis</strong> karena performanya paling optimal`
                            : `diatur secara manual (model dengan akurasi terbaik adalah <strong>${bestModelName}</strong>)`;
                        
                        infoText.innerHTML = `Model <strong>${selectedModelName}</strong> ${isBestText}. <br><i class="bi bi-shield-fill-check text-success me-1"></i> <strong>Keyakinan:</strong> ${data.confidence_note}`;
                    }

                    const quickFactValue = document.getElementById('fact-future-projection-value');
                    if (quickFactValue) quickFactValue.innerText = data.total_predicted;

                    // Render Future Projection Chart
                    const canvas = document.getElementById('forecastChart');
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        if (forecastChartInstance) forecastChartInstance.destroy();

                        const labels = (data.detail_harian || []).map(day => type === 'harian' ? day.label.replace(/^([A-Za-z]{3})[a-z]*, (.*) 202\d$/, '$1, $2') : day.label);
                        const values = (data.detail_harian || []).map(day => day.pendapatan);

                        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                        gradient.addColorStop(0, 'rgba(244, 197, 66, 0.15)');
                        gradient.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

                        forecastChartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{ label: 'Proyeksi (Prediksi)', data: values, borderColor: '#F4C542', borderWidth: 2, backgroundColor: gradient, fill: true, tension: 0.3, pointBackgroundColor: '#F4C542', pointBorderColor: '#ffffff', pointBorderWidth: 1, pointRadius: 3, pointHoverRadius: 5 }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { padding: 10, backgroundColor: 'rgba(15, 23, 42, 0.95)', callbacks: { label: c => ' Proyeksi: Rp ' + new Intl.NumberFormat('id-ID').format(c.raw) } } },
                                scales: {
                                    y: { grid: { borderDash: [6, 6], color: '#e2e8f0', drawBorder: false }, ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v), font: { family: 'Inter', size: 10 } } },
                                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter', size: 9 }, autoSkip: true, maxTicksLimit: 8 } }
                                }
                            }
                        });
                    }

                    const recsList = document.getElementById('forecast-recommendations');
                    recsList.innerHTML = '';
                    (data.recommendations && data.recommendations.length > 0 ? data.recommendations : ['Tidak ada rekomendasi spesifik.']).forEach(rec => { recsList.insertAdjacentHTML('beforeend', `<li class="mb-1">${rec}</li>`); });
                    loadingState.style.setProperty('display', 'none', 'important');
                    contentState.style.setProperty('display', 'flex', 'important');
                } else { throw new Error(res.message || 'Gagal.'); }
            })
            .catch(err => {
                loadingState.style.setProperty('display', 'none', 'important');
                if (errorMsg) errorMsg.innerText = err.message || 'Gagal memuat proyeksi.';
                errorState.style.setProperty('display', 'block', 'important');
            });
    }

    function initAllReportScripts() {
        if ($.fn.DataTable.isDataTable('#laporanTable')) $('#laporanTable').DataTable().destroy();
        $('#laporanTable').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }, pageLength: 10, lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]], order: [[0, 'asc']] });
        initFutureForecast();
        initLaporanChart();
    }

    function submitLaporanFilterAjax() {
        // Sync hidden date inputs from dynamic period selectors
        if (typeof computePeriodDates === 'function') computePeriodDates();

        const form = document.getElementById('laporan-filter-form');
        if (!form) return;
        const start_date = form.querySelector('input[name="start_date"]').value;
        const end_date = form.querySelector('input[name="end_date"]').value;
        const rayon_id = form.querySelector('select[name="rayon_id"]').value;
        const type = form.querySelector('select[name="type"]').value;
        const query = new URLSearchParams({ start_date, end_date, rayon_id, type }).toString();
        const fetchUrl = `${window.location.pathname}?${query}`;
        window.history.pushState(null, '', fetchUrl);

        // Update export buttons href dynamically
        const pdfBtn = document.getElementById('btn-export-pdf');
        if (pdfBtn) {
            const basePath = window.location.pathname.replace(/\/$/, '') + '/export-pdf';
            pdfBtn.href = `${basePath}?${query}`;
        }
        const excelBtn = document.getElementById('btn-export-excel');
        if (excelBtn) {
            const basePath = window.location.pathname.replace(/\/$/, '') + '/export-excel';
            excelBtn.href = `${basePath}?${query}`;
        }
        const wrapper = document.getElementById('report-content-wrapper');
        if (wrapper) { wrapper.style.opacity = '0.5'; wrapper.style.pointerEvents = 'none'; }
        fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const oldMeta = document.getElementById('report-meta-area');
                const newMeta = doc.getElementById('report-meta-area');
                if (oldMeta && newMeta) oldMeta.innerHTML = newMeta.innerHTML;
                const oldWrapper = document.getElementById('report-content-wrapper');
                const newWrapper = doc.getElementById('report-content-wrapper');
                if (oldWrapper && newWrapper) { oldWrapper.innerHTML = newWrapper.innerHTML; oldWrapper.style.opacity = '1'; oldWrapper.style.pointerEvents = 'auto'; }
                initAllReportScripts();
            })
            .catch(err => { console.error(err); if (wrapper) { wrapper.style.opacity = '1'; wrapper.style.pointerEvents = 'auto'; } });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initAllReportScripts();
        // Event delegation — works even after type-switch rebuilds the period inputs
        document.addEventListener('change', function(e) {
            var el = e.target;
            if (el.closest && el.closest('form.toolbar')) {
                if (el.tagName === 'SELECT' || el.type === 'date' ||
                    el.type === 'number') {
                    // Don't re-submit immediately when type changes so switchPeriodInputs runs first
                    if (el.id !== 'filter-type-select') {
                        submitLaporanFilterAjax();
                    } else {
                        // Type changed: switchPeriodInputs already ran via toolbar script,
                        // then submit with a tiny delay so DOM is updated
                        setTimeout(submitLaporanFilterAjax, 50);
                    }
                }
            }
        });
    });
</script>
@endsection
