<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kas_model extends CI_Model
{
    /**
     * Saldo kumulatif SEBELUM tanggal_dari (dipakai sebagai "saldo awal" buku kas).
     */
    public function get_saldo_sebelum($tanggal_dari)
    {
        $masuk = $this->db->select_sum('jumlah')
            ->where('tipe', 'masuk')
            ->where('tanggal <', $tanggal_dari)
            ->get('kas')->row();

        $keluar = $this->db->select_sum('jumlah')
            ->where('tipe', 'keluar')
            ->where('tanggal <', $tanggal_dari)
            ->get('kas')->row();

        $total_masuk = $masuk ? (float) $masuk->jumlah : 0;
        $total_keluar = $keluar ? (float) $keluar->jumlah : 0;

        return $total_masuk - $total_keluar;
    }

    /**
     * Daftar mutasi kas sesuai filter, diurutkan KRONOLOGIS (lama -> baru)
     * supaya kolom saldo berjalan enak dibaca seperti buku kas asli.
     */
    public function get_mutasi($filter = array())
    {
        $this->db->select('kas.*, users.nama AS nama_user')
            ->from('kas')
            ->join('users', 'users.id = kas.user_id', 'left');

        if (!empty($filter['tanggal_dari'])) {
            $this->db->where('kas.tanggal >=', $filter['tanggal_dari']);
        }
        if (!empty($filter['tanggal_sampai'])) {
            $this->db->where('kas.tanggal <=', $filter['tanggal_sampai']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('kas.kategori', $filter['kategori']);
        }
        if (!empty($filter['tipe'])) {
            $this->db->where('kas.tipe', $filter['tipe']);
        }

        $this->db->order_by('kas.tanggal', 'ASC')->order_by('kas.id', 'ASC');

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get('kas')->row();
    }

    /**
     * Catat kas manual (di luar transaksi otomatis).
     * Kategori dibatasi ke 'operasional'/'lainnya' -- kategori 'transaksi' dan 'gaji'
     * hanya boleh dibuat otomatis oleh modul Transaksi/Gaji, supaya tidak dobel catat.
     */
    public function simpan_manual($data)
    {
        return $this->db->insert('kas', $data);
    }

    /**
     * Hanya izinkan hapus entri manual (ref_type kosong).
     * Entri yang nempel ke transaksi/gaji tidak boleh dihapus lewat sini,
     * supaya datanya tetap sinkron dengan modul asalnya.
     */
    public function hapus_manual($id)
    {
        $row = $this->get_by_id($id);
        if (!$row) {
            return array('success' => false, 'message' => 'Data kas tidak ditemukan.');
        }
        if (!empty($row->ref_type)) {
            return array('success' => false, 'message' => 'Entri ini otomatis dari modul ' . $row->ref_type . ', tidak bisa dihapus dari sini.');
        }

        $this->db->where('id', $id)->delete('kas');
        return array('success' => true);
    }
}