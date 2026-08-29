<div class="topbar">
    <div>
        <h1>Master Barang & Harga</h1>
        <span class="topbar-date">Kelola jenis rosok dan perbarui harga harian</span>
    </div>
    <button type="button" class="btn btn-rust" onclick="tambahBarang()">
        <i class="fa-solid fa-plus"></i> Tambah barang
    </button>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0">
            <?= html_escape($this->session->flashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0">
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>Daftar Harga & Stok</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($barang) ?> item aktif</span>
        </div>

        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Nama Barang</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Stok Tersedia</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barang)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-4">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    Data barang masih kosong. Klik "Tambah barang".
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang as $b): ?>
                            <tr>
                                <td><?= html_escape($b->nama_kategori) ?></td>
                                <td class="fw-medium"><?= html_escape($b->nama_barang) ?></td>
                                <td class="text-end money text-danger"><?= rupiah($b->harga_beli) ?> /<?= html_escape($b->satuan) ?></td>
                                <td class="text-end money text-success"><?= rupiah($b->harga_jual) ?> /<?= html_escape($b->satuan) ?></td>
                                <td class="text-end fw-bold">
                                    <?= angka($b->stok, 1) ?> <span style="font-size:11px; font-weight:400; color:var(--steel);"> <?= html_escape($b->satuan) ?></span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2" 
                                            title="Edit/Update Harga"
                                            onclick="editBarang(<?= html_escape(json_encode($b)) ?>)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('Barang/hapus/' . $b->id) ?>" 
                                       class="btn btn-sm btn-outline-danger py-1 px-2" 
                                       title="Hapus Barang"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini? Data stok dan nota tidak akan terpengaruh.')">
                                        <i class="fa-solid fa-trash"></i>
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

<!-- Modal Form Barang -->
<div class="modal fade" id="modalBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; border-radius:10px;">
            <div class="modal-header" style="background-color: var(--paper-card); border-bottom: 1px solid var(--steel-line);">
                <h5 class="modal-title fw-semibold" id="modalBarangTitle" style="font-size: 15px;">Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('barang/simpan') ?>" method="post">
                <div class="modal-body" style="background-color: var(--paper);">
                    <!-- Hidden ID untuk Edit -->
                    <input type="hidden" name="id" id="form-id">
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_id" id="form-kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $k): ?>
                                <option value="<?= $k->id ?>"><?= html_escape($k->nama_kategori) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="form-nama" class="form-control" required placeholder="Mis. Kardus Bekas">
                        </div>
                        <div class="col-4">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="form-satuan" class="form-control" required value="kg" placeholder="kg/pcs">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-danger" style="font-size:13px; font-weight:500;">Harga Beli <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                                <input type="number" name="harga_beli" id="form-beli" class="form-control figure" required placeholder="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-success" style="font-size:13px; font-weight:500;">Harga Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="font-family: var(--font-mono);">Rp</span>
                                <input type="number" name="harga_jual" id="form-jual" class="form-control figure" required placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: var(--paper-card); border-top: 1px solid var(--steel-line);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-rust"><i class="fa-solid fa-floppy-disk"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var modalBarang = new bootstrap.Modal(document.getElementById('modalBarang'));

    function tambahBarang() {
        document.getElementById('modalBarangTitle').textContent = 'Tambah Barang Baru';
        document.getElementById('form-id').value = '';
        document.getElementById('form-kategori').value = '';
        document.getElementById('form-nama').value = '';
        document.getElementById('form-satuan').value = 'kg';
        document.getElementById('form-beli').value = '';
        document.getElementById('form-jual').value = '';
        modalBarang.show();
    }

    function editBarang(data) {
        document.getElementById('modalBarangTitle').textContent = 'Update Barang & Harga';
        document.getElementById('form-id').value = data.id;
        document.getElementById('form-kategori').value = data.kategori_id;
        document.getElementById('form-nama').value = data.nama_barang;
        document.getElementById('form-satuan').value = data.satuan;
        document.getElementById('form-beli').value = Math.round(data.harga_beli);
        document.getElementById('form-jual').value = Math.round(data.harga_jual);
        modalBarang.show();
    }
</script>