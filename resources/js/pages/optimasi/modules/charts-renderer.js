import { config } from './config.js';

let performanceChartInstance = null;
let gsChartInstance = null;
let gwoChartInstance = null;
let comparisonChartInstance = null;

function getFilteredData(preds, rayonId) {
    rayonId = parseInt(rayonId);
    if (rayonId === 0) {
        const grouped = {};
        preds.forEach(p => {
            if (!grouped[p.tanggal]) {
                grouped[p.tanggal] = { actual: 0, predicted: 0 };
            }
            grouped[p.tanggal].actual += p.actual_value;
            grouped[p.tanggal].predicted += p.predicted_value;
        });
        const labels = Object.keys(grouped);
        const actual = labels.map(l => grouped[l].actual);
        const predicted = labels.map(l => grouped[l].predicted);
        return { labels, actual, predicted };
    } else {
        const filtered = preds.filter(p => p.rayon_id === rayonId);
        const labels = filtered.map(p => p.tanggal);
        const actual = filtered.map(p => p.actual_value);
        const predicted = filtered.map(p => p.predicted_value);
        return { labels, actual, predicted };
    }
}

export function updateGsChart(rayonId) {
    const data = getFilteredData(config.allGsPreds, rayonId);
    const countEl = document.getElementById('gs-chart-data-count');
    if (countEl) countEl.innerText = `Total Data: ${data.actual.length}`;
    if (gsChartInstance) {
        gsChartInstance.data.labels = data.labels;
        const dsActual = gsChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
        if (dsActual) dsActual.data = data.actual;
        const dsPredict = gsChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Prediksi SVR (GS)');
        if (dsPredict) dsPredict.data = data.predicted;
        gsChartInstance.update();
    }
}

export function updateGwoChart(rayonId) {
    const data = getFilteredData(config.allGwoPreds, rayonId);
    const countEl = document.getElementById('gwo-chart-data-count');
    if (countEl) countEl.innerText = `Total Data: ${data.actual.length}`;
    if (gwoChartInstance) {
        gwoChartInstance.data.labels = data.labels;
        const dsActual = gwoChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
        if (dsActual) dsActual.data = data.actual;
        const dsPredict = gwoChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Prediksi SVR (GWO)');
        if (dsPredict) dsPredict.data = data.predicted;
        gwoChartInstance.update();
    }
}

export function updateCompChart(rayonId) {
    const defaultData = getFilteredData(config.allDefaultPreds, rayonId);
    const gsData = getFilteredData(config.allGsPreds, rayonId);
    const gwoData = getFilteredData(config.allGwoPreds, rayonId);

    const totalLen = gwoData.labels.length > 0 ? gwoData.labels.length : (gsData.labels.length > 0 ? gsData.labels.length : defaultData.labels.length);
    const countEl = document.getElementById('comparison-chart-data-count');
    if (countEl) countEl.innerText = `Total Data: ${totalLen}`;

    if (comparisonChartInstance) {
        comparisonChartInstance.data.labels = gwoData.labels.length > 0 ? gwoData.labels : (gsData.labels.length > 0 ? gsData.labels : defaultData.labels);

        const dsActual = comparisonChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
        if (dsActual) dsActual.data = gwoData.actual.length > 0 ? gwoData.actual : (gsData.actual.length > 0 ? gsData.actual : defaultData.actual);

        const dsDefault = comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR Standar');
        if (dsDefault) dsDefault.data = defaultData.predicted;

        const dsGs = comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR + Grid Search');
        if (dsGs) dsGs.data = gsData.predicted;

        const dsGwo = comparisonChartInstance.data.datasets.find(ds => ds.label === 'Prediksi SVR + GWO (Grey Wolf)');
        if (dsGwo) dsGwo.data = gwoData.predicted;

        comparisonChartInstance.update();
    }
}

export function initializeAllCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded.');
        return;
    }

    // 1. Performance Chart (Bar/Line comparison)
    const canvasEl = document.getElementById('performanceChart');
    if (canvasEl) {
        const ctx = canvasEl.getContext('2d');
        const mapeSvrDefault = config.chartMetrics.mape_default ?? null;
        const r2SvrDefault = config.chartMetrics.r2_default ?? null;
        const mapeGridSearch = config.chartMetrics.mape_gs ?? null;
        const r2GridSearch = config.chartMetrics.r2_gs ?? null;
        const mapeGwo = config.chartMetrics.mape_gwo ?? null;
        const r2Gwo = config.chartMetrics.r2_gwo ?? null;

        performanceChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['SVR Standar (Default)', 'SVR + Grid Search', 'SVR + GWO (Grey Wolf)'],
                datasets: [
                    {
                        label: 'MAPE (%) (Semakin Kecil Semakin Baik)',
                        data: [mapeSvrDefault, mapeGridSearch, mapeGwo],
                        backgroundColor: [
                            'rgba(220, 38, 38, 0.75)',
                            'rgba(245, 158, 11, 0.75)',
                            'rgba(16, 185, 129, 0.75)'
                        ],
                        borderColor: ['rgb(220, 38, 38)', 'rgb(245, 158, 11)', 'rgb(16, 185, 129)'],
                        borderWidth: 1.5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'R² Score (Semakin Besar Semakin Baik)',
                        data: [r2SvrDefault, r2GridSearch, r2Gwo],
                        backgroundColor: 'rgba(0, 91, 170, 0.15)',
                        borderColor: '#005BAA',
                        borderWidth: 1.5,
                        type: 'line',
                        tension: 0.2,
                        pointBackgroundColor: '#005BAA',
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'Inter', size: 11 } } },
                    tooltip: { padding: 10, backgroundColor: '#1f2937', titleFont: { family: 'Inter', size: 11, weight: 'bold' }, bodyFont: { family: 'Inter', size: 11 } }
                },
                scales: {
                    y: {
                        type: 'linear', display: true, position: 'left',
                        title: { display: true, text: 'MAPE (%)', font: { family: 'Inter', size: 11, weight: 'bold' } },
                        grid: { borderDash: [5, 5], color: '#e2e8f0' },
                        ticks: { callback: v => v + '%', font: { family: 'Inter', size: 10 } }
                    },
                    y1: {
                        type: 'linear', display: true, position: 'right',
                        title: { display: true, text: 'R² Score', font: { family: 'Inter', size: 11, weight: 'bold' } },
                        grid: { drawOnChartArea: false },
                        min: 0, max: 1.0,
                        ticks: { font: { family: 'Inter', size: 10 } }
                    },
                    x: { ticks: { font: { family: 'Inter', size: 11 } } }
                }
            }
        });
    }

    // 2. Comparison Trend Chart
    const canvasCompEl = document.getElementById('comparisonTrendChart');
    if (canvasCompEl) {
        const ctxComp = canvasCompEl.getContext('2d');
        const startRayonId = config.rayonId;
        const defaultData = getFilteredData(config.allDefaultPreds, startRayonId);
        const gsData = getFilteredData(config.allGsPreds, startRayonId);
        const gwoData = getFilteredData(config.allGwoPreds, startRayonId);

        const labelsComp = gwoData.labels.length > 0 ? gwoData.labels : (gsData.labels.length > 0 ? gsData.labels : defaultData.labels);
        const actualDataComp = gwoData.actual.length > 0 ? gwoData.actual : (gsData.actual.length > 0 ? gsData.actual : defaultData.actual);

        const datasetsComp = [
            {
                label: 'Pendapatan Aktual',
                data: actualDataComp,
                borderColor: '#005BAA',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointBackgroundColor: '#005BAA',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1,
                pointRadius: 2.5,
                pointHoverRadius: 4
            }
        ];

        if (defaultData.predicted.length > 0) {
            datasetsComp.push({
                label: 'Prediksi SVR Standar',
                data: defaultData.predicted,
                borderColor: '#6c757d',
                borderWidth: 1.5,
                fill: false,
                tension: 0.3,
                pointBackgroundColor: '#6c757d',
                pointRadius: 2,
                borderDash: [4, 4]
            });
        }

        if (gsData.predicted.length > 0) {
            datasetsComp.push({
                label: 'Prediksi SVR + Grid Search',
                data: gsData.predicted,
                borderColor: '#F59E0B',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointBackgroundColor: '#F59E0B',
                pointRadius: 2,
                borderDash: [3, 3]
            });
        }

        if (gwoData.predicted.length > 0) {
            datasetsComp.push({
                label: 'Prediksi SVR + GWO (Grey Wolf)',
                data: gwoData.predicted,
                borderColor: '#10B981',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointBackgroundColor: '#10B981',
                pointRadius: 2.5,
                pointHoverRadius: 4
            });
        }

        const totalLen = gwoData.labels.length > 0 ? gwoData.labels.length : (gsData.labels.length > 0 ? gsData.labels.length : defaultData.labels.length);
        const countEl = document.getElementById('comparison-chart-data-count');
        if (countEl) countEl.innerText = `Total Data: ${totalLen}`;

        comparisonChartInstance = new Chart(ctxComp, {
            type: 'line',
            data: {
                labels: labelsComp,
                datasets: datasetsComp
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            font: { family: 'Inter', size: 11, weight: '500' },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: '#1f2937',
                        titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 11 },
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                let val = context.raw;
                                return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            font: { family: 'Inter', size: 10 },
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                            }
                        },
                        grid: { borderDash: [5, 5], color: '#e2e8f0' }
                    },
                    x: {
                        ticks: { font: { family: 'Inter', size: 10 }, maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });
    }

    // 3. Grid Search Chart
    const canvasGsEl = document.getElementById('gsChart');
    if (canvasGsEl && config.gsRun && config.allGsPreds.length > 0) {
        const ctxGs = canvasGsEl.getContext('2d');
        const gradientActualGs = ctxGs.createLinearGradient(0, 0, 0, 380);
        gradientActualGs.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
        gradientActualGs.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

        const gradientPredictGs = ctxGs.createLinearGradient(0, 0, 0, 380);
        gradientPredictGs.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
        gradientPredictGs.addColorStop(1, 'rgba(244, 197, 66, 0.0)');

        const startRayonId = config.rayonId;
        const startGsData = getFilteredData(config.allGsPreds, startRayonId);
        const countEl = document.getElementById('gs-chart-data-count');
        if (countEl) countEl.innerText = `Total Data: ${startGsData.actual.length}`;

        gsChartInstance = new Chart(ctxGs, {
            type: 'line',
            data: {
                labels: startGsData.labels,
                datasets: [
                    {
                        label: 'Pendapatan Aktual',
                        data: startGsData.actual,
                        borderColor: '#005BAA',
                        borderWidth: 2,
                        backgroundColor: gradientActualGs,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#005BAA',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 2.5,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'Pendapatan Prediksi SVR (GS)',
                        data: startGsData.predicted,
                        borderColor: '#F4C542',
                        borderWidth: 2,
                        backgroundColor: gradientPredictGs,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#F4C542',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderDash: [5, 5]
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
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            font: { family: 'Inter', size: 11, weight: '500' },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: '#1f2937',
                        titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 11 },
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                let val = context.raw;
                                return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { borderDash: [5, 5], color: '#e2e8f0' },
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                            },
                            font: { family: 'Inter', size: 10 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 9.5 },
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    }
                }
            }
        });
    }

    // 4. GWO Chart
    const canvasGwoEl = document.getElementById('gwoChart');
    if (canvasGwoEl && config.gwoRun && config.allGwoPreds.length > 0) {
        const ctxGwo = canvasGwoEl.getContext('2d');
        const gradientActualGwo = ctxGwo.createLinearGradient(0, 0, 0, 380);
        gradientActualGwo.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
        gradientActualGwo.addColorStop(1, 'rgba(0, 91, 170, 0.0)');

        const gradientPredictGwo = ctxGwo.createLinearGradient(0, 0, 0, 380);
        gradientPredictGwo.addColorStop(0, 'rgba(16, 185, 129, 0.08)');
        gradientPredictGwo.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const startRayonId = config.rayonId;
        const startGwoData = getFilteredData(config.allGwoPreds, startRayonId);
        const countEl = document.getElementById('gwo-chart-data-count');
        if (countEl) countEl.innerText = `Total Data: ${startGwoData.actual.length}`;

        gwoChartInstance = new Chart(ctxGwo, {
            type: 'line',
            data: {
                labels: startGwoData.labels,
                datasets: [
                    {
                        label: 'Pendapatan Aktual',
                        data: startGwoData.actual,
                        borderColor: '#005BAA',
                        borderWidth: 2,
                        backgroundColor: gradientActualGwo,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#005BAA',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 2.5,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'Pendapatan Prediksi SVR (GWO)',
                        data: startGwoData.predicted,
                        borderColor: '#10B981',
                        borderWidth: 2,
                        backgroundColor: gradientPredictGwo,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderDash: [5, 5]
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
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            font: { family: 'Inter', size: 11, weight: '500' },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: '#1f2937',
                        titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 11 },
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                let val = context.raw;
                                return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { borderDash: [5, 5], color: '#e2e8f0' },
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                            },
                            font: { family: 'Inter', size: 10 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 9.5 },
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    }
                }
            }
        });
    }
}
