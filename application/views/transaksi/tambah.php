<div class="topbar">
    <div>
        <h1>Transaksi Baru</h1>
        <span class="topbar-date">Isi barang, harga, lalu simpan nota</span>
    </div>
    <a href="<?= base_url('transaksi') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="content-area">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger border-0">
            <?= validation_errors() ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0">
            <?= html_escape($this->session->flashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('transaksi/simpan') ?>" method="post" id="form-transaksi">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="panel h-100">
                    <div class="panel-header">
                        <h2>Data transaksi</h2>
                    </div>
                    <div class="p-3 p-lg-4">
                        <div class="mb-3">
                            <label class="form-label">Tipe transaksi</label>
                            <select name="tipe" id="tipe-transaksi" class="form-select" data-autofocus>
                                <option value="beli" <?= set_select('tipe', 'beli', true) ?>>Beli</option>
                                <option value="jual" <?= set_select('tipe', 'jual') ?>>Jual</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama pihak</label>
                            <input type="text" name="nama_pihak" class="form-control" value="<?= set_value('nama_pihak') ?>" placeholder="Nama penjual / pembeli">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= set_value('no_hp') ?>" placeholder="Opsional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status bayar</label>
                            <select name="status_bayar" class="form-select">
                                <option value="lunas" <?= set_select('status_bayar', 'lunas', true) ?>>Lunas</option>
                                <option value="hutang" <?= set_select('status_bayar', 'hutang') ?>>Hutang</option>
                                <option value="piutang" <?= set_select('status_bayar', 'piutang') ?>>Piutang</option>
                            </select>
                        </div>

                        <div class="alert alert-light border mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="color:var(--steel);">Total sementara</span>
                                <strong class="money" id="grand-total">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Detail barang</h2>
                        <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus"></i> Tambah baris
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-rosok mb-0 align-middle" id="tabel-detail-transaksi">
                            <thead>
                                <tr>
                                    <th style="min-width: 260px;">Barang</th>
                                    <th style="width: 120px;">Qty</th>
                                    <th style="width: 160px;">Harga satuan</th>
                                    <th style="width: 160px;">Subtotal</th>
                                    <th style="width: 56px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($form_rows as $row): ?>
                                    <tr class="detail-row">
                                        <td>
                                            <select name="barang_id[]" class="form-select barang-select">
                                                <option value="">Pilih barang</option>
                                                <?php foreach ($barang_list as $barang): ?>
                                                    <option value="<?= (int) $barang->id ?>"
                                                        data-harga-beli="<?= (float) $barang->harga_beli ?>"
                                                        data-harga-jual="<?= (float) $barang->harga_jual ?>"
                                                        data-satuan="<?= html_escape($barang->satuan) ?>"
                                                        data-stok="<?= (float) $barang->stok ?>"
                                                        <?= (string) $row['barang_id'] === (string) $barang->id ? 'selected' : '' ?>>
                                                        <?= html_escape(($barang->nama_kategori ? $barang->nama_kategori . ' - ' : '') . $barang->nama_barang) ?>
                                                        <?php if (!empty($barang->satuan)): ?>
                                                            (<?= html_escape($barang->satuan) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="qty[]" class="form-control qty-input" value="<?= html_escape($row['qty']) ?>" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="harga_satuan[]" class="form-control harga-input" value="<?= html_escape($row['harga_satuan']) ?>" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control subtotal-output money" value="Rp 0" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus baris">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="money" id="tfoot-total">Rp 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="<?= base_url('transaksi') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-rust">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan transaksi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="template-baris-detail">
    <tr class="detail-row">
        <td>
            <select name="barang_id[]" class="form-select barang-select">
                <option value="">Pilih barang</option>
                <?php foreach ($barang_list as $barang): ?>
                    <option value="<?= (int) $barang->id ?>"
                        data-harga-beli="<?= (float) $barang->harga_beli ?>"
                        data-harga-jual="<?= (float) $barang->harga_jual ?>"
                        data-satuan="<?= html_escape($barang->satuan) ?>"
                        data-stok="<?= (float) $barang->stok ?>">
                        <?= html_escape(($barang->nama_kategori ? $barang->nama_kategori . ' - ' : '') . $barang->nama_barang) ?>
                        <?php if (!empty($barang->satuan)): ?>
                            (<?= html_escape($barang->satuan) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="qty[]" class="form-control qty-input" placeholder="0">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="harga_satuan[]" class="form-control harga-input" placeholder="0">
        </td>
        <td>
            <input type="text" class="form-control subtotal-output money" value="Rp 0" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus baris">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </td>
    </tr>
</template>

<script>
(function () {
    var tableBody = document.querySelector('#tabel-detail-transaksi tbody');
    var addRowButton = document.getElementById('btn-tambah-baris');
    var typeSelect = document.getElementById('tipe-transaksi');
    var template = document.getElementById('template-baris-detail');

    function formatRupiah(number) {
        var value = Number(number || 0);
        return 'Rp ' + value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function getSelectedPrice(row) {
        var select = row.querySelector('.barang-select');
        var selected = select.options[select.selectedIndex];
        if (!selected) {
            return 0;
        }

        var hargaBeli = parseFloat(selected.dataset.hargaBeli || '0') || 0;
        var hargaJual = parseFloat(selected.dataset.hargaJual || '0') || 0;
        return typeSelect.value === 'jual' ? hargaJual : hargaBeli;
    }

    function recalculateRow(row) {
        var qtyInput = row.querySelector('.qty-input');
        var hargaInput = row.querySelector('.harga-input');
        var subtotalOutput = row.querySelector('.subtotal-output');
        var qty = parseFloat(qtyInput.value || '0') || 0;
        var harga = parseFloat(hargaInput.value || '0') || 0;
        var subtotal = qty * harga;

        subtotalOutput.value = formatRupiah(subtotal);
        return subtotal;
    }

    function recalculateAll() {
        var total = 0;
        tableBody.querySelectorAll('.detail-row').forEach(function (row) {
            total += recalculateRow(row);
        });

        document.getElementById('grand-total').textContent = formatRupiah(total);
        document.getElementById('tfoot-total').textContent = formatRupiah(total);
    }

    function bindRow(row) {
        var select = row.querySelector('.barang-select');
        var qtyInput = row.querySelector('.qty-input');
        var hargaInput = row.querySelector('.harga-input');
        var removeButton = row.querySelector('.btn-hapus-baris');

        select.addEventListener('change', function () {
            hargaInput.value = getSelectedPrice(row) || '';
            recalculateAll();
        });

        qtyInput.addEventListener('input', recalculateAll);
        hargaInput.addEventListener('input', recalculateAll);

        removeButton.addEventListener('click', function () {
            if (tableBody.querySelectorAll('.detail-row').length === 1) {
                select.value = '';
                qtyInput.value = '';
                hargaInput.value = '';
                recalculateAll();
                return;
            }

            row.remove();
            recalculateAll();
        });
    }

    function createRow() {
        var row = template.content.firstElementChild.cloneNode(true);
        bindRow(row);
        return row;
    }

    addRowButton.addEventListener('click', function () {
        tableBody.appendChild(createRow());
    });

    tableBody.querySelectorAll('.detail-row').forEach(function (row) {
        bindRow(row);
    });

    typeSelect.addEventListener('change', function () {
        tableBody.querySelectorAll('.detail-row').forEach(function (row) {
            var select = row.querySelector('.barang-select');
            var hargaInput = row.querySelector('.harga-input');
            if (select.value) {
                hargaInput.value = getSelectedPrice(row) || hargaInput.value || '';
            }
        });
        recalculateAll();
    });

    recalculateAll();
})();
</script>
