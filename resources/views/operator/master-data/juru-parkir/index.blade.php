@extends('layouts.app')

@section('title', 'Data Juru Parkir')
@section('subtitle', 'Halaman ini digunakan untuk mengelola data jumlah juru parkir aktif berdasarkan rayon.')

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
                @if(count($availableRayons) > 0)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJukirModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </button>
                @else
                    <button type="button" class="btn btn-primary" disabled title="Semua rayon sudah memiliki data juru parkir">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Data
                    </button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="jukirTable">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No</th>
                            <th>Nama Rayon</th>
                            <th style="text-align: right;">Jumlah Juru Parkir Aktif</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<x-master-data.juru-parkir.modals :availableRayons="$availableRayons" />
</div> {{-- closes sk-content --}}
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dataUrl  = '{{ route("operator.juru-parkir.data") }}';
        const storeUrl = '{{ route("operator.juru-parkir.store") }}';
        const csrfToken = '{{ csrf_token() }}';

        const table = $('#jukirTable').DataTable({
            processing: true,
            ajax: dataUrl,
            columns: [
                { data: null, render: function(d, t, r, meta) { return meta.row + 1; } },
                { data: 'rayon.nama_rayon', className: 'fw-semibold', defaultContent: 'Tidak Diketahui' },
                { data: 'jumlah_juru_parkir', className: 'text-end fw-semibold', render: function(d) { return `${d} Orang`; } },
                { data: null, orderable: false, className: 'text-center', render: function(d, t, row) { const rn = row.rayon ? row.rayon.nama_rayon : ''; return `<div class="action-btns justify-content-center"><button class="btn-action btn-edit" title="Edit" data-id="${row.id}"><i class="bi bi-pencil-square"></i></button><button class="btn-action btn-delete" title="Hapus" data-id="${row.id}" data-rayon="${rn}"><i class="bi bi-trash"></i></button></div>`; } }
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

        // Add
        document.getElementById('addJukirForm').addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(this);
            fetch(storeUrl, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(this, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('addJukirModal')).hide(); this.reset(); showToast('success', d.message); setTimeout(() => location.reload(), 1000); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Load Edit
        $(document).on('click', '.btn-edit', function() {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('editJukirForm'); clearValidations(form);
            fetch(`/operator/master-data/juru-parkir/${id}`).then(r => r.json()).then(d => {
                document.getElementById('edit_id').value = d.id;
                document.getElementById('edit_rayon_id').value = d.rayon_id;
                document.getElementById('edit_rayon_display').value = d.rayon ? d.rayon.nama_rayon : 'Tidak Diketahui';
                document.getElementById('edit_jumlah_juru_parkir').value = d.jumlah_juru_parkir;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editJukirModal')).show();
            });
        });

        // Edit
        document.getElementById('editJukirForm').addEventListener('submit', function(e) {
            e.preventDefault(); clearValidations(this);
            const id = document.getElementById('edit_id').value;
            fetch(`/operator/master-data/juru-parkir/${id}`, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.status === 422 ? r.json().then(d => { handleValidationError(this, d); throw new Error('Validation Error'); }) : r.json())
                .then(d => { if (d.success) { bootstrap.Modal.getOrCreateInstance(document.getElementById('editJukirModal')).hide(); showToast('success', d.message); setTimeout(() => location.reload(), 1000); } })
                .catch(e => { if (e.message !== 'Validation Error') Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            const id = this.getAttribute('data-id'); const rayon = this.getAttribute('data-rayon');
            Swal.fire({ title: 'Apakah Anda yakin?', text: `Data juru parkir untuk "${rayon}" akan dihapus!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#005BAA', cancelButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' })
                .then(result => { if (result.isConfirmed) { fetch(`/operator/master-data/juru-parkir/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.ok ? r.json() : r.json().then(d => { throw new Error(d.message || 'Gagal.'); })).then(d => { if (d.success) { showToast('success', d.message); setTimeout(() => location.reload(), 1000); } }).catch(e => Swal.fire('Gagal!', e.message, 'error')); } });
        });
    });
</script>
@endsection
