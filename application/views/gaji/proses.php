<div class="topbar">
    <div>
        <h1>Proses Gaji Mingguan</h1>
        <span class="topbar-date">Periode <?= date('d M', strtotime($periode_awal)) ?> – <?= date('d M Y', strtotime($periode_akhir)) ?></span>
    </div>
    <a href="<?= base_url('gaji') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0"><?= html_escape($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <div class="panel mb-3">
        <form method="get" action="<?= base_url('gaji/proses') ?>" class="d-flex align-items-end gap-2 p-3 flex-wrap">
            <div>
                <label class="form-label small">Periode awal (Senin)</label>
                <input type="date" name="periode_awal" class="form-control form-control-sm" value="<?= html_escape($periode_awal) ?>">
            </div>
            <div>
                <label class="form-label small">Periode akhir (Sabtu)</label>
                <input type="date" name="periode_akhir" class="form-control form-control-sm" value="<?= html_escape($periode_akhir) ?>">
            </div>
            <button type="submit" class="btn btn-rust btn-sm"><i class="fa-solid fa-rotate"></i> Muat ulang</button>
            <a href="<?= base_url('gaji/proses?periode_awal=' . date('Y-m-d', strtotime($periode_awal . ' -7 day')) . '&periode_akhir=' . date('Y-m-d', strtotime($periode_akhir . ' -7 day'))) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-chevron-left"></i> Minggu lalu
            </a>
            <a href="<?= base_url('gaji/proses?periode_awal=' . date('Y-m-d', strtotime($periode_awal . ' +7 day')) . '&periode_akhir=' . date('Y-m-d', strtotime($periode_akhir . ' +7 day'))) ?>" class="btn btn-sm btn-outline-secondary">
                Minggu depan <i class="fa-solid fa-chevron-right"></i>
            </a>
        </form>
    </div>

    <?php if (empty($preview)): ?>
        <div class="panel">
            <div class="empty-state">
                <i class="fa-solid fa-users"></i>
                Belum ada karyawan aktif.
            </div>
        </div>
    <?php else: ?>
    <form method="post" action="<?= base_url('gaji/simpan') ?>" id="form-gaji">
        <input type="hidden" name="periode_awal" value="<?= html_escape($periode_awal) ?>">
        <input type="hidden" name="periode_akhir" value="<?= html_escape($periode_akhir) ?>">

        <div class="panel">
            <div class="panel-header">
                <h2>Rekap &amp; estimasi gaji</h2>
                <span style="font-size:12px; color:var(--steel);"><?= count($preview) ?> karyawan aktif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-rosok mb-0">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Nama</th>
                            <th class="text-end">Hadir</th>
                            <th class="text-end">Jam lembur</th>
                            <th class="text-end">Gaji pokok</th>
                            <th class="text-end">Lembur</th>
                            <th style="width:130px;">Potongan</th>
                            <th class="text-end">Estimasi total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($preview as $i => $p): ?>
                        <?php
                            $k = $p['karyawan'];
                            $gaji_pokok_periode = $p['total_hadir'] * (float) $k->gaji_pokok;
                            $tunjangan_lembur = $p['total_jam_lembur'] * (float) $k->tarif_lembur;
                        ?>
                        <tr class="baris-gaji <?= $p['sudah_diproses'] ? 'text-muted' : '' ?>"
                            data-gaji-pokok="<?= $k->gaji_pokok ?>"
                            data-tarif-lembur="<?= $k->tarif_lembur ?>"
                            data-hadir="<?= $p['total_hadir'] ?>"
                            data-jam-lembur="<?= $p['total_jam_lembur'] ?>">
                            <td>
                                <input type="hidden" name="karyawan_id[]" value="<?= $k->id ?>">
                                <?php if ($p['sudah_diproses']): ?>
                                    <input type="checkbox" class="form-check-input" disabled title="Sudah pernah digaji periode ini">
                                <?php else: ?>
                                    <input type="checkbox" name="bayar[]" value="<?= $k->id ?>" class="form-check-input chk-bayar" checked>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= html_escape($k->nama) ?>
                                <?php if ($p['sudah_diproses']): ?>
                                    <span class="badge-status badge-lunas" style="margin-left:6px;">Sudah digaji</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end figure"><?= $p['total_hadir'] ?> hari</td>
                            <td class="text-end figure"><?= angka($p['total_jam_lembur'], 2) ?> jam</td>
                            <td class="text-end money gaji-pokok-cell"><?= rupiah($gaji_pokok_periode) ?></td>
                            <td class="text-end money lembur-cell"><?= rupiah($tunjangan_lembur) ?></td>
                            <td>
                                <input type="number" name="potongan[]" class="form-control form-control-sm potongan-input figure" value="0" min="0" <?= $p['sudah_diproses'] ? 'readonly' : '' ?>>
                            </td>
                            <td class="text-end money fw-semibold total-cell"><?= rupiah(max(0, $gaji_pokok_periode + $tunjangan_lembur)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-rust mt-3" onclick="return confirm('Proses & bayar gaji untuk karyawan yang dicentang? Tindakan ini otomatis mencatat pengeluaran ke Kas.')">
            <i class="fa-solid fa-money-check-dollar"></i> Proses &amp; bayar gaji terpilih
        </button>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatRupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    }

    function hitungBaris(row) {
        var gajiPokokHarian = parseFloat(row.dataset.gajiPokok) || 0;
        var tarifLembur = parseFloat(row.dataset.tarifLembur) || 0;
        var hadir = parseFloat(row.dataset.hadir) || 0;
        var jamLembur = parseFloat(row.dataset.jamLembur) || 0;
        var potonganInput = row.querySelector('.potongan-input');
        var potongan = parseFloat(potonganInput.value) || 0;

        var subtotalPokok = hadir * gajiPokokHarian;
        var subtotalLembur = jamLembur * tarifLembur;
        var total = Math.max(0, subtotalPokok + subtotalLembur - potongan);

        row.querySelector('.total-cell').textContent = formatRupiah(total);
    }

    document.querySelectorAll('.baris-gaji').forEach(function (row) {
        var potonganInput = row.querySelector('.potongan-input');
        if (potonganInput && !potonganInput.disabled) {
            potonganInput.addEventListener('input', function () {
                hitungBaris(row);
            });
        }
    });
});
</script>