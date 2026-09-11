<div class="topbar">
    <button type="button" id="btn-sidebar-toggle" class="btn d-md-none me-3" style="background:none; border:none; padding:0; color:var(--ink); font-size:20px;">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <h1>Riwayat Absensi</h1>
        <span class="topbar-date">Rekap kehadiran &amp; lembur per periode</span>
    </div>
    <a href="<?= base_url('absensi') ?>" class="btn btn-rust">
        <i class="fa-solid fa-calendar-check"></i> Input absensi hari ini
    </a>
</div>

<div class="content-area">
    <div class="panel mb-3">
        <div class="panel-header"><h2>Filter</h2></div>
        <form method="get" action="<?= base_url('absensi/riwayat') ?>" class="row g-2 p-3">
            <div class="col-6 col-md-3">
                <label class="form-label small">Dari tanggal</label>
                <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="<?= html_escape($filter['tanggal_dari']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Sampai tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="<?= html_escape($filter['tanggal_sampai']) ?>">
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
            <h2>Detail kehadiran</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($riwayat) ?> catatan</span>
        </div>

        <?php if (empty($riwayat)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Tidak ada data absensi pada filter ini.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Jam masuk</th>
                        <th>Jam pulang</th>
                        <th class="text-end">Lembur</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r->tanggal)) ?></td>
                        <td><?= html_escape($r->nama) ?></td>
                        <td>
                            <?php
                                $badge_map = array('hadir' => 'badge-tipe-jual', 'izin' => 'badge-tipe-beli', 'sakit' => 'badge-tipe-beli', 'alpa' => 'badge-hutang');
                                $badge_class = isset($badge_map[$r->status]) ? $badge_map[$r->status] : '';
                            ?>
                            <span class="badge-status <?= $badge_class ?>"><?= ucfirst($r->status) ?></span>
                        </td>
                        <td class="money"><?= $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' ?></td>
                        <td class="money"><?= $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-' ?></td>
                        <td class="text-end money"><?= $r->jam_lembur > 0 ? angka($r->jam_lembur, 2) . ' jam' : '-' ?></td>
                        <td style="font-size:12.5px; color:var(--steel);"><?= html_escape($r->keterangan) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>