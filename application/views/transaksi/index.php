<div class="topbar">
    <button type="button" id="btn-sidebar-toggle" class="btn d-md-none me-3" style="background:none; border:none; padding:0; color:var(--ink); font-size:20px;">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <h1><?= $is_edit ? 'Edit Transaksi' : 'Transaksi Baru' ?></h1>
        <span class="topbar-date">Riwayat transaksi beli dan jual</span>
    </div>
    <?php if (has_akses('transaksi', 'create')): ?>
    <a href="<?= base_url('Transaksi/tambah') ?>" id="btn-transaksi-baru" class="btn btn-rust">
        <i class="fa-solid fa-plus"></i> Transaksi baru <kbd>n</kbd>
    </a>
    <?php endif; ?>
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
                        <th style="width:36px;"></th>
                        <th>No nota</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Nama pihak</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $t): ?>
                        <tr>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-expand-nota py-0 px-2"
                                        data-id="<?= $t->id ?>"
                                        data-bs-toggle="collapse" data-bs-target="#detail-nota-<?= $t->id ?>">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </td>
                            <td><?= html_escape($t->no_nota) ?></td>
                            <td><?= date('d/m/Y', strtotime($t->tanggal)) ?></td>
                            <td>
                                <span class="badge-status <?= $t->tipe === 'beli' ? 'badge-tipe-beli' : 'badge-tipe-jual' ?>">
                                    <?= ucfirst($t->tipe) ?>
                                </span>
                            </td>
                            <td><?= html_escape($t->nama_pihak) ?></td>
                            <td><span class="badge-status badge-<?= $t->status_bayar ?>"><?= ucfirst($t->status_bayar) ?></span></td>
                            <td class="text-end money"><?= rupiah($t->total - $t->potongan) ?></td>
                            <td class="text-center">
                                <?php if (has_akses('transaksi', 'print')):?>
                                    <a href="<?= base_url('Transaksi/cetak/' . $t->id) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" target="_blank" title="Cetak nota">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                <?php endif;?>
                                <?php if (has_akses('transaksi', 'update')): ?>
                                    <a href="<?= base_url('Transaksi/edit/' . $t->id) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit nota">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (has_akses('transaksi', 'delete')): ?>
                                    <a href="<?= base_url('Transaksi/hapus/' . $t->id) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-transaksi" title="Hapus nota"
                                    data-delete-url="<?= base_url('Transaksi/hapus/' . $t->id) ?>" data-no-nota="<?= html_escape($t->no_nota) ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="100" class="p-0" style="border-top:none;">
                                <div class="collapse" id="detail-nota-<?= $t->id ?>">
                                    <div class="p-3" style="background-color: var(--paper);">
                                        <div class="detail-nota-content" data-loaded="0">
                                            <div class="text-center text-muted py-2" style="font-size:12px;">
                                                <i class="fa-solid fa-spinner fa-spin"></i> Memuat detail...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($total_rows > 0): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-top:1px solid var(--steel-line);">
                    <span style="font-size:12px; color:var(--steel);">
                        Menampilkan <?= count($transaksi) ?> dari <?= $total_rows ?> transaksi
                    </span>
                    <?= $pagination_links ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- MODAL DELETE TRANSAKSI -->
    <div class="modal fade" id="modalDeleteTransaksi" tabindex="-1"
        aria-labelledby="modalDeleteTransaksiLabel" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modal-delete">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDeleteTransaksiLabel">
                        <i class="fa-solid fa-trash-can me-2"></i>
                        Hapus Transaksi
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-2">
                        Apakah Anda yakin ingin menghapus transaksi:
                    </p>

                    <div class="delete-nota">
                        <i class="fa-solid fa-receipt me-2"></i>
                        <strong id="deleteNoNota">-</strong>
                    </div>

                    <small class="delete-warning">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        Stok dan kas terkait akan otomatis dibalik.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <a href="#"
                    id="btn-confirm-delete-transaksi"
                    class="btn btn-delete-confirm">
                        <i class="fa-solid fa-trash me-1"></i>
                        Hapus
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // TABEL DETAIL NOTA
        var URL_TRANSAKSI_DETAIL = '<?= base_url('Transaksi/detail_ajax') ?>';

        (function () {
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str == null ? '' : String(str);
                return div.innerHTML;
            }

            function formatRp(n) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            }

            document.querySelectorAll('.btn-expand-nota').forEach(function (btn) {
                var targetSelector = btn.getAttribute('data-bs-target');
                var collapseEl = document.querySelector(targetSelector);
                var contentEl = collapseEl.querySelector('.detail-nota-content');
                var icon = btn.querySelector('i');
                var transaksiId = btn.dataset.id;

                collapseEl.addEventListener('show.bs.collapse', function () {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');

                    if (contentEl.dataset.loaded === '1') {
                        return; // sudah pernah dimuat, tidak perlu fetch ulang tiap dibuka-tutup
                    }

                    fetch(URL_TRANSAKSI_DETAIL + '/' + transaksiId)
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (!data.success) {
                                contentEl.innerHTML = '<div class="text-danger" style="font-size:12px;">Gagal memuat detail.</div>';
                                return;
                            }

                            var html = '<table class="table table-sm mb-2" style="font-size:12.5px;">';
                            html += '<thead><tr><th>Barang</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead><tbody>';

                            data.items.forEach(function (item) {
                                html += '<tr>' +
                                    '<td>' + escapeHtml(item.nama_barang) + '</td>' +
                                    '<td class="text-end">' + item.qty + ' ' + escapeHtml(item.satuan) + '</td>' +
                                    '<td class="text-end">' + formatRp(item.harga_satuan) + '</td>' +
                                    '<td class="text-end">' + formatRp(item.subtotal) + '</td>' +
                                '</tr>';
                            });
                            html += '</tbody></table>';

                            html += '<div class="d-flex justify-content-end gap-4" style="font-size:12.5px;">';
                            html += '<span>Total barang: <strong>' + formatRp(data.total) + '</strong></span>';
                            if (data.potongan > 0) {
                                html += '<span style="color:var(--warn);">Potongan: <strong>-' + formatRp(data.potongan) + '</strong></span>';
                            }
                            html += '<span>Total dibayar: <strong>' + formatRp(data.total_bayar) + '</strong></span>';
                            html += '</div>';

                            contentEl.innerHTML = html;
                            contentEl.dataset.loaded = '1';
                        })
                        .catch(function () {
                            contentEl.innerHTML = '<div class="text-danger" style="font-size:12px;">Gagal memuat detail.</div>';
                        });
                });

                collapseEl.addEventListener('hide.bs.collapse', function () {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                });
            });
        })();

        // MODAL DELETE TRANSAKSI
        $(document).on('click', '.btn-delete-transaksi', function (e) {
            e.preventDefault();

            var url = $(this).data('delete-url');
            var noNota = $(this).data('no-nota');

            $('#deleteNoNota').text(noNota);
            $('#btn-confirm-delete-transaksi').attr('href', url);

            var modalElement = document.getElementById('modalDeleteTransaksi');
            var modal = bootstrap.Modal.getOrCreateInstance(modalElement);

            modal.show();
        });
    </script>
</div>