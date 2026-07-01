file_path = 'd:/KULIAH/Semester 8/MODEL_SVR/web-app/resources/views/operator/optimasi/index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content_lf = content.replace('\r\n', '\n')

# Remove the converged banner logic and restore clean GWO modal
gwo_target = """            setTimeout(() => {
                const data = gwoApiData || {};
                const isBetter = data.is_better === true;
                const isConverged = data.converged === true;
                if (typeof Swal !== 'undefined') {
                    // Build converged banner (injected above comparison table)
                    const convergedBanner = isConverged
                        ? `<div style="margin:-4px 0 14px;padding:10px 14px;background:linear-gradient(135deg,#fef3c7,#fde68a);border:1.5px solid #f59e0b;border-radius:10px;display:flex;align-items:flex-start;gap:10px;text-align:left;">
                            <span style="font-size:1.4rem;line-height:1;">🏁</span>
                            <div>
                              <div style="font-weight:700;color:#92400e;font-size:0.85rem;margin-bottom:2px;">GWO Mencapai Titik Konvergensi</div>
                              <div style="font-size:0.78rem;color:#78350f;line-height:1.4;">Algoritma berhenti lebih awal setelah <strong>8 iterasi berturut-turut tanpa perbaikan</strong>. Parameter yang ditampilkan adalah yang <strong>terbaik yang dapat ditemukan</strong> dalam ruang pencarian saat ini. Untuk kemungkinan hasil lebih baik, coba perluas batas search space atau tambah jumlah wolf.</div>
                            </div>
                          </div>`
                        : '';
                    const title = isConverged
                        ? 'GWO Konvergen — Hasil Terbaik! 🏁'
                        : (isBetter ? 'Optimasi GWO Berhasil! 🐺' : 'Optimasi GWO Selesai');
                    Swal.fire({
                         title: title,
                         html: convergedBanner + buildComparisonHtml({
                             is_better: isBetter,
                             old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                             new_params: data.new_params || { c: 250.034536, epsilon: 0.00536603, gamma: 0.0044554, mape: mapeGwo, r2: r2Gwo },
                         }),
                         icon: isConverged ? 'warning' : (isBetter ? 'success' : 'info'),
                         showCancelButton: true,
                         confirmButtonColor: '#005BAA',
                         cancelButtonColor: '#6B7280',
                         confirmButtonText: 'Lihat Hasil',
                         cancelButtonText: 'Latih Ulang'
                     }).then((result) => {
                         clearTempParams();
                         if (result.dismiss === 'cancel') {
                             window.goToGwoStep(3);
                             executeGwoTuning();
                         } else {
                             sessionStorage.setItem('optimasi_method', 'gwo');
                             sessionStorage.setItem('gwo_step', '4');
                             window.location.reload();
                         }
                     });"""

gwo_replacement = """            setTimeout(() => {
                const data = gwoApiData || {};
                const isBetter = data.is_better === true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                         title: isBetter ? 'Optimasi GWO Berhasil! 🐺' : 'Optimasi GWO Selesai',
                         html: buildComparisonHtml({
                             is_better: isBetter,
                             old_params: data.old_params || { c: '1.0', epsilon: '0.1', gamma: 'scale', mape: mapeSvrDefault, r2: r2SvrDefault },
                             new_params: data.new_params || { c: 250.034536, epsilon: 0.00536603, gamma: 0.0044554, mape: mapeGwo, r2: r2Gwo },
                         }),
                         icon: isBetter ? 'success' : 'info',
                         showCancelButton: true,
                         confirmButtonColor: '#005BAA',
                         cancelButtonColor: '#6B7280',
                         confirmButtonText: 'Lihat Hasil',
                         cancelButtonText: 'Latih Ulang'
                     }).then((result) => {
                         clearTempParams();
                         if (result.dismiss === 'cancel') {
                             window.goToGwoStep(3);
                             executeGwoTuning();
                         } else {
                             sessionStorage.setItem('optimasi_method', 'gwo');
                             sessionStorage.setItem('gwo_step', '4');
                             window.location.reload();
                         }
                     });"""

updated = False
if gwo_target in content_lf:
    content_lf = content_lf.replace(gwo_target, gwo_replacement)
    updated = True
    print("GWO SweetAlert converged banner removed.")
else:
    print("WARNING: GWO SweetAlert target not found.")

if updated:
    if '\r\n' in content:
        content_lf = content_lf.replace('\n', '\r\n')
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content_lf)
    print("Update successful!")
else:
    print("No updates applied.")
