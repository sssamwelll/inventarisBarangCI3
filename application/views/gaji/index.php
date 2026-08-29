<div class="topbar">
    <div>
        <h1>Penggajian</h1>
        <span class="topbar-date">Riwayat gaji mingguan yang sudah diproses</span>
    </div>
    <a href="<?= base_url('gaji/proses') ?>" class="btn btn-rust">
        <i class="fa-solid fa-money-check-dollar"></i> Proses gaji mingguan
    </a>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <div class="panel mb-3">
        <div class="panel-header"><h2>Filter</h2></div>
        <form method="get" name="admin" action="<?= base_url('Gaji') ?>" class="row g-2 p-3">
            <input type="hidden" value="<?= $this->session->userdata('nama');?>">
            <div class="col-6 col-md-3">
                <label class="form-label small">Periode dari</label>
                <input type="date" name="periode_awal" class="form-control form-control-sm" value="<?= html_escape($filter['periode_awal']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Periode sampai</label>
                <input type="date" name="periode_akhir" class="form-control form-control-sm" value="<?= html_escape($filter['periode_akhir']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Karyawan</label>
                <select name="karyawan_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach ($karyawan_list as $k): ?>
                        <option value="<?= $k->id ?>" <?= (string) $filter['karyawan_id'] === (string) $k->id ? 'selected' : '' ?>>
                            <?= html_escape($k->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-rust btn-sm w-100"><i class="fa-solid fa-filter"></i> Terapkan</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Riwayat gaji</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($riwayat) ?> catatan</span>
        </div>

        <?php if (empty($riwayat)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-money-check-dollar"></i>
                Belum ada riwayat gaji. Klik "Proses gaji mingguan" untuk mulai.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Nama</th>
                        <th class="text-end">Hadir</th>
                        <th class="text-end">Lembur</th>
                        <th class="text-end">Potongan</th>
                        <th class="text-end">Total gaji</th>
                        <th>Status</th>
                        <th class="text-center" style="width:56px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($riwayat as $g): ?>
                    <tr>
                        <td><?= date('d/m', strtotime($g->periode_awal)) ?> – <?= date('d/m/Y', strtotime($g->periode_akhir)) ?></td>
                        <td class="fw-medium"><?= html_escape($g->nama) ?></td>
                        <td class="text-end money"><?= $g->total_hadir ?> hari</td>
                        <td class="text-end money"><?= angka($g->total_jam_lembur, 2) ?> jam</td>
                        <td class="text-end money"><?= $g->potongan > 0 ? rupiah($g->potongan) : '—' ?></td>
                        <td class="text-end money fw-semibold"><?= rupiah($g->total_gaji) ?></td>
                        <td><span class="badge-status badge-lunas"><?= ucfirst($g->status_bayar) ?></span></td>
                        <td class="text-center">
                            <a href="<?= base_url('gaji/cetak/' . $g->id) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" target="_blank" title="Cetak slip">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>