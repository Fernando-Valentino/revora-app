import { config } from './modules/config.js';
import { swalConfirm, swalLoading } from './modules/dialog-helper.js';
import {
    unlockGridParams,
    lockGridParams,
    unlockGwoParams,
    lockGwoParams,
    toggleAutoGridDevelop,
    toggleAutoGwoDevelop,
    saveTempParams,
    loadTempParams,
    initializeDefaultParams,
    updateGridInfoText
} from './modules/param-manager.js';
import {
    initializeAllCharts,
    updateGsChart,
    updateGwoChart,
    updateCompChart
} from './modules/charts-renderer.js';
import {
    startGridSearchTuning
} from './modules/grid-runner.js';
import {
    startGwoTuning
} from './modules/gwo-runner.js';
import {
    switchMethod,
    goToGridStep,
    goToGwoStep,
    retuneCurrentMethod,
    setGridStep,
    setGwoStep
} from './stepper-controller.js';

// Bind methods to window object for inline HTML event attributes
window.unlockGridParams = unlockGridParams;
window.lockGridParams = lockGridParams;
window.unlockGwoParams = unlockGwoParams;
window.lockGwoParams = lockGwoParams;
window.toggleAutoGridDevelop = toggleAutoGridDevelop;
window.toggleAutoGwoDevelop = toggleAutoGwoDevelop;
window.switchMethod = switchMethod;
window.goToGridStep = goToGridStep;
window.goToGwoStep = goToGwoStep;
window.retuneCurrentMethod = retuneCurrentMethod;
window.startGridSearchTuning = startGridSearchTuning;
window.startGwoTuning = startGwoTuning;
window.updateGsChart = updateGsChart;
window.updateGwoChart = updateGwoChart;
window.updateCompChart = updateCompChart;

window.confirmDeleteOptimasiRun = function (runId, startedAt, modelName) {
    swalConfirm(
        'Hapus Riwayat Pelatihan?',
        `Riwayat pelatihan ${modelName} tanggal ${startedAt} beserta hasil prediksinya akan dihapus secara permanen!`,
        'Ya, Hapus!',
        function () {
            swalLoading('Menghapus Riwayat...', 'Mohon tunggu sebentar.');
            const idInput = document.getElementById('delete-run-id');
            const deleteForm = document.getElementById('delete-optimasi-run-form');
            if (idInput && deleteForm) {
                idInput.value = runId;
                deleteForm.submit();
            }
        }
    );
};

window.confirmResetOptimasiAll = function (target) {
    const modelName = target === 'grid_search' ? 'Grid Search' : 'Grey Wolf Optimizer (GWO)';
    const formId = target === 'grid_search' ? 'reset-grid-form' : 'reset-gwo-form';

    swalConfirm(
        'Reset Semua Riwayat?',
        `Seluruh riwayat proses optimasi ${modelName} dan hasil prediksi terkait akan dihapus secara permanen dari database!`,
        'Ya, Reset Semua!',
        function () {
            swalLoading('Memproses Reset...', 'Mohon tunggu sebentar.');
            const resetForm = document.getElementById(formId);
            if (resetForm) {
                resetForm.submit();
            }
        }
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const savedMethod = urlParams.get('method') || sessionStorage.getItem('optimasi_method') || 'grid';

    // Check if models are already trained in database to set default step to 4 (Results)
    const isGridTrained = config.gsRun !== null;
    const isGwoTrained = config.gwoRun !== null;

    const defaultGridStep = isGridTrained ? 4 : 1;
    const defaultGwoStep = isGwoTrained ? 4 : 1;

    // Step 3 is the loading/in-progress screen — never restore it on fresh page load
    // because there is no active process running. Clamp to step 2 if saved as 3.
    const rawGridStep = parseInt(urlParams.get('grid_step') || sessionStorage.getItem('grid_step') || defaultGridStep);
    const rawGwoStep = parseInt(urlParams.get('gwo_step') || sessionStorage.getItem('gwo_step') || defaultGwoStep);
    
    let savedGridStep = rawGridStep === 3 ? 2 : rawGridStep;
    if ((savedGridStep === 4 || savedGridStep === 5) && !isGridTrained) {
        savedGridStep = 1;
    }
    let savedGwoStep = rawGwoStep === 3 ? 2 : rawGwoStep;
    if ((savedGwoStep === 4 || savedGwoStep === 5) && !isGwoTrained) {
        savedGwoStep = 1;
    }

    setGridStep(savedGridStep);
    setGwoStep(savedGwoStep);

    initializeDefaultParams();
    loadTempParams();
    updateGridInfoText();

    // Listen for input changes to temporarily save parameters and update info text
    const gridFieldsList = ['grid_c', 'grid_epsilon', 'grid_gamma'];
    const gwoFieldsList = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];
    [...gridFieldsList, ...gwoFieldsList].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                saveTempParams();
                if (id === 'gwo_wolves' || id === 'gwo_iterations') {
                    localStorage.setItem(id, el.value);
                }
                if (gridFieldsList.includes(id)) {
                    updateGridInfoText();
                }
            });
        }
    });

    window.switchMethod(savedMethod);
    initializeAllCharts();
});
