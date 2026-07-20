@props(['availableRayons'])

{{-- Modal Tambah Juru Parkir --}}
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

{{-- Modal Edit Juru Parkir --}}
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
