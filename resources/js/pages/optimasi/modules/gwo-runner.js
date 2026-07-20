import { config } from './config.js';
import { swalConfirmPrimary, swalError } from './dialog-helper.js';
import { setPipeStatus, buildComparisonHtml } from './runner-helper.js';
import { clearTempParams } from './param-manager.js';

export let isGwoRunning = false;
let gwoApiFinished = false;
let gwoApiError = null;
let gwoApiData = null;
let gwoCurrentStep = 1;
let gwoTimeout = null;
let gwoIterInterval = null;
let gwoWaitCount = 0;
const GWO_MAX_WAIT = 9000; // 9000 * 200ms = 30 minutes max wait

let elapsedTimerInterval = null;
let elapsedSeconds = 0;

export function startGwoTuning() {
    swalConfirmPrimary(
        'Jalankan GWO?',
        'Sistem akan memulai pencarian parameter optimal SVR dengan Grey Wolf Optimizer. Harap tunggu hingga selesai.',
        'Ya, Jalankan!',
        function() {
            isGwoRunning = true;
            if (window.goToGwoStep) window.goToGwoStep(3);
            executeGwoTuning();
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

    const gwoProgressBar = document.getElementById('gwo-progress-bar');
    const gwoIterLabel = document.getElementById('gwo-iter-label');
    const gwoIterPct = document.getElementById('gwo-iter-pct');
    const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 30;

    elapsedTimerInterval = setInterval(() => {
        elapsedSeconds++;
        if (elapsedEl) elapsedEl.innerText = `${elapsedSeconds}s`;

        if (method === 'gwo') {
            let currentIter = Math.min(maxIters - 1, Math.floor((elapsedSeconds / estimatedSeconds) * maxIters));
            let pct = Math.min(95, Math.round((elapsedSeconds / estimatedSeconds) * 95));

            if (gwoProgressBar) {
                gwoProgressBar.style.width = pct + '%';
                gwoProgressBar.setAttribute('aria-valuenow', pct);
            }
            if (gwoIterLabel) gwoIterLabel.innerText = `Iterasi GWO: ${currentIter} / ${maxIters}`;
            if (gwoIterPct) gwoIterPct.innerText = pct + '%';
        }
    }, 1000);
}

function stopElapsedTimer() {
    if (elapsedTimerInterval) {
        clearInterval(elapsedTimerInterval);
        elapsedTimerInterval = null;
    }
}

function executeGwoTuning() {
    isGwoRunning = true;

    // Reset UI containers for running state
    const spinnerContainer = document.getElementById('gwo-spinner-container');
    const successContainer = document.getElementById('gwo-success-container');
    const timerBox = document.getElementById('gwo-timer-box');
    const progressBarContainer = document.getElementById('gwo-progress-bar-container');
    if (spinnerContainer) spinnerContainer.classList.remove('d-none');
    if (successContainer) successContainer.classList.add('d-none');
    if (timerBox) timerBox.classList.remove('d-none');
    if (progressBarContainer) progressBarContainer.classList.remove('d-none');

    gwoApiFinished = false;
    gwoApiError = null;
    gwoApiData = null;
    gwoCurrentStep = 1;
    gwoWaitCount = 0;
    if (gwoTimeout) clearTimeout(gwoTimeout);
    if (gwoIterInterval) clearInterval(gwoIterInterval);

    for (let i = 1; i <= 6; i++) setPipeStatus('gwo', i, 'pending');

    const wolves = parseInt(document.getElementById('gwo_wolves')?.value) || 15;
    const iterations = parseInt(document.getElementById('gwo_iterations')?.value) || 30;

    const progressBar = document.getElementById('gwo-progress-bar');
    const iterLabel = document.getElementById('gwo-iter-label');
    const iterPct = document.getElementById('gwo-iter-pct');
    if (progressBar) { progressBar.style.width = '0%'; progressBar.setAttribute('aria-valuenow', '0'); }
    if (iterLabel) iterLabel.innerText = "Iterasi GWO: 0 / " + iterations;
    if (iterPct) iterPct.innerText = "0%";

    const titleEl = document.getElementById('gwo-process-title');
    const descEl = document.getElementById('gwo-process-desc');
    if (titleEl) titleEl.innerText = "Sedang Menyiapkan GWO...";
    if (descEl) descEl.innerText = "Algoritma Grey Wolf Optimizer sedang diinisialisasi.";

    setPipeStatus('gwo', 1, 'processing');
    gwoTimeout = setTimeout(runGwoStepSequence, 800);

    // Calculate estimated seconds
    const totalFits = wolves * iterations * 5;
    const estimatedSeconds = Math.max(10, Math.ceil(totalFits * 0.12));
    startElapsedTimer('gwo', estimatedSeconds);

    // Collect form params
    const formData = {
        wolves: wolves,
        iterations: iterations,
        c_min: document.getElementById('c_min')?.value || 10.0,
        c_max: document.getElementById('c_max')?.value || 300.0,
        epsilon_min: document.getElementById('epsilon_min')?.value || 0.0001,
        epsilon_max: document.getElementById('epsilon_max')?.value || 0.05,
        gamma_min: document.getElementById('gamma_min')?.value || 0.0005,
        gamma_max: document.getElementById('gamma_max')?.value || 0.1,
    };

    const targetUrl = config.routes.gwo || "/operator/optimasi/gwo";

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
            console.log("GWO response:", data);
            gwoApiFinished = true;
            gwoApiData = data;
        })
        .catch(err => {
            console.error("GWO error:", err);
            gwoApiFinished = true;
            gwoApiError = err;
        });
}

function runGwoStepSequence() {
    const titleEl = document.getElementById('gwo-process-title');
    const descEl = document.getElementById('gwo-process-desc');

    if (gwoApiError) {
        isGwoRunning = false;
        stopElapsedTimer();
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById(`gwo-pipe-${i}`);
            if (el && el.classList.contains('active')) setPipeStatus('gwo', i, 'failed');
        }
        swalError('Gagal!', gwoApiError.message || 'Optimasi GWO gagal.');
        if (window.goToGwoStep) window.goToGwoStep(2);
        return;
    }

    if (gwoCurrentStep === 1) {
        setPipeStatus('gwo', 1, 'success');
        gwoCurrentStep = 2;
        setPipeStatus('gwo', 2, 'processing');
        if (titleEl) titleEl.innerText = "Menghitung Fitness Awal...";
        if (descEl) descEl.innerText = "Evaluasi kebugaran (fitness) posisi serigala awal di search space.";
        gwoTimeout = setTimeout(runGwoStepSequence, 1000);
    } else if (gwoCurrentStep === 2) {
        setPipeStatus('gwo', 2, 'success');
        gwoCurrentStep = 3;
        setPipeStatus('gwo', 3, 'processing');
        if (titleEl) titleEl.innerText = "Iterasi GWO & Perburuan...";
        if (descEl) descEl.innerText = "Serigala Alpha, Beta, dan Delta sedang memimpin perburuan parameter optimal.";
        gwoTimeout = setTimeout(runGwoStepSequence, 500); // start checking API status
    } else if (gwoCurrentStep === 3) {
        if (gwoApiFinished) {
            // Complete progress bar immediately
            const progressBar = document.getElementById('gwo-progress-bar');
            const iterLabel = document.getElementById('gwo-iter-label');
            const iterPct = document.getElementById('gwo-iter-pct');
            const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 30;
            if (progressBar) { progressBar.style.width = '100%'; progressBar.setAttribute('aria-valuenow', '100'); }
            if (iterLabel) iterLabel.innerText = `Iterasi GWO: Selesai (${maxIters} / ${maxIters})`;
            if (iterPct) iterPct.innerText = '100%';

            setPipeStatus('gwo', 3, 'success');
            gwoCurrentStep = 4;
            setPipeStatus('gwo', 4, 'processing');
            if (titleEl) titleEl.innerText = "Memperbarui Model Parameter Optimal...";
            if (descEl) descEl.innerText = "Menyimpan nilai C, Epsilon, dan Gamma terbaik hasil perburuan GWO.";
            gwoTimeout = setTimeout(runGwoStepSequence, 1200);
        } else {
            // Safety cutoff check
            gwoWaitCount++;
            if (gwoWaitCount >= GWO_MAX_WAIT) {
                gwoApiFinished = true;
                gwoApiError = { message: 'Waktu tunggu GWO habis (timeout). Server membutuhkan waktu lebih lama untuk memproses.' };
            }
            gwoTimeout = setTimeout(runGwoStepSequence, 200);
        }
    } else if (gwoCurrentStep === 4) {
        isGwoRunning = false;
        stopElapsedTimer();
        setPipeStatus('gwo', 6, 'success');
        setPipeStatus('gwo', 4, 'success');
        if (titleEl) titleEl.innerText = "Optimasi GWO Selesai!";
        if (descEl) descEl.innerText = "Posisi parameter global optimal berhasil ditemukan.";

        setTimeout(() => {
            const data = gwoApiData || {};
            const isBetter = data.is_better === true;
            const mapeSvrDefault = config.chartMetrics.mape_default || 0;
            const r2SvrDefault = config.chartMetrics.r2_default || 0;
            const mapeGwo = config.chartMetrics.mape_gwo || 0;
            const r2Gwo = config.chartMetrics.r2_gwo || 0;

            if (window.Swal) {
                window.Swal.fire({
                    title: isBetter ? 'Optimasi GWO Berhasil! 🐺' : 'Optimasi GWO Selesai',
                    html: buildComparisonHtml({
                        is_better: isBetter,
                        old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                        new_params: data.new_params || { c: 250.034536, epsilon: 0.00536603, gamma: 0.004455, mape: mapeGwo, r2: r2Gwo },
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
                        isGwoRunning = true;
                        if (window.goToGwoStep) window.goToGwoStep(3);
                        executeGwoTuning();
                    } else {
                        sessionStorage.setItem('optimasi_method', 'gwo');
                        sessionStorage.setItem('gwo_step', '4');
                        window.location.reload();
                    }
                });
            } else {
                if (window.goToGwoStep) window.goToGwoStep(4);
            }
        }, 800);
    }
}
