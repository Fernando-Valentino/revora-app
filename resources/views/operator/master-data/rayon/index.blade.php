@extends('layouts.app')

@section('title', 'Data Rayon')
@section('subtitle', 'Halaman ini digunakan untuk mengelola data wilayah rayon parkir.')

@section('content')
<div class="container-fluid p-0">
    <!-- Table Card -->
    <div class="card">
        <div class="card-body">
            <!-- Table Header Toolbar -->
            <div class="d-flex justify-content-end align-items-center mb-3 pb-3 border-bottom flex-wrap gap-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRayonModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Data
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rayonTable">
                    <thead>
                        <tr>
                            <th style="width: 65px;">No</th>
                            <th>Nama Rayon</th>
                            <th>Kecamatan</th>
                            <th>Lokasi</th>
                            <th>Karakteristik Area</th>
                            <th style="text-align: right;">Jumlah Juru Parkir</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Rayon -->
<div class="modal fade" id="addRayonModal" tabindex="-1" aria-labelledby="addRayonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRayonModalLabel">Tambah Data Rayon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRayonForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_nama_rayon" class="form-label">Nama Rayon</label>
                        <input type="text" class="form-control" id="add_nama_rayon" name="nama_rayon" required placeholder="Contoh: Rayon I" />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_kecamatan" class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" id="add_kecamatan" name="kecamatan" required placeholder="Contoh: Kec. Kejaksan" />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="add_lokasi" name="lokasi" required placeholder="Contoh: Jl. Siliwangi, Jl. Kartini" />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_karakteristik_area" class="form-label">Karakteristik Area</label>
                        <input type="text" class="form-control" id="add_karakteristik_area" name="karakteristik_area" required placeholder="Contoh: Pusat Bisnis" />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_jumlah_juru_parkir" class="form-label">Jumlah Juru Parkir</label>
                        <input type="number" class="form-control" id="add_jumlah_juru_parkir" name="jumlah_juru_parkir" required min="0" placeholder="Contoh: 80" />
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Rayon -->
<div class="modal fade" id="editRayonModal" tabindex="-1" aria-labelledby="editRayonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRayonModalLabel">Edit Data Rayon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRayonForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_rayon" class="form-label">Nama Rayon</label>
                        <input type="text" class="form-control" id="edit_nama_rayon" name="nama_rayon" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kecamatan" class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" id="edit_kecamatan" name="kecamatan" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_karakteristik_area" class="form-label">Karakteristik Area</label>
                        <input type="text" class="form-control" id="edit_karakteristik_area" name="karakteristik_area" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah_juru_parkir" class="form-label">Jumlah Juru Parkir</label>
                        <input type="number" class="form-control" id="edit_jumlah_juru_parkir" name="jumlah_juru_parkir" required min="0" />
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // CSRF Token Setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Initialize DataTable
        const table = $('#rayonTable').DataTable({
            processing: true,
            ajax: '{{ route("operator.rayon.data") }}',
            columns: [
                { 
                    data: null, 
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'nama_rayon', className: 'fw-semibold' },
                { data: 'kecamatan' },
                { data: 'lokasi' },
                { data: 'karakteristik_area' },
                { data: 'jumlah_juru_parkir', className: 'text-end' },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `
                            <div class="action-btns justify-content-center">
                                <button class="btn-action btn-edit" title="Edit" data-id="${row.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn-action btn-delete" title="Hapus" data-id="${row.id}" data-name="${row.nama_rayon}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });

        // Clear validations helper
        function clearValidations(form) {
            form.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(div => {
                div.textContent = '';
            });
        }

        // Add Rayon Form AJAX
        const addForm = document.getElementById('addRayonForm');
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidations(addForm);
            
            const formData = new FormData(addForm);
            
            fetch("{{ route('operator.rayon.store') }}", {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.status === 422) {
                    return res.json().then(data => {
                        Object.keys(data.errors).forEach(key => {
                            const input = addForm.querySelector(`[name="${key}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = data.errors[key][0];
                                }
                            }
                        });
                        throw new Error("Validation Error");
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('addRayonModal');
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    addForm.reset();
                    showToast('success', data.message);
                    table.ajax.reload(null, false);
                }
            })
            .catch(err => {
                if (err.message !== "Validation Error") {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        });

        // Load Edit Modal (delegated)
        $(document).on('click', '.btn-edit', function() {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('editRayonForm');
            clearValidations(form);

            fetch(`/operator/master-data/rayon/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nama_rayon').value = data.nama_rayon;
                    document.getElementById('edit_kecamatan').value = data.kecamatan;
                    document.getElementById('edit_lokasi').value = data.lokasi;
                    document.getElementById('edit_karakteristik_area').value = data.karakteristik_area;
                    document.getElementById('edit_jumlah_juru_parkir').value = data.jumlah_juru_parkir;
                    
                    const modalEl = document.getElementById('editRayonModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
        });

        // Edit Rayon Form AJAX
        const editForm = document.getElementById('editRayonForm');
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidations(editForm);

            const id = document.getElementById('edit_id').value;
            const formData = new FormData(editForm);

            fetch(`/operator/master-data/rayon/${id}`, {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.status === 422) {
                    return res.json().then(data => {
                        Object.keys(data.errors).forEach(key => {
                            const input = editForm.querySelector(`[name="${key}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = data.errors[key][0];
                                }
                            }
                        });
                        throw new Error("Validation Error");
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('editRayonModal');
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    showToast('success', data.message);
                    table.ajax.reload(null, false);
                }
            })
            .catch(err => {
                if (err.message !== "Validation Error") {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        });

        // Delete Confirmation (delegated)
        $(document).on('click', '.btn-delete', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Data rayon "${name}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#005BAA',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/operator/master-data/rayon/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.json().then(data => {
                                throw new Error(data.message || 'Gagal menghapus data.');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast('success', data.message);
                            table.ajax.reload(null, false);
                        }
                    })
                    .catch(err => {
                        Swal.fire('Gagal!', err.message, 'error');
                    });
                }
            });
        });
    });
</script>
@endsection
