<div class="topbar">
    <div>
        <h1>Absensi</h1>
        <span class="topbar-date">Input kehadiran harian — jam kerja 08:00–16:00</span>
    </div>
    <a href="<?= base_url('absensi/riwayat') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat absensi
    </a>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0"><?= html_escape($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <div class="panel mb-3">
        <form method="get" action="<?= base_url('absensi') ?>" class="d-flex align-items-end gap-2 p-3">
            <div>
                <label class="form-label small">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= html_escape($tanggal) ?>" onchange="this.form.submit()">
            </div>
            <a href="<?= base_url('absensi?tanggal=' . date('Y-m-d', strtotime($tanggal . ' -1 day'))) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <a href="<?= base_url('absensi?tanggal=' . date('Y-m-d', strtotime($tanggal . ' +1 day'))) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <a href="<?= base_url('absensi') ?>" class="btn btn-sm btn-outline-secondary">Hari ini</a>
        </form>
    </div>

    <?php if (empty($karyawan)): ?>
        <div class="panel">
            <div class="empty-state">
                <i class="fa-solid fa-users"></i>
                Belum ada karyawan aktif. Tambahkan dulu di menu <a href="<?= base_url('karyawan') ?>">Data karyawan</a>.
            </div>
        </div>
    <?php else: ?>
    <form method="post" action="<?= base_url('absensi/simpan') ?>" id="form-absensi">
        <input type="hidden" name="tanggal" value="<?= html_escape($tanggal) ?>">

        <div class="panel">
            <div class="panel-header">
                <h2>Kehadiran — <?= date('d F Y', strtotime($tanggal)) ?></h2>
                <span style="font-size:12px; color:var(--steel);"><?= count($karyawan) ?> karyawan aktif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-rosok mb-0" id="tabel-absensi">
                    <thead>
                        <tr>
                            <th style="width:22%">Nama</th>
                            <th style="width:14%">Status</th>
                            <th style="width:14%">Jam masuk</th>
                            <th style="width:14%">Jam pulang</th>
                            <th style="width:12%" class="text-end">Est. lembur</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($karyawan as $i => $k): ?>
                        <?php
                            $status = $k->status ?: 'hadir';
                            $jam_masuk = $k->jam_masuk ?: '08:00';
                            $jam_pulang = $k->jam_pulang ?: '16:00';
                        ?>
                        <tr class="baris-absensi" data-index="<?= $i ?>">
                            <td>
                                <input type="hidden" name="karyawan_id[]" value="<?= $k->karyawan_id ?>">
                                <?= html_escape($k->nama) ?>
                                <div style="font-size:11px; color:var(--steel);"><?= html_escape($k->jabatan) ?></div>
                            </td>
                            <td>
                                <select name="status[]" class="form-select form-select-sm status-select" <?= $i === 0 ? 'data-autofocus' : '' ?>>
                                    <option value="hadir" <?= $status === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                    <option value="izin" <?= $status === 'izin' ? 'selected' : '' ?>>Izin</option>
                                    <option value="sakit" <?= $status === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                    <option value="alpa" <?= $status === 'alpa' ? 'selected' : '' ?>>Alpa</option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="jam_masuk[]" class="form-control form-control-sm jam-masuk-input" value="<?= html_escape($jam_masuk) ?>" <?= $status !== 'hadir' ? 'readonly' : '' ?>>
                            </td>
                            <td>
                                <input type="time" name="jam_pulang[]" class="form-control form-control-sm jam-pulang-input" value="<?= html_escape($jam_pulang) ?>" <?= $status !== 'hadir' ? 'readonly' : '' ?>>
                            </td>
                            <td class="text-end money lembur-preview" style="font-size:12.5px;">
                                <?= $k->jam_lembur ? angka($k->jam_lembur, 2) . ' jam' : '-' ?>
                            </td>
                            <td>
                                <input type="text" name="keterangan[]" class="form-control form-control-sm" value="<?= html_escape($k->keterangan) ?>" placeholder="opsional">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-rust mt-3">
            <i class="fa-solid fa-floppy-disk"></i> Simpan absensi hari ini
        </button>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var JAM_SELESAI_KERJA = 16; // 16:00, harus sama dengan Absensi_model::JAM_SELESAI_KERJA

    function hitungLemburPreview(row) {
        var jamPulangInput = row.querySelector('.jam-pulang-input');
        var previewCell = row.querySelector('.lembur-preview');
        var val = jamPulangInput.value; // format "HH:MM"
        if (!val) { previewCell.textContent = '-'; return; }

        var parts = val.split(':');
        var totalJam = parseInt(parts[0], 10) + (parseInt(parts[1], 10) / 60);
        var lembur = totalJam - JAM_SELESAI_KERJA;

        previewCell.textContent = lembur > 0 ? lembur.toFixed(2).replace('.', ',') + ' jam' : '-';
    }

    function toggleStatusRow(row) {
        var status = row.querySelector('.status-select').value;
        var jamMasuk = row.querySelector('.jam-masuk-input');
        var jamPulang = row.querySelector('.jam-pulang-input');
        var aktif = status === 'hadir';

        jamMasuk.readOnly = !aktif;
        jamPulang.readOnly = !aktif;

        if (aktif) {
            hitungLemburPreview(row);
        } else {
            row.querySelector('.lembur-preview').textContent = '-';
        }
    }

    document.querySelectorAll('.baris-absensi').forEach(function (row) {
        toggleStatusRow(row);

        row.querySelector('.status-select').addEventListener('change', function () {
            toggleStatusRow(row);
        });
        row.querySelector('.jam-pulang-input').addEventListener('input', function () {
            hitungLemburPreview(row);
        });
    });
});
</script>