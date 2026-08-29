<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barang_model extends CI_Model
{
    public function get_semua_barang()
    {
        return $this->db->select('barang.*, kategori_barang.nama_kategori')
            ->from('barang')
            ->join('kategori_barang', 'kategori_barang.id = barang.kategori_id', 'left')
            ->where('barang.is_aktif', 1) // Hanya tampilkan yang aktif (tidak di-soft delete)
            ->order_by('kategori_barang.nama_kategori', 'ASC')
            ->order_by('barang.nama_barang', 'ASC')
            ->get()
            ->result();
    }

    public function get_semua_kategori()
    {
        return $this->db->order_by('nama_kategori', 'ASC')->get('kategori_barang')->result();
    }

    public function simpan_baru($data)
    {
        return $this->db->insert('barang', $data);
    }

    public function update_data($id, $data)
    {
        return $this->db->where('id', $id)->update('barang', $data);
    }

    public function hapus_sementara($id)
    {
        // Mengubah status is_aktif menjadi 0 (Soft Delete), agar nota lama tidak error
        return $this->db->where('id', $id)->update('barang', ['is_aktif' => 0]);
    }
}