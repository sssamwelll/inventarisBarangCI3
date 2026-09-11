<div class="topbar">
    <button type="button" id="btn-sidebar-toggle" class="btn d-md-none me-3" style="background:none; border:none; padding:0; color:var(--ink); font-size:20px;">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <h1>Laporan</h1>
        <span class="topbar-date"><?= date('d M', strtotime($tanggal_dari)) ?> – <?= date('d M Y', strtotime($tanggal_sampai)) ?></span>
    </div>
    <a href="<?= base_url('laporan/cetak?tanggal_dari=' . $tanggal_dari . '&tanggal_sampai=' . $tanggal_sampai) ?>" class="btn btn-rust" target="_blank">
        <i class="fa-solid fa-file-pdf"></i> Cetak PDF
    </a>
</div>

<div class="content-area">
    <div class="panel mb-3">
        <div class="panel-header"><h2>Filter periode</h2></div>
        <form method="get" action="<?= base_url('laporan') ?>" class="row g-2 p-3">
            <div class="col-6 col-md-3">
                <label class="form-label small">Dari tanggal</label>
                <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="<?= html_escape($tanggal_dari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Sampai tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="<?= html_escape($tanggal_sampai) ?>">
            </div>
            <div class="col-6 col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-rust btn-sm w-100"><i class="fa-solid fa-filter"></i> Terapkan</button>
            </div>
        </form>
    </div>

    <!-- Ringkasan performa transaksi -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-down-to-bracket"></i> Total pembelian</div>
                <div class="stat-value money" style="font-size:19px;"><?= rupiah($total_beli) ?></div>
                <div class="stat-note"><?= $nota_beli ?> nota</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-up-from-bracket"></i> Total penjualan</div>
                <div class="stat-value money" style="font-size:19px;"><?= rupiah($total_jual) ?></div>
                <div class="stat-note"><?= $nota_jual ?> nota</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-scale-balanced"></i> Laba kotor</div>
                <div class="stat-value money" style="font-size:19px; color:<?= $laba_kotor >= 0 ? 'var(--ok)' : 'var(--danger)' ?>;"><?= rupiah($laba_kotor) ?></div>
                <div class="stat-note">Penjualan − pembelian</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-sack-dollar"></i> Laba bersih</div>
                <div class="stat-value money" style="font-size:19px; color:<?= $laba_bersih >= 0 ? 'var(--ok)' : 'var(--danger)' ?>;"><?= rupiah($laba_bersih) ?></div>
                <div class="stat-note">Setelah gaji &amp; operasional</div>
            </div>
        </div>
    </div>

    <!-- Rincian pengeluaran kas -->
    <div class="panel mb-3">
        <div class="panel-header"><h2>Realisasi pengeluaran kas (cash basis)</h2></div>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <tbody>
                    <tr>
                        <td>Pengeluaran gaji</td>
                        <td class="text-end money" style="color:var(--warn);"><?= rupiah($kas_keluar_gaji) ?></td>
                    </tr>
                    <tr>
                        <td>Pengeluaran operasional</td>
                        <td class="text-end money" style="color:var(--warn);"><?= rupiah($kas_keluar_operasional) ?></td>
                    </tr>
                    <tr>
                        <td>Pengeluaran lainnya</td>
                        <td class="text-end money" style="color:var(--warn);"><?= rupiah($kas_keluar_lainnya) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Breakdown kategori -->
    <div class="panel mb-3">
        <div class="panel-header"><h2>Breakdown per kategori barang</h2></div>
        <?php if (empty($breakdown_kategori)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-boxes-stacked"></i>
                Tidak ada transaksi pada periode ini.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-end">Qty beli</th>
                        <th class="text-end">Nominal beli</th>
                        <th class="text-end">Qty jual</th>
                        <th class="text-end">Nominal jual</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($breakdown_kategori as $b): ?>
                    <tr>
                        <td class="fw-medium"><?= html_escape($b['nama_kategori']) ?></td>
                        <td class="text-end modey"><?= angka($b['qty_beli'], 1) ?></td>
                        <td class="text-end money"><?= rupiah($b['nominal_beli']) ?></td>
                        <td class="text-end modey"><?= angka($b['qty_jual'], 1) ?></td>
                        <td class="text-end money"><?= rupiah($b['nominal_jual']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rekap harian -->
    <div class="panel">
        <div class="panel-header"><h2>Rekap harian</h2></div>
        <?php if (empty($rekap_harian)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-calendar-days"></i>
                Tidak ada transaksi pada periode ini.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-end">Total beli</th>
                        <th class="text-end">Total jual</th>
                        <th class="text-end">Laba harian</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rekap_harian as $h): ?>
                    <?php $laba_harian = $h['total_jual'] - $h['total_beli']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($h['tanggal'])) ?></td>
                        <td class="text-end money"><?= rupiah($h['total_beli']) ?></td>
                        <td class="text-end money"><?= rupiah($h['total_jual']) ?></td>
                        <td class="text-end money fw-semibold" style="color:<?= $laba_harian >= 0 ? 'var(--ok)' : 'var(--danger)' ?>;"><?= rupiah($laba_harian) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>