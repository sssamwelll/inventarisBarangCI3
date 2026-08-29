<div class="topbar">
    <div>
        <h1>Kas</h1>
        <span class="topbar-date">Buku kas — mutasi otomatis dari transaksi &amp; catatan manual</span>
    </div>
    <button type="button" class="btn btn-rust" onclick="catatKasManual()">
        <i class="fa-solid fa-plus"></i> Catat kas manual
    </button>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-door-open"></i> Saldo awal</div>
                <div class="stat-value money" style="font-size:19px;"><?= rupiah($saldo_awal) ?></div>
                <div class="stat-note">Per <?= date('d/m/Y', strtotime($filter['tanggal_dari'])) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-down-to-bracket"></i> Total masuk</div>
                <div class="stat-value money" style="font-size:19px; color:var(--ok);">
                    <?= rupiah(array_sum(array_map(function ($m) { return $m->tipe === 'masuk' ? (float) $m->jumlah : 0; }, $mutasi))) ?>
                </div>
                <div class="stat-note">Periode terpilih</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-arrow-up-from-bracket"></i> Total keluar</div>
                <div class="stat-value money" style="font-size:19px; color:var(--warn);">
                    <?= rupiah(array_sum(array_map(function ($m) { return $m->tipe === 'keluar' ? (float) $m->jumlah : 0; }, $mutasi))) ?>
                </div>
                <div class="stat-note">Periode terpilih</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-label"><i class="fa-solid fa-wallet"></i> Saldo akhir</div>
                <div class="stat-value money" style="font-size:19px;"><?= rupiah($saldo_akhir) ?></div>
                <div class="stat-note">Per <?= date('d/m/Y', strtotime($filter['tanggal_sampai'])) ?></div>
            </div>
        </div>
    </div>

    <div class="panel mb-3">
        <div class="panel-header"><h2>Filter</h2></div>
        <form method="get" action="<?= base_url('kas') ?>" class="row g-2 p-3">
            <div class="col-6 col-md-3">
                <label class="form-label small">Dari tanggal</label>
                <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="<?= html_escape($filter['tanggal_dari']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Sampai tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="<?= html_escape($filter['tanggal_sampai']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="transaksi" <?= $filter['kategori'] === 'transaksi' ? 'selected' : '' ?>>Transaksi</option>
                    <option value="gaji" <?= $filter['kategori'] === 'gaji' ? 'selected' : '' ?>>Gaji</option>
                    <option value="operasional" <?= $filter['kategori'] === 'operasional' ? 'selected' : '' ?>>Operasional</option>
                    <option value="lainnya" <?= $filter['kategori'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Tipe</label>
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="masuk" <?= $filter['tipe'] === 'masuk' ? 'selected' : '' ?>>Masuk</option>
                    <option value="keluar" <?= $filter['tipe'] === 'keluar' ? 'selected' : '' ?>>Keluar</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-rust btn-sm"><i class="fa-solid fa-filter"></i> Terapkan</button>
                <a href="<?= base_url('kas') ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Buku kas</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($mutasi) ?> mutasi</span>
        </div>

        <?php if (empty($mutasi)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-wallet"></i>
                Tidak ada mutasi kas pada periode/filter ini.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Dicatat oleh</th>
                        <th class="text-end">Masuk</th>
                        <th class="text-end">Keluar</th>
                        <th class="text-end">Saldo berjalan</th>
                        <th class="text-center" style="width:56px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mutasi as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($m->tanggal)) ?></td>
                        <td>
                            <span class="badge-status <?= $m->tipe === 'masuk' ? 'badge-tipe-jual' : 'badge-tipe-beli' ?>">
                                <?= ucfirst(html_escape($m->kategori)) ?>
                            </span>
                        </td>
                        <td><?= html_escape($m->keterangan) ?></td>
                        <td style="font-size:12px; color:var(--steel);"><?= html_escape($m->nama_user) ?></td>
                        <td class="text-end money" style="color:var(--ok);"><?= $m->tipe === 'masuk' ? rupiah($m->jumlah) : '—' ?></td>
                        <td class="text-end money" style="color:var(--warn);"><?= $m->tipe === 'keluar' ? rupiah($m->jumlah) : '—' ?></td>
                        <td class="text-end money fw-semibold"><?= rupiah($m->saldo_berjalan) ?></td>
                        <td class="text-center">
                            <?php if (empty($m->ref_type)): ?>
                                <a href="<?= base_url('kas/hapus/' . $m->id) ?>" class="btn btn-sm btn-outline-danger py-0 px-2"
                                   onclick="return confirm('Hapus mutasi kas manual ini?')" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:11px;" title="Otomatis dari <?= html_escape($m->ref_type) ?>">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
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

<!-- Modal Catat Kas Manual -->
<div class="modal fade" id="modalKas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; border-radius:10px;">
            <div class="modal-header" style="background-color: var(--paper-card); border-bottom: 1px solid var(--steel-line);">
                <h5 class="modal-title fw-semibold" style="font-size:15px;">Catat kas manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('kas/simpan') ?>" method="post">
                <div class="modal-body" style="background-color: var(--paper);">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Tipe</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipe" id="kas-tipe-masuk" value="masuk" checked>
                            <label class="btn btn-outline-secondary btn-sm" for="kas-tipe-masuk"><i class="fa-solid fa-arrow-down-to-bracket"></i> Masuk</label>

                            <input type="radio" class="btn-check" name="tipe" id="kas-tipe-keluar" value="keluar">
                            <label class="btn btn-outline-secondary btn-sm" for="kas-tipe-keluar"><i class="fa-solid fa-arrow-up-from-bracket"></i> Keluar</label>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Kategori</label>
                            <select name="kategori" class="form-select" required>
                                <option value="operasional">Operasional</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" required placeholder="Mis. Beli bensin operasional, sewa gudang, dll">
                    </div>

                    <div class="mb-1">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                            <input type="number" name="jumlah" class="form-control figure" required min="1" placeholder="0">
                        </div>
                    </div>
                    <div class="form-text" style="font-size:11px;">
                        Kategori "Transaksi" dan "Gaji" tercatat otomatis dari modul masing-masing, tidak lewat form ini.
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
    var modalKas = new bootstrap.Modal(document.getElementById('modalKas'));
    function catatKasManual() {
        modalKas.show();
    }
</script>