{{-- Modal Tambah Rayon --}}
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

{{-- Modal Edit Rayon --}}
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
