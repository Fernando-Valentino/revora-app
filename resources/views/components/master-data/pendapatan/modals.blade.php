@props(['rayons'])

{{-- Modal Tambah Pendapatan --}}
<div class="modal fade" id="addPendapatanModal" tabindex="-1" aria-labelledby="addPendapatanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPendapatanModalLabel">Tambah Data Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPendapatanForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="add_tanggal" name="tanggal" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_rayon_id" class="form-label">Rayon</label>
                        <select class="form-select" id="add_rayon_id" name="rayon_id" required>
                            <option value="">-- Pilih Rayon --</option>
                            @foreach($rayons as $rayon)
                                <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }} ({{ $rayon->kecamatan }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_jumlah" class="form-label">Jumlah Pendapatan (Rp)</label>
                        <input type="number" class="form-control" id="add_jumlah" name="jumlah" required min="0" placeholder="Contoh: 1250000" />
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

{{-- Modal Edit Pendapatan --}}
<div class="modal fade" id="editPendapatanModal" tabindex="-1" aria-labelledby="editPendapatanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPendapatanModalLabel">Edit Data Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPendapatanForm">
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
                        <label for="edit_rayon_id" class="form-label">Rayon</label>
                        <select class="form-select" id="edit_rayon_id" name="rayon_id" required>
                            @foreach($rayons as $rayon)
                                <option value="{{ $rayon->id }}">{{ $rayon->nama_rayon }} ({{ $rayon->kecamatan }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah" class="form-label">Jumlah Pendapatan (Rp)</label>
                        <input type="number" class="form-control" id="edit_jumlah" name="jumlah" required min="0" />
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

{{-- Modal Import Pendapatan --}}
<div class="modal fade" id="importPendapatanModal" tabindex="-1" aria-labelledby="importPendapatanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importPendapatanModalLabel">Import Data Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importPendapatanForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="bg-light p-2 rounded border mb-3" style="font-size: 11px;">
                        <span class="d-block fw-bold text-secondary mb-1" style="font-size: 11px;">Format File CSV / Excel:</span>
                        <div class="table-responsive">
                            <table class="table table-bordered bg-white text-dark mb-1 font-monospace" style="font-size: 10px; margin-bottom: 0;">
                                <thead>
                                    <tr class="table-light">
                                        <th style="padding: 4px 6px !important; text-align: center;">Tanggal</th>
                                        <th style="padding: 4px 6px !important; text-align: center;">Rayon</th>
                                        <th style="padding: 4px 6px !important; text-align: center;">Jumlah Jukir</th>
                                        <th style="padding: 4px 6px !important; text-align: center;">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 4px 6px !important; text-align: center;">2026-06-01</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">Rayon I</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">80</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">1250000</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 6px !important; text-align: center;">2026-06-01</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">Rayon II</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">82</td>
                                        <td style="padding: 4px 6px !important; text-align: center;">1680000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="d-block text-muted mt-1" style="font-size: 9px; line-height: 1.3;">
                            * Kolom <strong>Jumlah Jukir</strong> opsional. Kolom <strong>Rayon</strong> bisa berupa teks (Rayon I) atau ID (1).
                        </small>
                    </div>

                    <a href="{{ route('operator.pendapatan.template') }}" class="btn btn-outline-secondary w-100 mb-3">
                        <i class="bi bi-download me-1"></i> Unduh Template CSV
                    </a>

                    <div class="mb-3">
                        <label for="import_file" class="form-label" style="font-size: 13px; font-weight: 600;">Pilih File CSV (.csv)</label>
                        <input type="file" class="form-control" id="import_file" name="file" accept=".csv" required />
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitImport">Unggah & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
