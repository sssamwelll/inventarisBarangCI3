<div class="topbar">
    <div>
        <h1>Hak Akses — <?= html_escape($user->nama) ?></h1>
        <span class="topbar-date">Centang aksi yang boleh dilakukan pengguna ini di tiap modul</span>
    </div>
    <a href="<?= base_url('pengguna') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('Pengguna/simpan_akses/' . $user->id) ?>">
        <div class="panel">
            <div class="panel-header">
                <h2>Matrix hak akses</h2>
                <span class="badge-status badge-tipe-jual"><?= ucfirst($user->role) ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-rosok mb-0">
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th class="text-center">Create<br><span style="font-weight:400; font-size:10.5px; color:var(--steel);">(tambah data)</span></th>
                            <th class="text-center">Read<br><span style="font-weight:400; font-size:10.5px; color:var(--steel);">(lihat/buka)</span></th>
                            <th class="text-center">Update<br><span style="font-weight:400; font-size:10.5px; color:var(--steel);">(ubah data)</span></th>
                            <th class="text-center">Delete<br><span style="font-weight:400; font-size:10.5px; color:var(--steel);">(hapus data)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daftar_modul as $kode => $nama): ?>
                        <?php $a = $akses[$kode]; ?>
                        <tr>
                            <td class="fw-medium"><?= html_escape($nama) ?></td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="akses[<?= $kode ?>][can_create]" value="1" <?= $a['can_create'] ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="akses[<?= $kode ?>][can_read]" value="1" <?= $a['can_read'] ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="akses[<?= $kode ?>][can_update]" value="1" <?= $a['can_update'] ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="akses[<?= $kode ?>][can_delete]" value="1" <?= $a['can_delete'] ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-text mb-3" style="font-size:11.5px;">
            Catatan: kalau "Read" pada suatu modul tidak dicentang, pengguna ini tidak bisa membuka halaman modul itu sama sekali —
            tombol/menu terkait di sidebar juga tidak akan tampil untuknya.
        </div>

        <button type="submit" class="btn btn-rust">
            <i class="fa-solid fa-floppy-disk"></i> Simpan hak akses
        </button>
    </form>
</div>