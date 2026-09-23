<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengguna_model extends CI_Model
{
    public function get_all_users() {
        return $this->db->order_by('role', 'ASC')->order_by('nama', 'ASC')->get('users')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get('users')->row();
    }

    public function get_by_username($username) {
        return $this->db->where('username', $username)->get('users')->row();
    }

    public function simpan_user($data) {
        return $this->db->insert('users', $data);
    }

    public function update_user($id, $data) {
        return $this->db->where('id', $id)->update('users', $data);
    }

    public function hapus_user($id) {
        // hak_akses ikut terhapus otomatis lewat FOREIGN KEY ... ON DELETE CASCADE
        return $this->db->where('id', $id)->delete('users');
    }

    /**
     * Ambil hak akses user dalam bentuk array asosiatif per modul.
     * Ini format yang dipakai has_akses() lewat session, JANGAN diubah strukturnya
     * tanpa menyesuaikan juga akses_helper.php.
     */
    public function get_permission_map($user_id) {
        $rows = $this->db->where('user_id', $user_id)->get('hak_akses')->result();
        $map = array();

        foreach ($rows as $r) {
            $map[$r->modul] = array(
                'can_create' => (int) $r->can_create,
                'can_read'   => (int) $r->can_read,
                'can_update' => (int) $r->can_update,
                'can_print' => (int) $r->can_print,
                'can_delete' => (int) $r->can_delete,
            );
        }

        return $map;
    }

    /**
     * Sama seperti get_permission_map(), tapi SEMUA modul dari daftar_modul() selalu
     * muncul di hasil (default 0 kalau belum pernah diatur) -- supaya gampang dirender
     * jadi tabel checkbox lengkap di halaman "Kelola Akses", tidak ada modul yang hilang.
     */
    public function get_akses_untuk_form($user_id)
    {
        $map = $this->get_permission_map($user_id);
        $hasil = array();

        foreach (daftar_modul() as $kode => $nama) {
            $hasil[$kode] = isset($map[$kode]) ? $map[$kode] : array(
                'can_create' => 0, 'can_read' => 0, 'can_update' => 0, 'can_print' => 0, 'can_delete' => 0,
            );
        }

        return $hasil;
    }

    /**
     * Simpan seluruh matrix hak akses seorang user sekaligus (upsert per modul).
     * $akses_per_modul = ['transaksi' => ['can_create'=>'1', 'can_read'=>'1', ...], ...]
     */
    public function simpan_akses($user_id, $akses_per_modul)
    {
        foreach (daftar_modul() as $kode => $nama) {
            $input = isset($akses_per_modul[$kode]) ? $akses_per_modul[$kode] : array();

            $data = array(
                'can_create' => !empty($input['can_create']) ? 1 : 0,
                'can_read'   => !empty($input['can_read']) ? 1 : 0,
                'can_update' => !empty($input['can_update']) ? 1 : 0,
                'can_print' => !empty($input['can_print']) ? 1 : 0,
                'can_delete' => !empty($input['can_delete']) ? 1 : 0,
            );

            $existing = $this->db->where('user_id', $user_id)->where('modul', $kode)->get('hak_akses')->row();

            if ($existing) {
                $this->db->where('id', $existing->id)->update('hak_akses', $data);
            } else {
                $data['user_id'] = $user_id;
                $data['modul'] = $kode;
                $this->db->insert('hak_akses', $data);
            }
        }

        return true;
    }
}