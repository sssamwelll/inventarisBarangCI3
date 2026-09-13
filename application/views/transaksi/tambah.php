<div class="topbar">
    <button type="button" id="btn-sidebar-toggle" class="btn d-md-none me-3" style="background:none; border:none; padding:0; color:var(--ink); font-size:20px;">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div>
        <h1>Transaksi Baru</h1>
        <span class="topbar-date">Isi barang, harga, lalu simpan nota</span>
    </div>
    <a href="<?= base_url('transaksi') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="content-area">
    <div id="area-notifikasi"></div>

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

    <form action="<?= base_url('Transaksi/simpan') ?>" method="post" id="form-transaksi">
        <div class="row g-3">
            <div class="col-lg-5">
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
                            <input type="text" name="nama_pihak" id="input-nama-pihak" class="form-control" value="<?= set_value('nama_pihak') ?>" placeholder="Nama penjual / pembeli" autocomplete="off">
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

                        <div class="mb-3">
                            <label class="form-label">Potongan <span class="text-muted">(opsional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                                <input type="number" name="potongan" id="input-potongan" class="form-control figure" min="0" value="<?= set_value('potongan', '0') ?>" placeholder="0">
                            </div>
                            <div class="form-text" style="font-size:11px;">Misalnya untuk memotong bon/pinjaman lama pihak ini dari uang yang dibayarkan di transaksi ini.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan potongan <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="catatan_potongan" class="form-control" value="<?= set_value('catatan_potongan') ?>" placeholder="Mis. Potong bon tanggal 10 Agustus">
                        </div>

                        <div class="alert alert-light border mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="color:var(--steel);">Total sementara</span>
                                <strong class="money" id="grand-total">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1" id="baris-potongan" style="display:none;">
                                <span style="color:var(--warn); font-size:13px;">Potongan</span>
                                <strong class="money" id="tampil-potongan" style="color:var(--warn); font-size:13px;">- Rp 0</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="color:var(--ink); font-weight:500;">Total dibayar/diterima</span>
                                <strong class="money" id="total-bersih" style="font-size:16px;">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
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
                                            <div class="position-relative">
                                                <input type="text" class="form-control barang-search-input" placeholder="Cari nama barang..." autocomplete="off">
                                                <select name="barang_id[]" class="form-select barang-select visually-hidden" tabindex="-1">
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
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" name="qty[]" class="form-control qty-input" value="<?= html_escape($row['qty']) ?>" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" name="harga_satuan[]" class="form-control harga-input" value="<?= html_escape($row['harga_satuan']) ?>" placeholder="0">
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
<!-- 
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="<?= base_url('transaksi') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-rust" target="_blank">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan transaksi
                    </button>
                </div> -->

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalBatal">
                        <i class="fa-solid fa-floppy-disk"></i> Batal
                    </button>
                    <button type="button" class="btn btn-rust" data-bs-toggle="modal" data-bs-target="#modalNota">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Simpan Nota -->
        <div class="modal fade" id="modalNota" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-0 shadow-md">
                    
                    <!-- Header dengan gradient & icon -->
                    <div class="modal-header bg-rust text-white border-0 rounded-top">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                <i class="bi bi-receipt-cutoff fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Simpan Nota Transaksi</h5>
                                <small class="text-white-50" type="hidden"><?= $nota?></small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body dengan ringkasan transaksi -->
                    <div class="modal-body p-4">
                        <!-- Alert informasi -->
                        <div class="alert bg-light d-flex align-items-center border-0" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                Pastikan semua data transaksi sudah benar sebelum menyimpan nota. 
                                Nota yang telah disimpan tidak dapat diubah atau ditambah
                            </div>
                        </div>
                    </div>

                    <!-- Footer dengan aksi -->
                    <div class="modal-footer border-0 justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </button>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-rust px-4">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal Batal -->
        <div class="modal fade" id="modalBatal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-0 shadow-md">
                    
                    <!-- Header dengan gradient & icon -->
                    <div class="modal-header bg-danger text-white border-0 rounded-top">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-25 rounded-circle p-2">
                                <i class="bi bi-receipt-cutoff fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Batalkan Nota?</h5>
                                <small class="text-white-50"></small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body dengan ringkasan transaksi -->
                    <div class="modal-body p-4">
                        <!-- Alert informasi -->
                        <div class="alert bg-light d-flex align-items-center border-0" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                Apakah anda yakin ingin membatalkan nota yang baru dibuat? ini akan mereset yang baru saja anda catat.
                            </div>
                        </div>
                    </div>

                    <!-- Footer dengan aksi -->
                    <div class="modal-footer border-0 justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </button>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('Transaksi/tambah') ?>" type="button" class="btn btn-danger px-4">
                                <i class="bi bi-check2-circle me-1"></i> Ya reset
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>


<template id="template-baris-detail">
    <tr class="detail-row">
        <td>
            <div class="position-relative">
                <input type="text" class="form-control barang-search-input" placeholder="Cari nama barang..." autocomplete="off">
                <select name="barang_id[]" class="form-select barang-select visually-hidden" tabindex="-1">
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
            </div>
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

<script src="<?= base_url('assets/js/script.js')?>"></script>

<!-- Daftar Barang --- Searcnign-->
<script>
    var DAFTAR_BARANG = [
        <?php foreach ($barang_list as $b): ?>
        {
            id: <?= (int) $b->id ?>,
            nama: '<?= addslashes($b->nama_barang) ?>',
            label: '<?= addslashes($b->nama_barang) ?> (<?= addslashes($b->satuan) ?>)',
            hargaBeli: <?= (float) $b->harga_beli ?>,
            hargaJual: <?= (float) $b->harga_jual ?>,
            satuan: '<?= addslashes($b->satuan) ?>'
        },
        <?php endforeach; ?>
    ];

    var URL_TRANSAKSI_SIMPAN = '<?= base_url('Transaksi/simpan')?>';
    var URL_TRANSAKSI_TAMBAH = '<?= base_url('Transaksi/tambah')?>';

</script>

<!-- AUTOCOMPLETE NAMA PIHAKA -->
<script>
    $(function () {
        var TIPE_SELECT = document.getElementById('tipe-transaksi');
        var NAMA_PIHAK_INPUT = document.getElementById('input-nama-pihak');
        var TABEL_DETAIL = document.getElementById('tabel-detail-transaksi');
        var URL_CARI_NAMA = '<?= base_url("transaksi/cari_nama_pihak") ?>';
        var URL_CEK_HARGA = '<?= base_url("transaksi/cek_harga_langganan") ?>';

        // Autocomplete nama pihak dari riwayat transaksi sebelumnya.
        $(NAMA_PIHAK_INPUT).autocomplete({
            source: URL_CARI_NAMA,
            minLength: 2,
            select: function () {
                setTimeout(cekHargaSemuaBaris, 30);
            }
        });

        function tandaiHargaLangganan(hargaInput, adaLangganan) {
            var sel = hargaInput.closest('td');
            var label = sel.querySelector('.label-harga-langganan');

            if (!adaLangganan) {
                if (label) label.remove();
                return;
            }

            if (!label) {
                label = document.createElement('div');
                label.className = 'label-harga-langganan';
                sel.appendChild(label);
            }
            label.textContent = 'Harga langganan orang ini';
        }

        function cekHargaBaris(row) {
            if (!row) return;

            var namaPihak = NAMA_PIHAK_INPUT.value.trim();
            var select = row.querySelector('.barang-select');
            var barangId = select ? select.value : '';
            var hargaInput = row.querySelector('.harga-input');

            if (!namaPihak || !barangId) {
                tandaiHargaLangganan(hargaInput, false);
                return;
            }

            $.getJSON(URL_CEK_HARGA, {
                nama_pihak: namaPihak,
                barang_id: barangId,
                tipe: TIPE_SELECT.value
            }, function (res) {
                if (res && res.ada) {
                    hargaInput.value = Math.round(res.harga_satuan);
                    // Trigger event 'input' supaya recalculateAll() bawaan ikut jalan
                    hargaInput.dispatchEvent(new Event('input', { bubbles: true }));
                    tandaiHargaLangganan(hargaInput, true);
                } else {
                    tandaiHargaLangganan(hargaInput, false);
                }
            });
        }

        function cekHargaSemuaBaris() {
            TABEL_DETAIL.querySelectorAll('.detail-row').forEach(cekHargaBaris);
        }

        // Delegasi event di parent tabel -- otomatis berlaku juga untuk baris
        // yang ditambahkan belakangan lewat tombol "Tambah baris"
        TABEL_DETAIL.addEventListener('change', function (e) {
            if (e.target.classList.contains('barang-select')) {
                cekHargaBaris(e.target.closest('tr'));
            }
        });

        NAMA_PIHAK_INPUT.addEventListener('blur', cekHargaSemuaBaris);
    });
</script>