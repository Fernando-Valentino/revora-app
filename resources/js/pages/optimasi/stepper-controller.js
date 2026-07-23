import { config } from './modules/config.js';
import { swalAlertWarning } from './modules/dialog-helper.js';
import { isGridRunning } from './modules/grid-runner.js';
import { isGwoRunning } from './modules/gwo-runner.js';
import { setPipeStatus } from './modules/runner-helper.js';
import { unlockGridParams, lockGridParams, unlockGwoParams, lockGwoParams } from './modules/param-manager.js';

export let gridStep = 1;
export let gwoStep = 1;
export let currentMethod = 'grid';

export function setGridStep(step) {
    gridStep = step;
}

export function setGwoStep(step) {
    gwoStep = step;
}

export function switchMethod(method) {
    currentMethod = method;
    sessionStorage.setItem('optimasi_method', method);
    const btnGrid = document.getElementById('tab-btn-grid');
    const btnGwo = document.getElementById('tab-btn-gwo');
    const contentGrid = document.getElementById('method-content-grid');
    const contentGwo = document.getElementById('method-content-gwo');

    if (method === 'grid') {
        if (btnGrid) { btnGrid.classList.add('btn-active-tab'); btnGrid.classList.remove('text-secondary'); }
        if (btnGwo) { btnGwo.classList.remove('btn-active-tab'); btnGwo.classList.add('text-secondary'); }
        if (contentGrid) contentGrid.classList.remove('d-none');
        if (contentGwo) contentGwo.classList.add('d-none');

        document.getElementById('grid-evaluation-details')?.classList.remove('d-none');
        document.getElementById('gwo-evaluation-details')?.classList.add('d-none');

        goToGridStep(gridStep);
    } else {
        if (btnGwo) { btnGwo.classList.add('btn-active-tab'); btnGwo.classList.remove('text-secondary'); }
        if (btnGrid) { btnGrid.classList.remove('btn-active-tab'); btnGrid.classList.add('text-secondary'); }
        if (contentGwo) contentGwo.classList.remove('d-none');
        if (contentGrid) contentGrid.classList.add('d-none');

        document.getElementById('grid-evaluation-details')?.classList.add('d-none');
        document.getElementById('gwo-evaluation-details')?.classList.remove('d-none');

        goToGwoStep(gwoStep);
    }
}

export function goToGridStep(stepNum) {
    const isGridTrained = config.gsRun !== null;

    if (stepNum === 3 && !isGridRunning && !isGridTrained) {
        swalAlertWarning('Akses Terkunci!', 'Langkah 3 (Proses Tuning) hanya dapat diakses saat proses optimasi Grid Search sedang berjalan atau telah selesai dilatih.');
        return;
    }

    if ((stepNum === 4 || stepNum === 5) && !isGridTrained && (!config.bestParamsGs || !config.bestParamsGs.c)) {
        swalAlertWarning('Hasil Belum Tersedia!', 'Silakan jalankan proses optimasi Grid Search terlebih dahulu pada Langkah 2 untuk melihat hasil.');
        return;
    }

    gridStep = stepNum;
    sessionStorage.setItem('grid_step', stepNum.toString());

    for (let i = 1; i <= 6; i++) {
        const item = document.getElementById(`stepper-grid-${i}`);
        if (item) {
            item.classList.remove('active', 'completed');
            if (i < stepNum) item.classList.add('completed');
            else if (i === stepNum) item.classList.add('active');
        }
    }
    for (let i = 1; i <= 4; i++) {
        const line = document.getElementById(`stepper-line-grid-${i}`);
        if (line) {
            line.classList.remove('completed');
            if (i < stepNum) line.classList.add('completed');
        }
    }
    const s1 = document.getElementById('grid-step-content-1');
    const s2 = document.getElementById('grid-step-content-2');
    const s3 = document.getElementById('grid-step-content-3');
    const s4 = document.getElementById('grid-step-content-4');
    const s5 = document.getElementById('results-step-content-5');

    if (s1) s1.classList.add('d-none');
    if (s2) s2.classList.add('d-none');
    if (s3) s3.classList.add('d-none');
    if (s4) s4.classList.add('d-none');
    if (s5) s5.classList.add('d-none');

    if (stepNum === 4) {
        if (s4) s4.classList.remove('d-none');
        setTimeout(() => { s4?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    } else if (stepNum === 5) {
        if (s5) s5.classList.remove('d-none');
        setTimeout(() => { s5?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    } else {
        const target = document.getElementById(`grid-step-content-${stepNum}`);
        if (target) target.classList.remove('d-none');

        if (stepNum === 3) {
            const spinnerContainer = document.getElementById('grid-spinner-container');
            const successContainer = document.getElementById('grid-success-container');
            const timerBox = document.getElementById('grid-timer-box');

            if (isGridRunning) {
                if (spinnerContainer) spinnerContainer.classList.remove('d-none');
                if (successContainer) successContainer.classList.add('d-none');
                if (timerBox) timerBox.classList.remove('d-none');
            } else if (config.bestParamsGs.c !== null) {
                if (spinnerContainer) spinnerContainer.classList.add('d-none');
                if (successContainer) successContainer.classList.remove('d-none');
                if (timerBox) timerBox.classList.add('d-none');

                let cLen = 5, epsLen = 4, gammaLen = 4;
                try {
                    const rawC = document.getElementById('grid_c')?.value || '';
                    const rawEps = document.getElementById('grid_epsilon')?.value || '';
                    const rawGamma = document.getElementById('grid_gamma')?.value || '';
                    if (rawC.startsWith('[')) cLen = JSON.parse(rawC.replace(/'/g, '"')).length;
                    if (rawEps.startsWith('[')) epsLen = JSON.parse(rawEps.replace(/'/g, '"')).length;
                    if (rawGamma.startsWith('[')) gammaLen = JSON.parse(rawGamma.replace(/'/g, '"')).length;
                } catch (e) { }
                let maxCombos = cLen * epsLen * gammaLen;

                const progressBar = document.getElementById('grid-progress-bar');
                const gridLabel = document.getElementById('grid-iter-label');
                const gridPct = document.getElementById('grid-iter-pct');
                if (progressBar) {
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', '100');
                    progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                }
                if (gridLabel) gridLabel.innerText = `Kombinasi Grid: Selesai (${maxCombos} / ${maxCombos})`;
                if (gridPct) gridPct.innerText = '100%';

                for (let i = 1; i <= 6; i++) {
                    setPipeStatus('grid', i, 'success');
                }
                const titleEl = document.getElementById('grid-process-title');
                const descEl = document.getElementById('grid-process-desc');
                if (titleEl) titleEl.innerText = "Optimasi Grid Search Selesai!";
                if (descEl) descEl.innerText = "Seluruh langkah tuning parameter telah berhasil diselesaikan sebelumnya.";
            } else {
                if (spinnerContainer) spinnerContainer.classList.add('d-none');
                if (successContainer) successContainer.classList.add('d-none');
                if (timerBox) timerBox.classList.add('d-none');
                for (let i = 1; i <= 6; i++) {
                    setPipeStatus('grid', i, 'pending');
                }
                const titleEl = document.getElementById('grid-process-title');
                const descEl = document.getElementById('grid-process-desc');
                if (titleEl) titleEl.innerText = "Grid Search Belum Dijalankan";
                if (descEl) descEl.innerText = "Silakan kembali ke Langkah 2 untuk mengonfigurasi dan menjalankan Grid Search.";
            }
        }
    }

    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        setTimeout(() => {
            if ($('#comparisonTable').length && $.fn.DataTable.isDataTable('#comparisonTable')) {
                $('#comparisonTable').DataTable().columns.adjust().draw();
            }
            $('.result-datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().columns.adjust().draw();
                }
            });
            $('.history-datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().columns.adjust().draw();
                }
            });
        }, 150);
    }
}

export function goToGwoStep(stepNum) {
    const isGwoTrained = config.gwoRun !== null;

    if (stepNum === 3 && !isGwoRunning && !isGwoTrained) {
        swalAlertWarning('Akses Terkunci!', 'Langkah 3 (Proses Tuning) hanya dapat diakses saat proses optimasi GWO sedang berjalan atau telah selesai dilatih.');
        return;
    }

    if ((stepNum === 4 || stepNum === 5) && !isGwoTrained && (!config.bestParamsGwo || !config.bestParamsGwo.c)) {
        swalAlertWarning('Hasil Belum Tersedia!', 'Silakan jalankan proses optimasi GWO terlebih dahulu pada Langkah 2 untuk melihat hasil.');
        return;
    }

    gwoStep = stepNum;
    sessionStorage.setItem('gwo_step', stepNum.toString());

    for (let i = 1; i <= 6; i++) {
        const item = document.getElementById(`stepper-gwo-${i}`);
        if (item) {
            item.classList.remove('active', 'completed');
            if (i < stepNum) item.classList.add('completed');
            else if (i === stepNum) item.classList.add('active');
        }
    }
    for (let i = 1; i <= 4; i++) {
        const line = document.getElementById(`stepper-line-gwo-${i}`);
        if (line) {
            line.classList.remove('completed');
            if (i < stepNum) line.classList.add('completed');
        }
    }
    const s1 = document.getElementById('gwo-step-content-1');
    const s2 = document.getElementById('gwo-step-content-2');
    const s3 = document.getElementById('gwo-step-content-3');
    const s4 = document.getElementById('gwo-step-content-4');
    const s5 = document.getElementById('results-step-content-5');

    if (s1) s1.classList.add('d-none');
    if (s2) s2.classList.add('d-none');
    if (s3) s3.classList.add('d-none');
    if (s4) s4.classList.add('d-none');
    if (s5) s5.classList.add('d-none');

    if (stepNum === 4) {
        if (s4) s4.classList.remove('d-none');
        setTimeout(() => { s4?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    } else if (stepNum === 5) {
        if (s5) s5.classList.remove('d-none');
        setTimeout(() => { s5?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    } else {
        const target = document.getElementById(`gwo-step-content-${stepNum}`);
        if (target) target.classList.remove('d-none');

        if (stepNum === 3) {
            const spinnerContainer = document.getElementById('gwo-spinner-container');
            const successContainer = document.getElementById('gwo-success-container');
            const timerBox = document.getElementById('gwo-timer-box');
            const progressBarContainer = document.getElementById('gwo-progress-bar-container');

            if (isGwoRunning) {
                if (spinnerContainer) spinnerContainer.classList.remove('d-none');
                if (successContainer) successContainer.classList.add('d-none');
                if (timerBox) timerBox.classList.remove('d-none');
                if (progressBarContainer) progressBarContainer.classList.remove('d-none');
            } else if (config.bestParamsGwo.c !== null) {
                if (spinnerContainer) spinnerContainer.classList.add('d-none');
                if (successContainer) successContainer.classList.remove('d-none');
                if (timerBox) timerBox.classList.add('d-none');
                if (progressBarContainer) progressBarContainer.classList.remove('d-none');

                const maxIters = parseInt(document.getElementById('gwo_iterations')?.value) || 30;
                const progressBar = document.getElementById('gwo-progress-bar');
                const iterLabel = document.getElementById('gwo-iter-label');
                const iterPct = document.getElementById('gwo-iter-pct');
                if (progressBar) {
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', '100');
                    progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                }
                if (iterLabel) iterLabel.innerText = `Iterasi GWO: Selesai (${maxIters} / ${maxIters})`;
                if (iterPct) iterPct.innerText = '100%';

                for (let i = 1; i <= 6; i++) {
                    setPipeStatus('gwo', i, 'success');
                }
                const titleEl = document.getElementById('gwo-process-title');
                const descEl = document.getElementById('gwo-process-desc');
                if (titleEl) titleEl.innerText = "Optimasi GWO Selesai!";
                if (descEl) descEl.innerText = "Seluruh langkah pencarian parameter global optimal telah berhasil diselesaikan sebelumnya.";
            } else {
                if (spinnerContainer) spinnerContainer.classList.add('d-none');
                if (successContainer) successContainer.classList.add('d-none');
                if (timerBox) timerBox.classList.add('d-none');
                if (progressBarContainer) progressBarContainer.classList.add('d-none');
                for (let i = 1; i <= 6; i++) {
                    setPipeStatus('gwo', i, 'pending');
                }
                const titleEl = document.getElementById('gwo-process-title');
                const descEl = document.getElementById('gwo-process-desc');
                if (titleEl) titleEl.innerText = "GWO Belum Dijalankan";
                if (descEl) descEl.innerText = "Silakan kembali ke Langkah 2 untuk mengonfigurasi dan menjalankan Grey Wolf Optimizer.";
            }
        }
    }

    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        setTimeout(() => {
            if ($('#comparisonTable').length && $.fn.DataTable.isDataTable('#comparisonTable')) {
                $('#comparisonTable').DataTable().columns.adjust().draw();
            }
            $('.result-datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().columns.adjust().draw();
                }
            });
            $('.history-datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().columns.adjust().draw();
                }
            });
        }, 150);
    }
}

export function retuneCurrentMethod() {
    console.log("Retune triggered for method:", currentMethod);
    if (currentMethod === 'grid') {
        unlockGridParams();
        goToGridStep(1);
    } else {
        unlockGwoParams();
        goToGwoStep(1);
    }
}
