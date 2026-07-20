import { config } from './config.js';

export function setPipeStatus(method, stepNum, status) {
    try {
        const el = document.getElementById(`${method}-pipe-${stepNum}`);
        if (!el) return;
        const icon = el.querySelector('.step-icon');
        if (!icon) return;
        el.classList.remove('active', 'success-step', 'failed-step');
        if (status === 'pending') {
            icon.innerHTML = '<i class="bi bi-circle"></i>';
            icon.className = 'step-icon me-2 text-muted';
        } else if (status === 'processing') {
            icon.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" style="width:12px;height:12px;border-width:1.5px;" role="status"></div>';
            icon.className = 'step-icon me-2';
            el.classList.add('active');
        } else if (status === 'success') {
            icon.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size:14px;"></i>';
            icon.className = 'step-icon me-2';
            el.classList.add('success-step');
        } else if (status === 'failed') {
            icon.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size:14px;"></i>';
            icon.className = 'step-icon me-2';
            el.classList.add('failed-step');
        }
    } catch (e) { console.error('setPipeStatus error:', e); }
}

export function formatNum(n, dec = 4) {
    return parseFloat(n).toFixed(dec);
}

export function buildComparisonHtml(data) {
    const op = data.old_params;
    const np = data.new_params;
    const isBetter = data.is_better;
    const mapeImprove = isBetter
        ? `<span class="text-success fw-bold">${formatNum(op.mape, 2)}% → ${formatNum(np.mape, 2)}%</span>`
        : `<span class="text-secondary">${formatNum(np.mape, 2)}% (tidak lebih baik dari ${formatNum(op.mape, 2)}%)</span>`;

    return `
        <div class="text-start" style="font-size:13px;">
            <table class="table table-sm table-bordered mb-2">
                <thead class="table-light"><tr><th>Parameter</th><th>Sebelumnya</th><th>Hasil Optimasi</th></tr></thead>
                <tbody>
                    <tr><td>C</td><td>${op.c}</td><td><strong>${formatNum(np.c, 4)}</strong></td></tr>
                    <tr><td>Epsilon</td><td>${op.epsilon}</td><td><strong>${formatNum(np.epsilon, 6)}</strong></td></tr>
                    <tr><td>Gamma</td><td>${op.gamma}</td><td><strong>${formatNum(np.gamma, 5)}</strong></td></tr>
                </tbody>
            </table>
            <p class="mb-1"><strong>MAPE:</strong> ${mapeImprove}</p>
            <p class="mb-0"><strong>Akurasi:</strong> ${formatNum(100 - np.mape, 2)}% &nbsp;|&nbsp; <strong>R²:</strong> ${formatNum(np.r2, 2)}</p>
            ${isBetter ? '<div class="alert alert-success mt-2 mb-0 py-2 px-3 text-sm">✅ Parameter baru telah disimpan sebagai model aktif di database.</div>'
            : '<div class="alert alert-secondary mt-2 mb-0 py-2 px-3 text-sm">ℹ️ Database tidak diperbarui — model tetap menggunakan parameter sebelumnya.</div>'}
        </div>`;
}
