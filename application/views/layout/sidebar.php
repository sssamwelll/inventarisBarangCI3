<?php
// Helper kecil untuk class aktif menu
function menu_active($current, $key) {
    return $current === $key ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <p class="brand-title">SUR App</p>
        <span class="brand-sub"><?= $this->session->userdata('nama')?></span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu utama</div>
        <a href="<?= site_url('Dashboard') ?>" class="nav-link <?= menu_active($active_menu, 'dashboard') ?>" accesskey="1">
            <i class="fa-solid fa-gauge"></i> Dashboard <span class="nav-key">alt+1</span>
        </a>

        <?php if (has_akses('transaksi', 'read')): ?>
            <a href="<?= site_url('Transaksi') ?>" class="nav-link <?= menu_active($active_menu, 'transaksi') ?>" accesskey="2">
                <i class="fa-solid fa-right-left"></i> Transaksi <span class="nav-key">alt+2</span>
            </a>
        <?php endif;?>

        <?php if (has_akses('barang', 'read')): ?>
            <a href="<?= site_url('Barang') ?>" class="nav-link <?= menu_active($active_menu, 'barang') ?>" accesskey="3">
                <i class="fa-solid fa-boxes-stacked"></i> Barang <span class="nav-key">alt+3</span>
            </a>
        <?php endif;?>

        <a href="<?= site_url('Kas') ?>" class="nav-link <?= menu_active($active_menu, 'kas') ?>" accesskey="4">
            <i class="fa-solid fa-wallet"></i> Kas <span class="nav-key">alt+4</span>
        </a>

        <div class="nav-section-label">Karyawan</div>
        <?php if (has_akses('karyawan', 'read')):?>
            <a href="<?= base_url('Karyawan') ?>" class="nav-link <?= menu_active($active_menu, 'karyawan') ?>" accesskey="5">
                <i class="fa-solid fa-users"></i> Data karyawan <span class="nav-key">alt+5</span>
            </a>
        <?php endif;?>

        <?php if (has_akses('absensi', 'read')):?>
            <a href="<?= base_url('Absensi/riwayat') ?>" class="nav-link <?= menu_active($active_menu, 'absensi') ?>" accesskey="6">
                <i class="fa-solid fa-calendar-check"></i> Absensi &amp; lembur <span class="nav-key">alt+6</span>
            </a>
            <?php elseif (has_akses('absensi', 'create')): ?>
                <a href="<?= base_url('Absensi') ?>" class="nav-link <?= menu_active($active_menu, 'absensi') ?>" accesskey="6">
                    <i class="fa-solid fa-calendar-check"></i> Absensi &amp; lembur <span class="nav-key">alt+6</span>
                </a>
        <?php endif; ?>

        <a href="<?= base_url('gaji') ?>" class="nav-link <?= menu_active($active_menu, 'gaji') ?>" accesskey="7">
            <i class="fa-solid fa-money-check-dollar"></i> Penggajian <span class="nav-key">alt+7</span>
        </a>

        <div class="nav-section-label">Laporan</div>
        <a href="<?= base_url('laporan') ?>" class="nav-link <?= menu_active($active_menu, 'laporan') ?>" accesskey="8">
            <i class="fa-solid fa-chart-column"></i> Laporan <span class="nav-key">alt+8</span>
        </a>

        <?php if ($this->session->userdata('role') === 'it_admin'): ?>
            <div class="nav-section-label">Administrasi</div>
            <a href="<?= base_url('pengguna') ?>" class="nav-link <?= menu_active($active_menu, 'pengguna') ?>">
                <i class="fa-solid fa-user-shield"></i> Kelola Pengguna
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= base_url('Auth/logout') ?>" class="nav-link px-0" style="color:#C7C3B8;">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar
        </a>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="main-wrapper">
