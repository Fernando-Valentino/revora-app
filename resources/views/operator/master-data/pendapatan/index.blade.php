@extends('layouts.app')

@section('title', 'Data Pendapatan')
@section('subtitle', 'Halaman ini digunakan untuk mengelola data pendapatan retribusi parkir harian.')

@section('content')
<div class="container-fluid p-0">
    {{-- Skeleton Placeholder --}}
    <div class="sk-wrapper">
        <x-ui.skeleton type="table" :rows="6" />
    </div>

    {{-- Real Content --}}
    <div class="sk-content">
    <div class="card">
        <div class="card-body">
            {{-- Toolbar --}}
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">Tanggal:</span>
                        <div style="width: 155px;">
                            <input type="date" class="form-control" id="filter_start_date" name="start_date" style="height: 38px; font-size: 13px; border-radius: 6px;">
                        </div>
                        <span style="font-size: 13px; color: var(--text-secondary);">s/d</span>
                        <div style="width: 155px;">
                            <input type="date" class="form-control" id="filter_end_date" name="end_date" style="height: 38px; font-size: 13px; border-radius: 6px;">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary fw-semibold" id="btnFilterTanggal" style="height: 38px; font-size: 13px; padding: 0 16px;">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <button type="button" class="btn btn-outline-secondary bg-white text-dark" id="btnResetTanggal" style="height: 38px; width: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise" style="font-size: 15px;"></i>
                    </button>
                </div>
                <div class="d-inline-flex gap-2">
                    <button type="button" class="btn btn-outline-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#importPendapatanModal" style="height: 38px; font-size: 13px;">
                        <i class="bi bi-file-earmark-text"></i> Import CSV
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#addPendapatanModal" style="height: 38px; font-size: 13px;">
                        <i class="bi bi-plus-lg"></i> Tambah Data
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pendapatanTable">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No</th>
                            <th>Tanggal</th>
                            <th>Rayon</th>
                            <th>Jumlah Juru Parkir</th>
                            <th style="text-align: right;">Jumlah Pendapatan</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<x-master-data.pendapatan.modals :rayons="$rayons" />
</div> {{-- closes sk-content --}}
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dataUrl  = '{{ route("operator.pendapatan.data") }}';
        const storeUrl = '{{ route("operator.pendapatan.store") }}';
        const importUrl = '{{ route("operator.pendapatan.import") }}';
        const csrfToken = '{{ csrf_token() }}';

        // Initialize DataTable
        const table = $('#pendapatanTable').DataTable({
            processing: true,
            ajax: { url: dataUrl, data: function(d) { d.start_date = $('#filter_start_date').val(); d.end_date = $('#filter_end_date').val(); } },
            columns: [
                { data: null, render: function(d, t, r, meta) { return meta.row + 1; } },
                { data: 'tanggal', render: function(d) { const p = d.split('-'); return p[2]+'-'+p[1]+'-'+p[0]; } },
                { data: 'rayon.nama_rayon', className: 'fw-semibold', defaultContent: 'Tidak Diketahui' },
                { data: null, render: function(d, t, row) { const c = (row.juru_parkir && row.juru_parkir.jumlah_juru_parkir !== undefined) ? row.juru_parkir.jumlah_juru_parkir : (row.rayon ? row.rayon.jumlah_juru_parkir : 80); return c + ' Jukir'; } },
                { data: 'jumlah', className: 'text-end fw-semibold', render: function(d) { return 'Rp ' + parseInt(d).toLocaleString('id-ID'); } },
                { data: null, orderable: false, className: 'text-center', render: function(d, t, row) { const rn = row.rayon ? row.rayon.nama_rayon : ''; const p = row.tanggal.split('-'); const fd = p[2]+'-'+p[1]+'-'+p[0]; const desc = `Rayon ${rn} (${fd})`; return `<div class="action-btns justify-content-center"><button class="btn-action btn-edit" title="Edit" data-id="${row.id}"><i class="bi bi-pencil-square"></i></button><button class="btn-action btn-delete" title="Hapus" data-id="${row.id}" data-desc="${desc}"><i class="bi bi-trash"></i></button></div>`; } }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });

        $('#btnFilterTanggal').on('click', () => table.ajax.reload());
        $('#btnResetTanggal').on('click', () => { $('#filter_start_date').val(''); $('#filter_end_date').val(''); table.ajax.reload(); });

        function clearValidations(form) {
            form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }

        function handleValidationError(form, data) {
            Object.keys(data.errors).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const fb = input.nextElementSibling;
                    if (fb && fb.classList.contains('invalid-feedback')) fb.textContent = data.errors[key][0];
                }
            });
        }

        // Add Form
        const addForm = document.getElementById('addPendapatanForm');
        addForm.addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(addForm);
            fetch(storeUrl, { method: 'POST', body: new FormData(addForm), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(addForm, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('addPendapatanModal')).hide(); addForm.reset(); showToast('success', d.message); setTimeout(() => location.reload(), 1000); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Load Edit Modal
        $(document).on('click', '.btn-edit', function() {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('editPendapatanForm');
            clearValidations(form);
            fetch(`/operator/master-data/pendapatan/${id}`).then(r => r.json()).then(d => {
                document.getElementById('edit_id').value = d.id;
                document.getElementById('edit_tanggal').value = d.tanggal;
                document.getElementById('edit_rayon_id').value = d.rayon_id;
                document.getElementById('edit_jumlah').value = d.jumlah;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editPendapatanModal')).show();
            });
        });

        // Edit Form
        const editForm = document.getElementById('editPendapatanForm');
        editForm.addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(editForm);
            const id = document.getElementById('edit_id').value;
            fetch(`/operator/master-data/pendapatan/${id}`, { method: 'POST', body: new FormData(editForm), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(editForm, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('editPendapatanModal')).hide(); showToast('success', d.message); setTimeout(() => location.reload(), 1000); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            const id = this.getAttribute('data-id'); const desc = this.getAttribute('data-desc');
            Swal.fire({ title: 'Apakah Anda yakin?', text: `Data pendapatan untuk "${desc}" akan dihapus!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#005BAA', cancelButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' })
                .then(result => { if (result.isConfirmed) { fetch(`/operator/master-data/pendapatan/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.ok ? r.json() : r.json().then(d => { throw new Error(d.message || 'Gagal menghapus.'); })).then(d => { if (d.success) { showToast('success', d.message); setTimeout(() => location.reload(), 1000); } }).catch(e => Swal.fire('Gagal!', e.message, 'error')); } });
        });

        // Import Form
        const importForm = document.getElementById('importPendapatanForm');
        const btnSubmitImport = document.getElementById('btnSubmitImport');
        importForm.addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(importForm);
            btnSubmitImport.disabled = true; btnSubmitImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor...';
            fetch(importUrl, { method: 'POST', body: new FormData(importForm), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => { btnSubmitImport.disabled = false; btnSubmitImport.innerHTML = 'Unggah & Import'; return r.status === 422 ? r.json().then(d => { if (d.errors && Array.isArray(d.errors)) { Swal.fire({ title: 'Gagal Import Data', html: '<div class="text-start" style="max-height:200px;overflow-y:auto;"><ul class="text-danger">' + d.errors.map(e => `<li>${e}</li>`).join('') + '</ul></div>', icon: 'error' }); } else { Swal.fire('Gagal!', d.message || 'Format file salah.', 'error'); } throw new Error('Validation Error'); }) : r.json(); })
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('importPendapatanModal')).hide(); importForm.reset(); showToast('success', d.message); setTimeout(() => location.reload(), 1000); } })
                .catch(e => { btnSubmitImport.disabled = false; btnSubmitImport.innerHTML = 'Unggah & Import'; if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });
    });
</script>
@endsection
