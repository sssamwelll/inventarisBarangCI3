<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('daftar_modul')) {
    /**
     * Daftar modul yang bisa diatur hak aksesnya. Tambahkan baris baru di sini
     * setiap kali ada modul baru di aplikasi -- ini satu-satunya tempat yang
     * perlu diupdate, tidak perlu migrasi database lagi.
     */
    function daftar_modul() {
        return array(
            'transaksi' => 'Transaksi',
            'barang'=> 'Barang',
            'kas' => 'Kas',
            'karyawan'=> 'Karyawan',
            'absensi' => 'Absensi',
            'gaji' => 'Gaji',
            'laporan' => 'Laporan',
        );
    }
}

if (!function_exists('has_akses')) {
    /**
     * Cek apakah user yang sedang login boleh melakukan $aksi di $modul.
     * $aksi salah satu dari: 'create', 'read', 'update', 'delete'.
     *
     * IT admin selalu TRUE untuk semua modul -- dialah yang mengatur orang lain,
     * jadi tidak perlu (dan tidak masuk akal) dibatasi oleh matrix yang sama.
     */
    function has_akses($modul, $aksi) {
        $CI = &get_instance();

        if ($CI->session->userdata('role') === 'it_admin') {
            return true;
        }

        $permissions = $CI->session->userdata('permissions');
        if (empty($permissions) || !isset($permissions[$modul])) {
            return false;
        }

        $key = 'can_' . $aksi;
        return !empty($permissions[$modul][$key]);
    }
}

if (!function_exists('cek_akses')) {
    /**
     * Versi "penjaga gerbang" dari has_akses() -- dipanggil di AWAL controller/method.
     * Kalau tidak berhak, langsung dialihkan ke dashboard dengan pesan error,
     * dan kode setelah pemanggilan ini TIDAK akan lanjut (karena redirect() = exit).
     */
    function cek_akses($modul, $aksi)
    {
        if (!has_akses($modul, $aksi)) {
            $CI = &get_instance();
            $CI->session->set_flashdata('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
            redirect('Dashboard');
        }
    }
}