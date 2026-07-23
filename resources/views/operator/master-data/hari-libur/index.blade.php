@extends('layouts.app')

@section('title', 'Data Hari Libur & Weekend')
@section('subtitle', 'Halaman ini digunakan untuk mengelola data hari libur nasional dan akhir pekan sebagai faktor eksternal dalam prediksi pendapatan.')

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
            <div class="d-flex justify-content-end align-items-center mb-3 pb-3 border-bottom flex-wrap gap-3">
                <div class="d-inline-flex gap-2">
                    <button type="button" class="btn btn-border" data-bs-toggle="modal" data-bs-target="#generateLiburModal">
                        <i class="bi bi-calendar-check me-1"></i> Generate Otomatis
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLiburModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="liburTable">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Keterangan</th>
                            <th style="width: 180px;">Tipe</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<x-master-data.hari-libur.modals />
</div> {{-- closes sk-content --}}
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dataUrl     = '{{ route("operator.hari-libur.data") }}';
        const storeUrl    = '{{ route("operator.hari-libur.store") }}';
        const generateUrl = '{{ route("operator.hari-libur.generate") }}';
        const csrfToken   = '{{ csrf_token() }}';

        // Year increment/decrement
        const yearInput = document.getElementById('generate_year');
        document.getElementById('btn-decrement-year')?.addEventListener('click', () => { const y = parseInt(yearInput.value); if (y > 2020) yearInput.value = y - 1; });
        document.getElementById('btn-increment-year')?.addEventListener('click', () => { const y = parseInt(yearInput.value); if (y < 2035) yearInput.value = y + 1; });

        // DataTable
        const table = $('#liburTable').DataTable({
            processing: true,
            ajax: dataUrl,
            columns: [
                { data: null, render: function(d, t, r, meta) { return meta.settings._iDisplayStart + meta.row + 1; } },
                { data: 'tanggal', render: function(d) { const p = d.split('-'); return p[2]+'-'+p[1]+'-'+p[0]; } },
                { data: 'hari' },
                { data: 'keterangan' },
                { data: 'tipe', render: function(d) { return d === 'Libur Nasional' ? '<span class="badge bg-primary">Libur Nasional</span>' : '<span class="badge bg-secondary text-dark">Weekend</span>'; } },
                { data: null, orderable: false, className: 'text-center', render: function(d, t, row) { const p = row.tanggal.split('-'); const fd = p[2]+'-'+p[1]+'-'+p[0]; const desc = `${row.keterangan} (${fd})`; return `<div class="action-btns justify-content-center"><button class="btn-action btn-edit" title="Edit" data-id="${row.id}"><i class="bi bi-pencil-square"></i></button><button class="btn-action btn-delete" title="Hapus" data-id="${row.id}" data-desc="${desc}"><i class="bi bi-trash"></i></button></div>`; } }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });

        function clearValidations(form) {
            form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }
        function handleValidationError(form, data) {
            Object.keys(data.errors).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) { input.classList.add('is-invalid'); const fb = input.nextElementSibling; if (fb?.classList.contains('invalid-feedback')) fb.textContent = data.errors[key][0]; }
            });
        }

        // Generate Form
        const btnGenerate = document.getElementById('btnSubmitGenerate');
        document.getElementById('generateLiburForm').addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(this);
            btnGenerate.disabled = true; btnGenerate.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
            fetch(generateUrl, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => { btnGenerate.disabled = false; btnGenerate.innerHTML = 'Generate'; return r.status === 422 ? r.json().then(d => { Object.keys(d.errors).forEach(k => { if (k === 'year') { const fb = document.getElementById('year-validation-feedback'); if (fb) { fb.textContent = d.errors[k][0]; fb.style.display = 'block'; } } else { const input = this.querySelector(`[name="${k}"]`); if (input) { input.classList.add('is-invalid'); const fb = input.parentNode.querySelector('.invalid-feedback'); if (fb) fb.textContent = d.errors[k][0]; } } }); throw new Error('Validation Error'); }) : r.ok ? r.json() : r.json().then(d => { throw new Error(d.message || 'Terjadi kesalahan.'); }); })
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('generateLiburModal')).hide(); this.reset(); showToast('success', d.message); table.ajax.reload(); } })
                .catch(e => { btnGenerate.disabled = false; btnGenerate.innerHTML = 'Generate'; if (e.message !== 'Validation Error') Swal.fire('Gagal!', e.message, 'error'); });
        });

        // Add Form
        document.getElementById('addLiburForm').addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(this);
            fetch(storeUrl, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(this, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('addLiburModal')).hide(); this.reset(); showToast('success', d.message); table.ajax.reload(); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Load Edit Modal
        $(document).on('click', '.btn-edit', function() {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('editLiburForm'); clearValidations(form);
            fetch(`/operator/master-data/hari-libur/${id}`).then(r => r.json()).then(d => {
                document.getElementById('edit_id').value = d.id;
                document.getElementById('edit_tanggal').value = d.tanggal;
                document.getElementById('edit_keterangan').value = d.keterangan;
                document.getElementById('edit_tipe').value = d.tipe;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editLiburModal')).show();
            });
        });

        // Edit Form
        document.getElementById('editLiburForm').addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(this);
            const id = document.getElementById('edit_id').value;
            fetch(`/operator/master-data/hari-libur/${id}`, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(this, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('editLiburModal')).hide(); showToast('success', d.message); table.ajax.reload(); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            const id = this.getAttribute('data-id'); const desc = this.getAttribute('data-desc');
            Swal.fire({ title: 'Apakah Anda yakin?', text: `Hari libur "${desc}" akan dihapus!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#005BAA', cancelButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' })
                .then(result => { if (result.isConfirmed) { fetch(`/operator/master-data/hari-libur/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.ok ? r.json() : r.json().then(d => { throw new Error(d.message || 'Gagal.'); })).then(d => { if (d.success) { showToast('success', d.message); table.ajax.reload(); } }).catch(e => Swal.fire('Gagal!', e.message, 'error')); } });
        });
    });
</script>
@endsection
