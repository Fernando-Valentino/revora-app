{{-- Modal Tambah Hari Libur --}}
<div class="modal fade" id="addLiburModal" tabindex="-1" aria-labelledby="addLiburModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLiburModalLabel">Tambah Data Hari Libur / Weekend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addLiburForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="add_tanggal" name="tanggal" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_keterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="add_keterangan" name="keterangan" required placeholder="Contoh: Tahun Baru Imlek, Weekend" />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_tipe" class="form-label">Tipe</label>
                        <select class="form-select" id="add_tipe" name="tipe" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Libur Nasional">Libur Nasional</option>
                            <option value="Weekend">Weekend</option>
                        </select>
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

{{-- Modal Edit Hari Libur --}}
<div class="modal fade" id="editLiburModal" tabindex="-1" aria-labelledby="editLiburModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLiburModalLabel">Edit Data Hari Libur / Weekend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLiburForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="edit_keterangan" name="keterangan" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tipe" class="form-label">Tipe</label>
                        <select class="form-select" id="edit_tipe" name="tipe" required>
                            <option value="Libur Nasional">Libur Nasional</option>
                            <option value="Weekend">Weekend</option>
                        </select>
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

{{-- Modal Generate Hari Libur --}}
<div class="modal fade" id="generateLiburModal" tabindex="-1" aria-labelledby="generateLiburModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateLiburModalLabel">Generate Hari Libur & Weekend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generateLiburForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label">Pilih Tahun</label>
                        <div class="input-group justify-content-center mx-auto" style="max-width: 200px;">
                            <button class="btn btn-border px-3" type="button" id="btn-decrement-year"><i class="bi bi-dash-lg"></i></button>
                            <input type="text" class="form-control text-center fw-bold" id="generate_year" name="year" value="{{ date('Y') }}" readonly style="background-color: var(--surface);" />
                            <button class="btn btn-border px-3" type="button" id="btn-increment-year"><i class="bi bi-plus-lg"></i></button>
                        </div>
                        <div class="invalid-feedback text-center" id="year-validation-feedback" style="font-size: 11.5px; font-weight: 500; margin-top: 8px;"></div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="import_api" name="import_api" value="1" checked />
                        <label class="form-check-label" for="import_api" style="font-size: 13px; font-weight: 500;">Ambil Hari Libur Nasional dari API</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="generate_weekend" name="generate_weekend" value="1" checked />
                        <label class="form-check-label" for="generate_weekend" style="font-size: 13px; font-weight: 500;">Generate Hari Sabtu & Minggu (Weekend)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitGenerate">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
