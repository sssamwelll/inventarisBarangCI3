<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi_model extends CI_Model
{
    // Jam kerja normal perusahaan: 08:00 - 16:00
    const JAM_MULAI_KERJA = '08:00:00';
    const JAM_SELESAI_KERJA = '16:00:00';

    /**
     * Hitung jam lembur dari jam pulang.
     * Lembur dihitung proporsional (desimal jam) dari selisih jam pulang - 16:00,
     * supaya lembur 30 menit otomatis = 0.5 jam saat dikali tarif per jam nanti.
     */
    public function hitung_lembur($jam_pulang)
    {
        if (empty($jam_pulang)) {
            return 0;
        }

        $batas = strtotime(self::JAM_SELESAI_KERJA);
        $pulang = strtotime($jam_pulang);

        if ($pulang === false || $pulang <= $batas) {
            return 0;
        }

        $selisih_jam = ($pulang - $batas) / 3600;
        return round($selisih_jam, 2);
    }

    /**
     * Ambil semua karyawan aktif, digabung dengan data absensi pada tanggal tertentu
     * (kalau belum diisi hari itu, field absensi-nya NULL -- ditangani di view/controller).
     */
    public function get_untuk_input($tanggal)
    {
        $this->db->select('karyawan.id AS karyawan_id, karyawan.nama, karyawan.jabatan,
                            absensi.id AS absensi_id, absensi.status, absensi.jam_masuk,
                            absensi.jam_pulang, absensi.jam_lembur, absensi.keterangan');
        $this->db->from('karyawan');
        $this->db->join('absensi', "absensi.karyawan_id = karyawan.id AND absensi.tanggal = " . $this->db->escape($tanggal), 'left');
        $this->db->where('karyawan.status', 'aktif');
        $this->db->order_by('karyawan.nama', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Simpan absensi massal untuk satu tanggal.
     * $rows = array of ['karyawan_id'=>, 'status'=>, 'jam_masuk'=>, 'jam_pulang'=>, 'keterangan'=>]
     */
    public function simpan_massal($tanggal, $rows)
    {
        foreach ($rows as $row) {
            $jam_masuk = $row['status'] === 'hadir' ? $row['jam_masuk'] : null;
            $jam_pulang = $row['status'] === 'hadir' ? $row['jam_pulang'] : null;
            $jam_lembur = $row['status'] === 'hadir' ? $this->hitung_lembur($jam_pulang) : 0;

            $data = array(
                'karyawan_id' => $row['karyawan_id'],
                'tanggal'     => $tanggal,
                'status'      => $row['status'],
                'jam_masuk'   => $jam_masuk,
                'jam_pulang'  => $jam_pulang,
                'jam_lembur'  => $jam_lembur,
                'keterangan'  => $row['keterangan'],
            );

            $existing = $this->db->where('karyawan_id', $row['karyawan_id'])
                ->where('tanggal', $tanggal)
                ->get('absensi')->row();

            if ($existing) {
                $this->db->where('id', $existing->id)->update('absensi', $data);
            } else {
                $this->db->insert('absensi', $data);
            }
        }

        return true;
    }

    /**
     * Riwayat absensi untuk halaman rekap, dengan filter tanggal & karyawan.
     */
    public function get_riwayat($filter = array())
    {
        $this->db->select('absensi.*, karyawan.nama')
            ->from('absensi')
            ->join('karyawan', 'karyawan.id = absensi.karyawan_id');

        if (!empty($filter['tanggal_dari'])) {
            $this->db->where('absensi.tanggal >=', $filter['tanggal_dari']);
        }
        if (!empty($filter['tanggal_sampai'])) {
            $this->db->where('absensi.tanggal <=', $filter['tanggal_sampai']);
        }
        if (!empty($filter['karyawan_id'])) {
            $this->db->where('absensi.karyawan_id', $filter['karyawan_id']);
        }

        $this->db->order_by('absensi.tanggal', 'DESC')->order_by('karyawan.nama', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Rekap hadir & lembur per karyawan pada satu rentang tanggal.
     * Dipakai nanti oleh modul Gaji untuk hitung gaji mingguan.
     */
    public function get_rekap($karyawan_id, $tanggal_awal, $tanggal_akhir)
    {
        $this->db->select('
            SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) AS total_hadir,
            SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) AS total_izin,
            SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) AS total_sakit,
            SUM(CASE WHEN status = "alpa" THEN 1 ELSE 0 END) AS total_alpa,
            COALESCE(SUM(jam_lembur), 0) AS total_jam_lembur
        ');
        $this->db->where('karyawan_id', $karyawan_id);
        $this->db->where('tanggal >=', $tanggal_awal);
        $this->db->where('tanggal <=', $tanggal_akhir);

        return $this->db->get('absensi')->row();
    }
}