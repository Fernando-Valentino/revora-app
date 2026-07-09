@extends('layouts.app')

@section('title', 'Data Juru Parkir')
@section('subtitle', 'Halaman ini digunakan untuk mengelola data jumlah juru parkir aktif berdasarkan rayon.')

@section('content')
<div class="container-fluid p-0">
    <!-- Toolbar (Bootstrap Row / Col) -->
    <div class="row mb-4">
        <div class="col text-end">
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
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-body">
            <!-- Table Header Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-3">
                <h5 class="card-title mb-0" style="border-bottom: none !important; padding-bottom: 0 !important;">Daftar Data Juru Parkir</h5>
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
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Juru Parkir -->
<div class="modal fade" id="addJukirModal" tabindex="-1" aria-labelledby="addJukirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addJukirModalLabel">Tambah Data Juru Parkir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addJukirForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_rayon_id" class="form-label">Rayon</label>
                        <select class="form-select" id="add_rayon_id" name="rayon_id" required>
                            <option value="">-- Pilih Rayon --</option>
                            @foreach($availableRayons as $rayon)
                                <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }} ({{ $rayon->kecamatan }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_jumlah_juru_parkir" class="form-label">Jumlah Juru Parkir Aktif</label>
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

<!-- Modal Edit Juru Parkir -->
<div class="modal fade" id="editJukirModal" tabindex="-1" aria-labelledby="editJukirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editJukirModalLabel">Edit Data Juru Parkir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editJukirForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_rayon_display" class="form-label">Rayon</label>
                        <input type="text" class="form-control bg-light" id="edit_rayon_display" readonly />
                        <input type="hidden" id="edit_rayon_id" name="rayon_id" />
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah_juru_parkir" class="form-label">Jumlah Juru Parkir Aktif</label>
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
        // Initialize DataTable
        const table = $('#jukirTable').DataTable({
            processing: true,
            ajax: '{{ route("operator.juru-parkir.data") }}',
            columns: [
                { 
                    data: null, 
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { 
                    data: 'rayon.nama_rayon', 
                    className: 'fw-semibold',
                    defaultContent: 'Tidak Diketahui'
                },
                { 
                    data: 'jumlah_juru_parkir', 
                    className: 'text-end fw-semibold',
                    render: function (data) {
                        return `${data} Orang`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        const rayonName = row.rayon ? row.rayon.nama_rayon : '';
                        return `
                            <div class="action-btns justify-content-center">
                                <button class="btn-action btn-edit" title="Edit" data-id="${row.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn-action btn-delete" title="Hapus" data-id="${row.id}" data-rayon="${rayonName}">
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
            form.querySelectorAll('.form-control, .form-select').forEach(input => {
                input.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(div => {
                div.textContent = '';
            });
        }

        // Add Form AJAX
        const addForm = document.getElementById('addJukirForm');
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidations(addForm);

            const formData = new FormData(addForm);

            fetch("{{ route('operator.juru-parkir.store') }}", {
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
                    const modalEl = document.getElementById('addJukirModal');
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    addForm.reset();
                    showToast('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
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
            const form = document.getElementById('editJukirForm');
            clearValidations(form);

            fetch(`/operator/master-data/juru-parkir/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_rayon_id').value = data.rayon_id;
                    document.getElementById('edit_rayon_display').value = data.rayon ? data.rayon.nama_rayon : 'Tidak Diketahui';
                    document.getElementById('edit_jumlah_juru_parkir').value = data.jumlah_juru_parkir;
                    
                    const modalEl = document.getElementById('editJukirModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
        });

        // Edit Form AJAX
        const editForm = document.getElementById('editJukirForm');
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearValidations(editForm);

            const id = document.getElementById('edit_id').value;
            const formData = new FormData(editForm);

            fetch(`/operator/master-data/juru-parkir/${id}`, {
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
                    const modalEl = document.getElementById('editJukirModal');
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    showToast('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
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
            const rayon = this.getAttribute('data-rayon');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Data juru parkir untuk "${rayon}" akan dihapus!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#005BAA',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/operator/master-data/juru-parkir/${id}`, {
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
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
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
