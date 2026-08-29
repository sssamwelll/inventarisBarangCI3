<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gaji_model extends CI_Model
{
    public function sudah_diproses($karyawan_id, $periode_awal, $periode_akhir)
    {
        return $this->db->where('karyawan_id', $karyawan_id)
            ->where('periode_awal', $periode_awal)
            ->where('periode_akhir', $periode_akhir)
            ->get('gaji')->row();
    }

    public function get_by_id($id)
    {
        $this->db->select('gaji.*, karyawan.nama, karyawan.jabatan')
            ->from('gaji')
            ->join('karyawan', 'karyawan.id = gaji.karyawan_id')
            ->where('gaji.id', $id);
        return $this->db->get()->row();
    }

    public function get_riwayat($filter = array())
    {
        $this->db->select('gaji.*, karyawan.nama, karyawan.jabatan')
            ->from('gaji')
            ->join('karyawan', 'karyawan.id = gaji.karyawan_id');

        if (!empty($filter['periode_awal'])) {
            $this->db->where('gaji.periode_awal >=', $filter['periode_awal']);
        }
        if (!empty($filter['periode_akhir'])) {
            $this->db->where('gaji.periode_akhir <=', $filter['periode_akhir']);
        }
        if (!empty($filter['karyawan_id'])) {
            $this->db->where('gaji.karyawan_id', $filter['karyawan_id']);
        }
        $this->db->order_by('gaji.periode_awal', 'DESC')->order_by('karyawan.nama', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Proses & bayar gaji SATU karyawan untuk SATU periode, dalam satu transaksi DB.
     * Insert ke tabel gaji + insert otomatis ke kas harus sama-sama sukses (atau sama-sama batal).
     *
     * total_hadir & total_jam_lembur dikirim dari controller (hasil query Absensi_model::get_rekap),
     * bukan dihitung ulang di sini, supaya model ini tidak perlu tahu soal tabel absensi.
     */
    public function proses_dan_bayar($karyawan, $periode_awal, $periode_akhir, $total_hadir, $total_jam_lembur, $potongan, $user_id)
    {
        $gaji_pokok_periode = $total_hadir * (float) $karyawan->gaji_pokok;
        $tunjangan_lembur = $total_jam_lembur * (float) $karyawan->tarif_lembur;
        $total_gaji = $gaji_pokok_periode + $tunjangan_lembur - $potongan;
        if ($total_gaji < 0) {
            $total_gaji = 0;
        }

        $this->db->trans_start();

        $this->db->insert('gaji', array(
            'karyawan_id'      => $karyawan->id,
            'periode_awal'     => $periode_awal,
            'periode_akhir'    => $periode_akhir,
            'total_hadir'      => $total_hadir,
            'total_jam_lembur' => $total_jam_lembur,
            'gaji_pokok'       => $gaji_pokok_periode,
            'tunjangan_lembur' => $tunjangan_lembur,
            'potongan'         => $potongan,
            'total_gaji'       => $total_gaji,
            'status_bayar'     => 'sudah',
            'tanggal_bayar'    => date('Y-m-d'),
            'user_id'          => $user_id,
        ));
        $gaji_id = $this->db->insert_id();

        $this->db->insert('kas', array(
            'tanggal'    => date('Y-m-d'),
            'tipe'       => 'keluar',
            'kategori'   => 'gaji',
            'keterangan' => 'Gaji mingguan ' . $karyawan->nama . ' (' . date('d/m', strtotime($periode_awal)) . ' - ' . date('d/m/Y', strtotime($periode_akhir)) . ')',
            'jumlah'     => $total_gaji,
            'ref_id'     => $gaji_id,
            'ref_type'   => 'gaji',
            'user_id'    => $user_id,
        ));

        $this->db->trans_complete();

        return $this->db->trans_status() ? $total_gaji : false;
    }
}