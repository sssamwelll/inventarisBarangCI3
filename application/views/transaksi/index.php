<div class="topbar">
    <div>
        <h1>Transaksi</h1>
        <span class="topbar-date">Riwayat transaksi beli dan jual</span>
    </div>
    <a href="<?= base_url('transaksi/tambah') ?>" id="btn-transaksi-baru" class="btn btn-rust">
        <i class="fa-solid fa-plus"></i> Transaksi baru <kbd>n</kbd>
    </a>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0">
            <?= html_escape($this->session->flashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0">
            <?= html_escape($this->session->flashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>Daftar transaksi terbaru</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($transaksi) ?> data</span>
        </div>

        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>No nota</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Nama pihak</th>
                        <th>Status bayar</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-4">
                                    <i class="fa-solid fa-receipt"></i>
                                    Belum ada transaksi. Buat transaksi pertama dari tombol di kanan atas.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksi as $row): ?>
                            <tr>
                                <td><?= html_escape($row->no_nota) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row->tanggal)) ?></td>
                                <td>
                                    <span class="badge-status <?= $row->tipe === 'beli' ? 'badge-tipe-beli' : 'badge-tipe-jual' ?>">
                                        <?= ucfirst(html_escape($row->tipe)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= html_escape($row->nama_pihak) ?></div>
                                    <?php if (!empty($row->nama_user)): ?>
                                        <div style="font-size:11px; color:var(--steel);">Input: <?= html_escape($row->nama_user) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= html_escape($row->status_bayar) ?>">
                                        <?= ucfirst(html_escape($row->status_bayar)) ?>
                                    </span>
                                </td>
                                <td class="text-end money"><?= rupiah($row->total) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('Transaksi/cetak/' . $row->id) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak nota">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
