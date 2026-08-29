<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function jumlah_transaksi_hari_ini($tipe = null)
    {
        $this->db->where('DATE(tanggal)', date('Y-m-d'));
        if ($tipe) {
            $this->db->where('tipe', $tipe);
        }
        return $this->db->count_all_results('transaksi');
    }

    public function total_nominal_transaksi_hari_ini($tipe)
    {
        $this->db->select_sum('total');
        $this->db->where('DATE(tanggal)', date('Y-m-d'));
        $this->db->where('tipe', $tipe);
        $row = $this->db->get('transaksi')->row();
        return $row ? (float) $row->total : 0;
    }

    public function saldo_kas()
    {
        $masuk = $this->db->select_sum('jumlah')->where('tipe', 'masuk')->get('kas')->row();
        $keluar = $this->db->select_sum('jumlah')->where('tipe', 'keluar')->get('kas')->row();
        $total_masuk = $masuk ? (float) $masuk->jumlah : 0;
        $total_keluar = $keluar ? (float) $keluar->jumlah : 0;
        return $total_masuk - $total_keluar;
    }

    public function karyawan_hadir_hari_ini()
    {
        return $this->db->where('tanggal', date('Y-m-d'))
            ->where('status', 'hadir')
            ->count_all_results('absensi');
    }

    public function total_karyawan_aktif()
    {
        return $this->db->where('status', 'aktif')->count_all_results('karyawan');
    }

    public function transaksi_terbaru($limit = 8)
    {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get('transaksi')->result();
    }
}
