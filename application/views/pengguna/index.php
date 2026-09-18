<div class="topbar">
    <div>
        <h1>Manajemen Pengguna</h1>
        <span class="topbar-date">Kelola akun &amp; hak akses admin/owner</span>
    </div>
    <button type="button" class="btn btn-rust" onclick="tambahUser()">
        <i class="fa-solid fa-user-plus"></i> Tambah pengguna
    </button>
</div>

<div class="content-area">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success border-0"><?= html_escape($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>Daftar pengguna</h2>
            <span style="font-size:12px; color:var(--steel);"><?= count($daftar_user) ?> akun</span>
        </div>

        <div class="table-responsive">
            <table class="table table-rosok mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th class="text-center" style="width:220px;">Aksi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($daftar_user as $u): ?>
                    <tr>
                        <td class="fw-medium"><?= html_escape($u->nama) ?></td>
                        <td class="money"><?= html_escape($u->username) ?></td>
                        <td>
                            <?php
                                $badge_map = array('it_admin' => 'badge-tipe-beli', 'admin' => 'badge-tipe-jual', 'owner' => 'badge-lunas');
                                $label_map = array('it_admin' => 'IT Admin', 'admin' => 'Admin', 'owner' => 'Owner');
                            ?>
                            <span class="badge-status <?= $badge_map[$u->role] ?? '' ?>"><?= $label_map[$u->role] ?? ucfirst($u->role) ?></span>
                        </td>
                        <td class="text-center" colspan="2">
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit"
                                    onclick="editUser(<?= html_escape(json_encode(array('id' => $u->id, 'username' => $u->username, 'nama' => $u->nama, 'role' => $u->role))) ?>)">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <?php if ($u->role === 'it_admin'): ?>
                                <span class="btn btn-sm btn-outline-secondary py-1 px-2 disabled" title="IT admin selalu akses penuh, tidak perlu diatur">
                                    <i class="fa-solid fa-shield-halved"></i> Akses penuh
                                </span>
                            <?php else: ?>
                                <a href="<?= base_url('pengguna/akses/' . $u->id) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Kelola hak akses">
                                    <i class="fa-solid fa-sliders"></i> Kelola akses
                                </a>
                            <?php endif; ?>

                            <?php if ((int) $u->id !== (int) $this->session->userdata('user_id')): ?>
                                <a href="<?= base_url('pengguna/hapus/' . $u->id) ?>" class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-user" title="Hapus" data-delete-url="<?= base_url('pengguna/hapus/' . $u->id) ?>" data-user-name="<?= html_escape($u->nama) ?>" data-user-username="<?= html_escape($u->username) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Delete Pengguna -->
<div class="modal fade" id="modalDeleteUser" tabindex="-1"
     aria-labelledby="modalDeleteUserLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-delete">

            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteUserLabel">
                    <i class="fa-solid fa-trash-can me-2"></i>
                    Hapus Pengguna
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <p class="mb-2">
                    Apakah Anda yakin ingin menghapus pengguna:
                </p>

                <div class="delete-nota">
                    <i class="fa-solid fa-user me-2"></i>

                    <div>
                        <strong id="deleteUserName">-</strong>
                        <div id="deleteUserUsername" style="font-size:11px; opacity:.8;"></div>
                    </div>
                </div>

                <small class="delete-warning">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    Hak akses pengguna ini juga akan ikut terhapus.
                </small>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <a href="#"
                   id="btn-confirm-delete-user"
                   class="btn btn-delete-confirm">
                    <i class="fa-solid fa-trash me-1"></i>
                    Hapus
                </a>

            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Pengguna -->
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; border-radius:10px;">
            <div class="modal-header" style="background-color: var(--paper-card); border-bottom: 1px solid var(--steel-line);">
                <h5 class="modal-title fw-semibold" id="modalUserTitle" style="font-size:15px;">Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('Pengguna/simpan') ?>" method="post">
                <div class="modal-body" style="background-color: var(--paper);">
                    <input type="hidden" name="id" id="form-id">

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:500;">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="form-nama" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="form-username" class="form-control" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:13px; font-weight:500;">Role <span class="text-danger">*</span></label>
                            <select name="role" id="form-role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="it_admin">IT Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" style="font-size:13px; font-weight:500;">
                            Password <span class="text-danger" id="label-wajib-password">*</span>
                        </label>
                        <input type="password" name="password" id="form-password" class="form-control" autocomplete="new-password">
                        <div class="form-text" style="font-size:11px;" id="hint-password">Wajib diisi untuk pengguna baru.</div>
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
    function getModalUser() {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUser'));
    }

    function tambahUser() {
        document.getElementById('modalUserTitle').textContent = 'Tambah Pengguna';
        document.getElementById('form-id').value = '';
        document.getElementById('form-nama').value = '';
        document.getElementById('form-username').value = '';
        document.getElementById('form-role').value = 'admin';
        document.getElementById('form-password').value = '';
        document.getElementById('form-password').required = true;
        document.getElementById('label-wajib-password').style.display = 'inline';
        document.getElementById('hint-password').textContent = 'Wajib diisi untuk pengguna baru.';
        getModalUser().show();
    }

    function editUser(data) {
        document.getElementById('modalUserTitle').textContent = 'Update Pengguna';
        document.getElementById('form-id').value = data.id;
        document.getElementById('form-nama').value = data.nama;
        document.getElementById('form-username').value = data.username;
        document.getElementById('form-role').value = data.role;
        document.getElementById('form-password').value = '';
        document.getElementById('form-password').required = false;
        document.getElementById('label-wajib-password').style.display = 'none';
        document.getElementById('hint-password').textContent = 'Kosongkan kalau tidak ingin mengubah password.';
        getModalUser().show();
    }

    // MODAL DELETE USER
    $(document).on('click', '.btn-delete-user', function (e) {
        e.preventDefault();

        const url = $(this).data('delete-url');
        const nama = $(this).data('user-name');
        const username = $(this).data('user-username');

        $('#deleteUserName').text(nama);
        $('#deleteUserUsername').text('@' + username);

        $('#btn-confirm-delete-user').attr('href', url);

        const modalElement = document.getElementById('modalDeleteUser');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        modal.show();
    });
</script>