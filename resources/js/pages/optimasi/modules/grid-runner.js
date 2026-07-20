import { config } from './config.js';
import { swalConfirmPrimary, swalError } from './dialog-helper.js';
import { setPipeStatus, buildComparisonHtml } from './runner-helper.js';
import { clearTempParams, updateGridInfoText } from './param-manager.js';

export let isGridRunning = false;
let gridApiFinished = false;
let gridApiError = null;
let gridApiData = null;
let gridCurrentStep = 1;
let gridTimeout = null;
let gridWaitCount = 0;
const GRID_MAX_WAIT = 9000; // 9000 * 200ms = 30 minutes max wait

let elapsedTimerInterval = null;
let elapsedSeconds = 0;

export function startGridSearchTuning() {
    swalConfirmPrimary(
        'Jalankan Grid Search?',
        'Sistem akan melatih kombinasi parameter SVR menggunakan 5-Fold Cross Validation. Harap tunggu hingga selesai.',
        'Ya, Jalankan!',
        function() {
            isGridRunning = true;
            if (window.goToGridStep) window.goToGridStep(3);
            executeGridSearchTuning();
        }
    );
}

function startElapsedTimer(method, estimatedSeconds) {
    elapsedSeconds = 0;
    if (elapsedTimerInterval) clearInterval(elapsedTimerInterval);

    const elapsedEl = document.getElementById(`${method}-elapsed-timer`);
    const estimatedEl = document.getElementById(`${method}-estimated-timer`);

    if (elapsedEl) elapsedEl.innerText = "0s";
    if (estimatedEl) estimatedEl.innerText = `~${estimatedSeconds}s`;

    elapsedTimerInterval = setInterval(() => {
        elapsedSeconds++;
        if (elapsedEl) elapsedEl.innerText = `${elapsedSeconds}s`;

        if (method === 'grid') {
            const gridBar = document.getElementById('grid-progress-bar');
            const gridLabel = document.getElementById('grid-iter-label');
            const gridPct = document.getElementById('grid-iter-pct');
            const maxCombos = parseInt(gridLabel?.getAttribute('data-max') || '80');

            let currentCombo = Math.min(maxCombos - 1, Math.floor((elapsedSeconds / estimatedSeconds) * maxCombos));
            let pct = Math.min(95, Math.round((elapsedSeconds / estimatedSeconds) * 95));

            if (gridBar) {
                gridBar.style.width = pct + '%';
                gridBar.setAttribute('aria-valuenow', pct);
            }
            if (gridLabel) gridLabel.innerText = `Kombinasi Grid: ${currentCombo} / ${maxCombos}`;
            if (gridPct) gridPct.innerText = pct + '%';
        }
    }, 1000);
}

function stopElapsedTimer() {
    if (elapsedTimerInterval) {
        clearInterval(elapsedTimerInterval);
        elapsedTimerInterval = null;
    }
}

function executeGridSearchTuning() {
    isGridRunning = true;

    // Reset UI containers for running state
    const spinnerContainer = document.getElementById('grid-spinner-container');
    const successContainer = document.getElementById('grid-success-container');
    const timerBox = document.getElementById('grid-timer-box');
    if (spinnerContainer) spinnerContainer.classList.remove('d-none');
    if (successContainer) successContainer.classList.add('d-none');
    if (timerBox) timerBox.classList.remove('d-none');

    // Reset states
    gridApiFinished = false;
    gridApiError = null;
    gridApiData = null;
    gridCurrentStep = 1;
    gridWaitCount = 0;
    if (gridTimeout) clearTimeout(gridTimeout);

    for (let i = 1; i <= 6; i++) setPipeStatus('grid', i, 'pending');

    const titleEl = document.getElementById('grid-process-title');
    const descEl = document.getElementById('grid-process-desc');
    if (titleEl) titleEl.innerText = "Sedang Menyiapkan Grid Search...";
    if (descEl) descEl.innerText = "Model SVR standar sedang disiapkan untuk pengujian kombinasi parameter.";

    setPipeStatus('grid', 1, 'processing');
    gridTimeout = setTimeout(runGridStepSequence, 800);

    // Calculate estimated seconds
    let cLen = 5, epsLen = 4, gammaLen = 4;
    try {
        const rawC = document.getElementById('grid_c')?.value || '';
        const rawEps = document.getElementById('grid_epsilon')?.value || '';
        const rawGamma = document.getElementById('grid_gamma')?.value || '';
        if (rawC.startsWith('[')) cLen = JSON.parse(rawC.replace(/'/g, '"')).length;
        if (rawEps.startsWith('[')) epsLen = JSON.parse(rawEps.replace(/'/g, '"')).length;
        if (rawGamma.startsWith('[')) gammaLen = JSON.parse(rawGamma.replace(/'/g, '"')).length;
    } catch (e) {
        console.error("Failed to parse grid range for timer estimate:", e);
    }
    let totalCombinations = cLen * epsLen * gammaLen;
    let totalFits = totalCombinations * 5;

    // Reset grid progress bar
    const gridBar = document.getElementById('grid-progress-bar');
    const gridLabel = document.getElementById('grid-iter-label');
    const gridPct = document.getElementById('grid-iter-pct');
    const gridBarContainer = document.getElementById('grid-progress-bar-container');
    if (gridBar) { gridBar.style.width = '0%'; gridBar.setAttribute('aria-valuenow', '0'); }
    if (gridLabel) {
        gridLabel.innerText = `Kombinasi Grid: 0 / ${totalCombinations}`;
        gridLabel.setAttribute('data-max', totalCombinations.toString());
    }
    if (gridPct) gridPct.innerText = "0%";
    if (gridBarContainer) gridBarContainer.classList.remove('d-none');

    let estimatedSeconds = Math.max(10, Math.ceil(totalFits * 0.20));
    startElapsedTimer('grid', estimatedSeconds);

    // Collect form params
    const formData = {
        grid_c: document.getElementById('grid_c')?.value || '',
        grid_epsilon: document.getElementById('grid_epsilon')?.value || '',
        grid_gamma: document.getElementById('grid_gamma')?.value || '',
    };

    const targetUrl = config.routes.gridSearch || "/operator/optimasi/grid-search";

    fetch(targetUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': config.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(r => r.ok ? r.json() : r.json().then(e => { throw e; }))
        .then(data => {
            console.log("Grid Search response:", data);
            gridApiFinished = true;
            gridApiData = data;
        })
        .catch(err => {
            console.error("Grid Search error:", err);
            gridApiFinished = true;
            gridApiError = err;
        });
}

function runGridStepSequence() {
    const titleEl = document.getElementById('grid-process-title');
    const descEl = document.getElementById('grid-process-desc');

    if (gridApiError) {
        isGridRunning = false;
        stopElapsedTimer();
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById(`grid-pipe-${i}`);
            if (el && el.classList.contains('active')) setPipeStatus('grid', i, 'failed');
        }
        swalError('Gagal!', gridApiError.message || 'Optimasi Grid Search gagal.');
        if (window.goToGridStep) window.goToGridStep(2);
        return;
    }

    if (gridCurrentStep === 1) {
        // Set all preprocessing pipeline steps to success sequentially
        setPipeStatus('grid', 1, 'success');
        setPipeStatus('grid', 2, 'success');
        setPipeStatus('grid', 3, 'success');
        setPipeStatus('grid', 4, 'success');
        setPipeStatus('grid', 5, 'success');
        setPipeStatus('grid', 6, 'processing');
        gridCurrentStep = 2;
        if (titleEl) titleEl.innerText = "Membangun Kombinasi Parameter...";
        if (descEl) descEl.innerText = "Membuat kombinasi C, Epsilon, dan Gamma dari list konfigurasi.";
        gridTimeout = setTimeout(runGridStepSequence, 1000);
    } else if (gridCurrentStep === 2) {
        gridCurrentStep = 3;
        if (titleEl) titleEl.innerText = "Pelatihan Cross Validation SVR...";
        if (descEl) descEl.innerText = "Menjalankan 5-Fold Cross Validation untuk setiap kombinasi parameter.";
        gridTimeout = setTimeout(runGridStepSequence, 500); // start checking API status
    } else if (gridCurrentStep === 3) {
        if (gridApiFinished) {
            // Complete progress bar immediately
            const gridBar = document.getElementById('grid-progress-bar');
            const gridLabel = document.getElementById('grid-iter-label');
            const gridPct = document.getElementById('grid-iter-pct');
            const maxCombos = parseInt(gridLabel?.getAttribute('data-max') || '80');
            if (gridBar) {
                gridBar.style.width = '100%';
                gridBar.setAttribute('aria-valuenow', '100');
                gridBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            }
            if (gridLabel) gridLabel.innerText = `Kombinasi Grid: Selesai (${maxCombos} / ${maxCombos})`;
            if (gridPct) gridPct.innerText = '100%';

            gridCurrentStep = 4;
            if (titleEl) titleEl.innerText = "Mengevaluasi Parameter Optimal...";
            if (descEl) descEl.innerText = "Mencari model dengan MAPE terkecil.";
            gridTimeout = setTimeout(runGridStepSequence, 1200); // brief pause to let user see step 4
        } else {
            // Safety cutoff check
            gridWaitCount++;
            if (gridWaitCount >= GRID_MAX_WAIT) {
                gridApiFinished = true;
                gridApiError = { message: 'Waktu tunggu tuning Grid Search habis (timeout). Server membutuhkan waktu lebih lama untuk memproses.' };
            }
            gridTimeout = setTimeout(runGridStepSequence, 200);
        }
    } else if (gridCurrentStep === 4) {
        isGridRunning = false;
        stopElapsedTimer();
        setPipeStatus('grid', 6, 'success');
        if (titleEl) titleEl.innerText = "Tuning Selesai!";
        if (descEl) descEl.innerText = "Parameter terbaik berhasil dianalisis.";

        setTimeout(() => {
            const data = gridApiData || {};
            const isBetter = data.is_better === true;
            const mapeSvrDefault = config.chartMetrics.mape_default || 0;
            const r2SvrDefault = config.chartMetrics.r2_default || 0;
            const mapeGridSearch = config.chartMetrics.mape_gs || 0;
            const r2GridSearch = config.chartMetrics.r2_gs || 0;

            if (window.Swal) {
                window.Swal.fire({
                    title: isBetter ? 'Optimasi Berhasil! 🎉' : 'Optimasi Selesai',
                    html: buildComparisonHtml({
                        is_better: isBetter,
                        old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                        new_params: data.new_params || { c: 200, epsilon: 0.001, gamma: 0.01, mape: mapeGridSearch, r2: r2GridSearch },
                    }),
                    icon: isBetter ? 'success' : 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Lihat Hasil',
                    cancelButtonText: 'Latih Ulang',
                    customClass: {
                        confirmButton: 'btn btn-primary px-4 py-2 me-2 rounded-3 fw-bold text-sm',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-bold text-sm'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    clearTempParams();
                    if (result.dismiss === 'cancel') {
                        isGridRunning = true;
                        if (window.goToGridStep) window.goToGridStep(3);
                        executeGridSearchTuning();
                    } else {
                        sessionStorage.setItem('optimasi_method', 'grid');
                        sessionStorage.setItem('grid_step', '4');
                        window.location.reload(); // reload agar tabel komparasi terupdate
                    }
                });
            } else {
                if (window.goToGridStep) window.goToGridStep(4);
            }
        }, 800);
    }
}
