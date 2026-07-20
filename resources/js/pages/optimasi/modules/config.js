const configEl = document.getElementById('optimasi-config');
const parsedConfig = configEl ? JSON.parse(configEl.getAttribute('data-config') || '{}') : {};

export const config = {
    bestParamsGs: parsedConfig.bestParamsGs || { c: null, epsilon: null, gamma: null },
    bestParamsGwo: parsedConfig.bestParamsGwo || { c: null, epsilon: null, gamma: null },
    bestParamsDefault: parsedConfig.bestParamsDefault || { c: 1.0, epsilon: 0.1, gamma: 'scale' },
    chartMetrics: parsedConfig.chartMetrics || {},
    allDefaultPreds: parsedConfig.allDefaultPreds || [],
    allGsPreds: parsedConfig.allGsPreds || [],
    allGwoPreds: parsedConfig.allGwoPreds || [],
    rayonId: parsedConfig.rayonId || 0,
    gsRun: parsedConfig.gsRun || null,
    gwoRun: parsedConfig.gwoRun || null,
    lastRun: parsedConfig.lastRun || null,
    routes: parsedConfig.routes || {},
    csrfToken: parsedConfig.csrfToken || '',
    readonly: parsedConfig.readonly || false
};
