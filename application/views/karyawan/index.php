<div class="topbar">
    <div>
        <h1>Data Karyawan</h1>
        <span class="topbar-date">Kelola karyawan, gaji harian, dan tarif lembur</span>
    </div>
    <button type="button" class="btn btn-rust" onclick="tambahKaryawan()">
        <i class="fa-solid fa-plus"></i> Tambah karyawan
    </button>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>Daftar karyawan</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($karyawan) ?> total</span>
        </div>

        <?php if (empty($karyawan)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-users"></i>
                Belum ada data karyawan. Klik "Tambah karyawan".
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>No HP</th>
                        <th>Tanggal masuk</th>
                        <th class="text-end">Gaji/hari</th>
                        <th class="text-end">Tarif lembur/jam</th>
                        <th>Status</th>
                        <th class="text-center" style="width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($karyawan as $k): ?>
                    <tr>
                        <td class="fw-medium"><?= html_escape($k->nama) ?></td>
                        <td><?= html_escape($k->jabatan) ?></td>
                        <td class=""><?= html_escape($k->no_hp) ?></td>
                        <td><?= $k->tanggal_masuk ? date('d/m/Y', strtotime($k->tanggal_masuk)) : '-' ?></td>
                        <td class="text-end money"><?= rupiah($k->gaji_pokok) ?></td>
                        <td class="text-end money"><?= rupiah($k->tarif_lembur) ?></td>
                        <td>
                            <span class="badge-status <?= $k->status === 'aktif' ? 'badge-tipe-jual' : 'badge-hutang' ?>">
                                <?= ucfirst($k->status) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit"
                                    onclick="editKaryawan(<?= html_escape(json_encode($k)) ?>)">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <?php if ($k->status === 'aktif'): ?>
                                <a href="<?= base_url('karyawan/nonaktifkan/' . $k->id) ?>" class="btn btn-sm btn-outline-danger py-1 px-2" title="Nonaktifkan"
                                   onclick="return confirm('Nonaktifkan karyawan ini? Data absensi & gaji lama tetap tersimpan.')">
                                    <i class="fa-solid fa-user-slash"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('karyawan/aktifkan/' . $k->id) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Aktifkan kembali">
                                    <i class="fa-solid fa-user-check"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form Karyawan -->
<div class="modal fade" id="modalKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; border-radius:10px;">
            <div class="modal-header" style="background-color: var(--paper-card); border-bottom: 1px solid var(--steel-line);">
                <h5 class="modal-title fw-semibold" id="modalKaryawanTitle" style="font-size:15px;">Tambah Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('karyawan/simpan') ?>" method="post">
                <div class="modal-body" style="background-color: var(--paper);">
                    <input type="hidden" name="id" id="form-id">

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="form-nama" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Jabatan</label>
                            <input type="text" name="jabatan" id="form-jabatan" class="form-control" placeholder="Mis. Kuli angkut">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">No HP</label>
                            <input type="text" name="no_hp" id="form-no-hp" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Tanggal masuk <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_masuk" id="form-tanggal-masuk" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Gaji per hari <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                                <input type="number" name="gaji_pokok" id="form-gaji-pokok" class="form-control figure" required min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Tarif lembur/jam <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                                <input type="number" name="tarif_lembur" id="form-tarif-lembur" class="form-control figure" required min="0" value="10000">
                            </div>
                        </div>
                    </div>
                    <div class="form-text" style="font-size:11px;">
                        Gaji per hari dibayarkan untuk tiap hari kerja hadir. Gaji mingguan dihitung otomatis di modul Penggajian (hari hadir × gaji/hari + total lembur).
                    </div>
                </div>
                <div class="modal-footer" style="background-color: var(--paper-card); border-top: 1px solid var(--steel-line);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-rust"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var modalKaryawan = new bootstrap.Modal(document.getElementById('modalKaryawan'));

    function tambahKaryawan() {
        document.getElementById('modalKaryawanTitle').textContent = 'Tambah Karyawan';
        document.getElementById('form-id').value = '';
        document.getElementById('form-nama').value = '';
        document.getElementById('form-jabatan').value = '';
        document.getElementById('form-no-hp').value = '';
        document.getElementById('form-tanggal-masuk').value = new Date().toISOString().slice(0, 10);
        document.getElementById('form-gaji-pokok').value = '';
        document.getElementById('form-tarif-lembur').value = '10000';
        modalKaryawan.show();
    }

    function editKaryawan(data) {
        document.getElementById('modalKaryawanTitle').textContent = 'Update Karyawan';
        document.getElementById('form-id').value = data.id;
        document.getElementById('form-nama').value = data.nama;
        document.getElementById('form-jabatan').value = data.jabatan || '';
        document.getElementById('form-no-hp').value = data.no_hp || '';
        document.getElementById('form-tanggal-masuk').value = data.tanggal_masuk || '';
        document.getElementById('form-gaji-pokok').value = Math.round(data.gaji_pokok);
        document.getElementById('form-tarif-lembur').value = Math.round(data.tarif_lembur);
        modalKaryawan.show();
    }
</script>