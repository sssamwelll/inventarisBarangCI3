<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model
{
    /**
     * Total nominal & jumlah nota per tipe (beli/jual) dalam periode.
     * Ini "accrual" -- dihitung dari semua transaksi yang terjadi, terlepas status bayarnya.
     */
    public function get_ringkasan_transaksi($tanggal_dari, $tanggal_sampai)
    {
        $this->db->select('tipe, COUNT(*) AS jumlah_nota, SUM(total) AS total_nominal');
        $this->db->where('DATE(tanggal) >=', $tanggal_dari);
        $this->db->where('DATE(tanggal) <=', $tanggal_sampai);
        $this->db->group_by('tipe');

        return $this->db->get('transaksi')->result();
    }

    /**
     * Total mutasi kas per tipe+kategori dalam periode.
     * Ini "cash basis" -- uang yang benar-benar sudah tercatat keluar/masuk di buku kas.
     */
    public function get_ringkasan_kas($tanggal_dari, $tanggal_sampai)
    {
        $this->db->select('tipe, kategori, SUM(jumlah) AS total');
        $this->db->where('tanggal >=', $tanggal_dari);
        $this->db->where('tanggal <=', $tanggal_sampai);
        $this->db->group_by('tipe, kategori');

        return $this->db->get('kas')->result();
    }

    /**
     * Breakdown qty & nominal per kategori barang (Besi, Kertas, Plastik, dst), per tipe.
     */
    public function get_breakdown_kategori($tanggal_dari, $tanggal_sampai)
    {
        $this->db->select('kategori_barang.nama_kategori, transaksi.tipe,
                            SUM(transaksi_detail.qty) AS total_qty,
                            SUM(transaksi_detail.subtotal) AS total_nominal');
        $this->db->from('transaksi_detail');
        $this->db->join('transaksi', 'transaksi.id = transaksi_detail.transaksi_id');
        $this->db->join('barang', 'barang.id = transaksi_detail.barang_id');
        $this->db->join('kategori_barang', 'kategori_barang.id = barang.kategori_id');
        $this->db->where('DATE(transaksi.tanggal) >=', $tanggal_dari);
        $this->db->where('DATE(transaksi.tanggal) <=', $tanggal_sampai);
        $this->db->group_by('kategori_barang.nama_kategori, transaksi.tipe');
        $this->db->order_by('kategori_barang.nama_kategori', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Rekap nominal transaksi per hari, per tipe -- dasar untuk tabel tren harian.
     */
    public function get_rekap_harian($tanggal_dari, $tanggal_sampai)
    {
        $this->db->select('DATE(tanggal) AS tgl, tipe, SUM(total) AS total_nominal');
        $this->db->where('DATE(tanggal) >=', $tanggal_dari);
        $this->db->where('DATE(tanggal) <=', $tanggal_sampai);
        $this->db->group_by('DATE(tanggal), tipe');
        $this->db->order_by('tgl', 'ASC');

        return $this->db->get('transaksi')->result();
    }
}