<div class="topbar">
    <button type="button" id="btn-sidebar-toggle" class="btn d-md-none me-3" style="background:none; border:none; padding:0; color:var(--ink); font-size:20px;">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <h1>Dashboard</h1>
        <span class="topbar-date"><?= date('l, d F Y') ?></span>
    </div>
    <a href="<?= base_url('Transaksi/tambah') ?>" id="btn-transaksi-baru" class="btn btn-rust">
        <i class="fa-solid fa-plus"></i> Transaksi baru <kbd>n</kbd>
    </a>
</div>

<div class="content-area">
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-down-to-bracket"></i> Beli hari ini</div>
                <div class="stat-value"><?= $jumlah_beli_hari_ini ?> <span style="font-size:13px; font-weight:400; color:var(--steel);">nota</span></div>
                <div class="stat-note money"><?= rupiah($nominal_beli_hari_ini) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-up-from-bracket"></i> Jual hari ini</div>
                <div class="stat-value"><?= $jumlah_jual_hari_ini ?> <span style="font-size:13px; font-weight:400; color:var(--steel);">nota</span></div>
                <div class="stat-note money"><?= rupiah($nominal_jual_hari_ini) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-wallet"></i> Saldo kas</div>
                <div class="stat-value money"><?= rupiah($saldo_kas) ?></div>
                <div class="stat-note">Total kas masuk dikurangi keluar</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-users"></i> Karyawan hadir</div>
                <div class="stat-value"><?= $karyawan_hadir ?> <span style="font-size:13px; font-weight:400; color:var(--steel);">/ <?= $total_karyawan ?></span></div>
                <div class="stat-note">Absensi hari ini</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Transaksi terbaru</h2>
            <a href="<?= base_url('transaksi') ?>" style="font-size:12.5px; color:var(--rust); font-weight:500;">
                Lihat semua <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
            </a>
        </div>

        <?php if (empty($transaksi_terbaru)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-receipt"></i>
                Belum ada transaksi. Tekan <kbd>n</kbd> atau klik "Transaksi baru" untuk mulai mencatat.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok">
                <thead>
                    <tr>
                        <th>No nota</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Nama pihak</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transaksi_terbaru as $t): ?>
                    <tr>
                        <td><?= html_escape($t->no_nota) ?></td>
                        <td><?= date('d/m/Y', strtotime($t->tanggal)) ?></td>
                        <td>
                            <span class="badge-status <?= $t->tipe === 'beli' ? 'badge-tipe-beli' : 'badge-tipe-jual' ?>">
                                <?= ucfirst($t->tipe) ?>
                            </span>
                        </td>
                        <td><?= html_escape($t->nama_pihak) ?></td>
                        <td>
                            <span class="badge-status badge-<?= $t->status_bayar ?>"><?= ucfirst($t->status_bayar) ?></span>
                        </td>
                        <td class="text-end money"><?= rupiah($t->total) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
