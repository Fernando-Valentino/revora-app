import { config } from './config.js';

const gridFields = ['grid_c', 'grid_epsilon', 'grid_gamma'];
const gwoFields = ['gwo_wolves', 'gwo_iterations', 'c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];

export function unlockGridParams() {
    gridFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.removeAttribute('disabled');
            el.classList.add('param-input-editable');
        }
    });

    const checkbox = document.getElementById('auto_develop_grid');
    if (checkbox) {
        checkbox.removeAttribute('disabled');
    }

    toggleAutoGridDevelop();

    document.getElementById('btn-unlock-grid')?.classList.add('d-none');
    document.getElementById('btn-lock-grid')?.classList.remove('d-none');

    const alertEl = document.getElementById('grid-alert-info');
    if (alertEl) {
        alertEl.className = "alert alert-warning border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
    }
    const iconEl = document.getElementById('grid-alert-icon');
    if (iconEl) {
        iconEl.className = "bi bi-exclamation-triangle-fill fs-4 text-warning me-3";
    }
    const titleEl = document.getElementById('grid-alert-title');
    if (titleEl) titleEl.innerText = "Mode Edit Aktif";

    const descEl = document.getElementById('grid-alert-desc');
    if (descEl) descEl.innerText = "Anda sekarang dapat mengubah konfigurasi rentang nilai parameter Grid Search di bawah ini.";
}

export function lockGridParams() {
    gridFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.setAttribute('disabled', 'true');
            el.classList.remove('param-input-editable');
        }
    });

    const checkbox = document.getElementById('auto_develop_grid');
    if (checkbox) {
        checkbox.setAttribute('disabled', 'true');
    }

    initializeDefaultParams();
    loadTempParams();
    updateGridInfoText();

    document.getElementById('btn-unlock-grid')?.classList.remove('d-none');
    document.getElementById('btn-lock-grid')?.classList.add('d-none');

    const alertEl = document.getElementById('grid-alert-info');
    if (alertEl) {
        alertEl.className = "alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
    }
    const iconEl = document.getElementById('grid-alert-icon');
    if (iconEl) {
        iconEl.className = "bi bi-info-circle-fill fs-4 text-info me-3";
    }
    const titleEl = document.getElementById('grid-alert-title');
    if (titleEl) titleEl.innerText = "Grid Search Telah Dijalankan";

    const descEl = document.getElementById('grid-alert-desc');
    if (descEl) {
        const gsC = config.gsRun && config.gsRun.model_parameter ? config.gsRun.model_parameter.c_value : '200';
        const gsEps = config.gsRun && config.gsRun.model_parameter ? config.gsRun.model_parameter.epsilon_value : '0.001';
        const gsGam = config.gsRun && config.gsRun.model_parameter ? config.gsRun.model_parameter.gamma_value : '0.01';
        const gsMape = config.chartMetrics.mape_gs || '';
        descEl.innerHTML = `Model aktif saat ini menggunakan parameter optimal: <strong>C = ${gsC}</strong>, <strong>&epsilon; = ${gsEps}</strong>, <strong>&gamma; = ${gsGam}</strong> dengan nilai <strong>MAPE: ${gsMape}%</strong>.`;
    }
}

export function unlockGwoParams() {
    gwoFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.removeAttribute('disabled');
            el.classList.add('param-input-editable');
        }
    });

    const checkbox = document.getElementById('auto_develop_gwo');
    if (checkbox) {
        checkbox.removeAttribute('disabled');
    }

    toggleAutoGwoDevelop();

    document.getElementById('btn-unlock-gwo')?.classList.add('d-none');
    document.getElementById('btn-lock-gwo')?.classList.remove('d-none');

    const alertEl = document.getElementById('gwo-alert-info');
    if (alertEl) {
        alertEl.className = "alert alert-warning border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
    }
    const iconEl = document.getElementById('gwo-alert-icon');
    if (iconEl) {
        iconEl.className = "bi bi-exclamation-triangle-fill fs-4 text-warning me-3";
    }
    const titleEl = document.getElementById('gwo-alert-title');
    if (titleEl) titleEl.innerText = "Mode Edit Aktif";

    const descEl = document.getElementById('gwo-alert-desc');
    if (descEl) descEl.innerText = "Anda sekarang dapat mengubah konfigurasi parameter GWO dan rentang search space di bawah ini.";
}

export function lockGwoParams() {
    gwoFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.setAttribute('disabled', 'true');
            el.classList.remove('param-input-editable');
        }
    });

    const checkbox = document.getElementById('auto_develop_gwo');
    if (checkbox) {
        checkbox.setAttribute('disabled', 'true');
    }

    initializeDefaultParams();
    loadTempParams();

    document.getElementById('btn-unlock-gwo')?.classList.remove('d-none');
    document.getElementById('btn-lock-gwo')?.classList.add('d-none');

    const alertEl = document.getElementById('gwo-alert-info');
    if (alertEl) {
        alertEl.className = "alert alert-info border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between";
    }
    const iconEl = document.getElementById('gwo-alert-icon');
    if (iconEl) {
        iconEl.className = "bi bi-info-circle-fill fs-4 text-info me-3";
    }
    const titleEl = document.getElementById('gwo-alert-title');
    if (titleEl) titleEl.innerText = "Grey Wolf Optimizer (GWO) Telah Dijalankan";

    const descEl = document.getElementById('gwo-alert-desc');
    if (descEl) {
        const gwoC = config.gwoRun && config.gwoRun.model_parameter ? config.gwoRun.model_parameter.c_value : '250.0345';
        const gwoEps = config.gwoRun && config.gwoRun.model_parameter ? config.gwoRun.model_parameter.epsilon_value : '0.0053';
        const gwoGam = config.gwoRun && config.gwoRun.model_parameter ? config.gwoRun.model_parameter.gamma_value : '0.0044';
        const gwoMape = config.chartMetrics.mape_gwo || '';
        descEl.innerHTML = `Model aktif saat ini menggunakan parameter optimal: <strong>C = ${gwoC}</strong>, <strong>&epsilon; = ${gwoEps}</strong>, <strong>&gamma; = ${gwoGam}</strong> dengan nilai <strong>MAPE: ${gwoMape}%</strong>.`;
    }
}

export function saveTempParams() {
    gridFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) sessionStorage.setItem(`temp_${id}`, el.value);
    });
    gwoFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) sessionStorage.setItem(`temp_${id}`, el.value);
    });
    const gridCheck = document.getElementById('auto_develop_grid');
    if (gridCheck) sessionStorage.setItem('temp_auto_develop_grid', gridCheck.checked);
    const gwoCheck = document.getElementById('auto_develop_gwo');
    if (gwoCheck) sessionStorage.setItem('temp_auto_develop_gwo', gwoCheck.checked);
}

export function loadTempParams() {
    gridFields.forEach(id => {
        const val = sessionStorage.getItem(`temp_${id}`);
        const el = document.getElementById(id);
        if (val && el) el.value = val;
    });
    gwoFields.forEach(id => {
        const val = sessionStorage.getItem(`temp_${id}`);
        const el = document.getElementById(id);
        if (val && el) el.value = val;
    });
    const gridCheck = document.getElementById('auto_develop_grid');
    const gridCheckVal = sessionStorage.getItem('temp_auto_develop_grid');
    if (gridCheck && gridCheckVal) {
        gridCheck.checked = gridCheckVal === 'true';
    }
    const gwoCheck = document.getElementById('auto_develop_gwo');
    const gwoCheckVal = sessionStorage.getItem('temp_auto_develop_gwo');
    if (gwoCheck && gwoCheckVal) {
        gwoCheck.checked = gwoCheckVal === 'true';
    }
}

export function clearTempParams() {
    gridFields.forEach(id => sessionStorage.removeItem(`temp_${id}`));
    gwoFields.forEach(id => sessionStorage.removeItem(`temp_${id}`));
    sessionStorage.removeItem('temp_auto_develop_grid');
    sessionStorage.removeItem('temp_auto_develop_gwo');
}

export function toggleAutoGridDevelop() {
    const toggle = document.getElementById('auto_develop_grid');
    const isAuto = toggle && toggle.checked;
    const fields = ['grid_c', 'grid_epsilon', 'grid_gamma'];

    if (isAuto) {
        const bestC = config.bestParamsGs.c !== null ? parseFloat(config.bestParamsGs.c) : 100.0;
        const bestEps = config.bestParamsGs.epsilon !== null ? parseFloat(config.bestParamsGs.epsilon) : 0.001;
        const bestGam = config.bestParamsGs.gamma !== null ? config.bestParamsGs.gamma : 0.01;

        const cMinGrid = Math.max(1.0, bestC - 50.0);
        const cPrevGrid = Math.max(1.0, bestC - 10.0);
        let cRange = [
            parseFloat(cMinGrid.toFixed(4)),
            parseFloat(cPrevGrid.toFixed(4)),
            parseFloat(bestC.toFixed(4)),
            parseFloat((bestC + 10.0).toFixed(4)),
            parseFloat((bestC + 50.0).toFixed(4))
        ];
        cRange = [...new Set(cRange)].sort((a, b) => a - b);

        let epsRange = [
            parseFloat(Math.max(0.0001, bestEps / 2.0).toFixed(8)),
            parseFloat(bestEps.toFixed(8)),
            parseFloat(Math.min(0.1, bestEps * 2.0).toFixed(8)),
            parseFloat(Math.min(0.1, bestEps * 5.0).toFixed(8))
        ];
        epsRange = [...new Set(epsRange)].sort((a, b) => a - b);

        let gammaRange;
        if (bestGam === 'scale' || bestGam === 'auto') {
            gammaRange = ['scale', 0.001, 0.01, 0.05];
        } else {
            const gamNum = parseFloat(bestGam);
            gammaRange = [
                parseFloat(Math.max(0.0001, gamNum / 2.0).toFixed(6)),
                parseFloat(gamNum.toFixed(6)),
                parseFloat(Math.min(0.1, gamNum * 2.0).toFixed(6)),
                parseFloat(Math.min(0.1, gamNum * 5.0).toFixed(6))
            ];
            gammaRange = [...new Set(gammaRange)].sort((a, b) => a - b);
        }

        const cEl = document.getElementById('grid_c');
        if (cEl) cEl.value = JSON.stringify(cRange);
        const epsEl = document.getElementById('grid_epsilon');
        if (epsEl) epsEl.value = JSON.stringify(epsRange);
        const gamEl = document.getElementById('grid_gamma');
        if (gamEl) gamEl.value = JSON.stringify(gammaRange).replace(/"/g, "'");

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.setAttribute('readonly', 'true');
                el.classList.remove('param-input-editable');
            }
        });
    } else {
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.hasAttribute('disabled')) {
                el.removeAttribute('readonly');
                el.classList.add('param-input-editable');
            }
        });
    }

    updateGridInfoText();
}

export function toggleAutoGwoDevelop() {
    const toggle = document.getElementById('auto_develop_gwo');
    const isAuto = toggle && toggle.checked;
    const fields = ['c_min', 'c_max', 'epsilon_min', 'epsilon_max', 'gamma_min', 'gamma_max'];

    if (isAuto) {
        const bestC = config.bestParamsGwo.c !== null ? parseFloat(config.bestParamsGwo.c) : 250.034536;
        const bestEps = config.bestParamsGwo.epsilon !== null ? parseFloat(config.bestParamsGwo.epsilon) : 0.00536603;
        const bestGam = config.bestParamsGwo.gamma !== null ? parseFloat(config.bestParamsGwo.gamma) : 0.004455;

        const cMin = Math.max(1.0, bestC - 50.0);
        const cMax = bestC + 50.0;
        const epsMin = Math.max(0.0001, bestEps / 2.0);
        const epsMax = Math.min(0.1, bestEps * 2.0);
        const gamMin = Math.max(0.0001, bestGam / 2.0);
        const gamMax = Math.min(0.1, bestGam * 2.0);

        const cMinEl = document.getElementById('c_min');
        if (cMinEl) cMinEl.value = cMin.toFixed(6).replace(/\.?0+$/, '');
        const cMaxEl = document.getElementById('c_max');
        if (cMaxEl) cMaxEl.value = cMax.toFixed(6).replace(/\.?0+$/, '');
        const epsMinEl = document.getElementById('epsilon_min');
        if (epsMinEl) epsMinEl.value = epsMin.toFixed(8).replace(/\.?0+$/, '');
        const epsMaxEl = document.getElementById('epsilon_max');
        if (epsMaxEl) epsMaxEl.value = epsMax.toFixed(8).replace(/\.?0+$/, '');
        const gamMinEl = document.getElementById('gamma_min');
        if (gamMinEl) gamMinEl.value = gamMin.toFixed(6).replace(/\.?0+$/, '');
        const gamMaxEl = document.getElementById('gamma_max');
        if (gamMaxEl) gamMaxEl.value = gamMax.toFixed(6).replace(/\.?0+$/, '');

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.setAttribute('readonly', 'true');
                el.classList.remove('param-input-editable');
            }
        });
    } else {
        const cMinEl = document.getElementById('c_min');
        if (cMinEl) cMinEl.value = 10.0;
        const cMaxEl = document.getElementById('c_max');
        if (cMaxEl) cMaxEl.value = 300.0;
        const epsMinEl = document.getElementById('epsilon_min');
        if (epsMinEl) epsMinEl.value = 0.0001;
        const epsMaxEl = document.getElementById('epsilon_max');
        if (epsMaxEl) epsMaxEl.value = 0.05;
        const gamMinEl = document.getElementById('gamma_min');
        if (gamMinEl) gamMinEl.value = 0.0005;
        const gamMaxEl = document.getElementById('gamma_max');
        if (gamMaxEl) gamMaxEl.value = 0.1;

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.hasAttribute('disabled')) {
                el.removeAttribute('readonly');
                el.classList.add('param-input-editable');
            }
        });
    }
}

export function initializeDefaultParams() {
    const gridCheck = document.getElementById('auto_develop_grid');
    if (gridCheck && !sessionStorage.getItem('temp_auto_develop_grid')) {
        gridCheck.checked = true;
    } else if (gridCheck) {
        gridCheck.checked = sessionStorage.getItem('temp_auto_develop_grid') === 'true';
    }

    const gwoCheck = document.getElementById('auto_develop_gwo');
    if (gwoCheck && !sessionStorage.getItem('temp_auto_develop_gwo')) {
        gwoCheck.checked = true;
    } else if (gwoCheck) {
        gwoCheck.checked = sessionStorage.getItem('temp_auto_develop_gwo') === 'true';
    }

    if (gridCheck && gridCheck.checked) {
        toggleAutoGridDevelop();
    } else {
        const cRange = [10, 50, 100, 150, 200];
        const epsRange = [0.001, 0.005, 0.01, 0.05];
        const gammaRange = ['scale', 0.001, 0.01, 0.05];

        const cEl = document.getElementById('grid_c');
        if (cEl && !sessionStorage.getItem('temp_grid_c')) {
            cEl.value = JSON.stringify(cRange);
        }
        const epsEl = document.getElementById('grid_epsilon');
        if (epsEl && !sessionStorage.getItem('temp_grid_epsilon')) {
            epsEl.value = JSON.stringify(epsRange);
        }
        const gamEl = document.getElementById('grid_gamma');
        if (gamEl && !sessionStorage.getItem('temp_grid_gamma')) {
            gamEl.value = JSON.stringify(gammaRange).replace(/"/g, "'");
        }
    }

    if (gwoCheck && gwoCheck.checked) {
        toggleAutoGwoDevelop();
        const wolves = localStorage.getItem('gwo_wolves') || 15;
        const iterations = localStorage.getItem('gwo_iterations') || 30;
        const wEl = document.getElementById('gwo_wolves');
        if (wEl) wEl.value = wolves;
        const iEl = document.getElementById('gwo_iterations');
        if (iEl) iEl.value = iterations;
    } else {
        const wolves = localStorage.getItem('gwo_wolves') || 15;
        const iterations = localStorage.getItem('gwo_iterations') || 30;
        const cMin = 10.0;
        const cMax = 300.0;
        const epsMin = 0.0001;
        const epsMax = 0.05;
        const gamMin = 0.0005;
        const gamMax = 0.1;

        const wEl = document.getElementById('gwo_wolves');
        if (wEl && !sessionStorage.getItem('temp_gwo_wolves')) wEl.value = wolves;
        const iEl = document.getElementById('gwo_iterations');
        if (iEl && !sessionStorage.getItem('temp_gwo_iterations')) iEl.value = iterations;
        const cMinEl = document.getElementById('c_min');
        if (cMinEl && !sessionStorage.getItem('temp_c_min')) cMinEl.value = cMin;
        const cMaxEl = document.getElementById('c_max');
        if (cMaxEl && !sessionStorage.getItem('temp_c_max')) cMaxEl.value = cMax;
        const epsMinEl = document.getElementById('epsilon_min');
        if (epsMinEl && !sessionStorage.getItem('temp_epsilon_min')) epsMinEl.value = epsMin;
        const epsMaxEl = document.getElementById('epsilon_max');
        if (epsMaxEl && !sessionStorage.getItem('temp_epsilon_max')) epsMaxEl.value = epsMax;
        const gamMinEl = document.getElementById('gamma_min');
        if (gamMinEl && !sessionStorage.getItem('temp_gamma_min')) gamMinEl.value = gamMin;
        const gamMaxEl = document.getElementById('gamma_max');
        if (gamMaxEl && !sessionStorage.getItem('temp_gamma_max')) gamMaxEl.value = gamMax;
    }
}

export function updateGridInfoText() {
    let cLen = 0, epsLen = 0, gammaLen = 0;
    let cRange = [], epsRange = [], gammaRange = [];

    const cVal = document.getElementById('grid_c')?.value || '';
    const epsVal = document.getElementById('grid_epsilon')?.value || '';
    const gamVal = document.getElementById('grid_gamma')?.value || '';

    try {
        cRange = JSON.parse(cVal.replace(/'/g, '"'));
        cLen = cRange.length;
    } catch (e) { }
    try {
        epsRange = JSON.parse(epsVal.replace(/'/g, '"'));
        epsLen = epsRange.length;
    } catch (e) { }
    try {
        gammaRange = JSON.parse(gamVal.replace(/'/g, '"'));
        gammaLen = gammaRange.length;
    } catch (e) { }

    const helpC = document.getElementById('grid_c_help');
    const helpEps = document.getElementById('grid_epsilon_help');
    const helpGamma = document.getElementById('grid_gamma_help');
    const infoEl = document.getElementById('grid-info-text');

    if (helpC) helpC.innerText = `${cLen} nilai: ${cRange.join(', ')}`;
    if (helpEps) helpEps.innerText = `${epsLen} nilai: ${epsRange.join(', ')}`;
    if (helpGamma) helpGamma.innerText = `${gammaLen} nilai: ${gammaRange.map(g => typeof g === 'string' ? `'${g}'` : g).join(', ')}`;

    const totalCombinations = cLen * epsLen * gammaLen;
    const totalFits = totalCombinations * 5; // 5-Fold CV
    if (infoEl) {
        infoEl.innerHTML = `Grid Search akan menguji <strong>${totalCombinations} kombinasi</strong> (${cLen}&times;${epsLen}&times;${gammaLen}) parameter menggunakan <strong>5-Fold Cross Validation</strong> (total ${totalFits} fits). Metrik evaluasi: <strong>RMSE</strong>.`;
    }
}
