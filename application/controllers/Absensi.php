<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends MY_Controller
{
    protected $active_menu = 'absensi';

    public function __construct() {
        parent::__construct();
        // cek_akses('absensi', 'read');

        $this->load->model('Absensi_model');
        $this->load->model('Karyawan_model');
    }

    public function index() {
        cek_akses('absensi', 'create');

        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

        $data['page_title'] = 'Absensi';
        $data['tanggal'] = $tanggal;
        $data['karyawan'] = $this->Absensi_model->get_untuk_input($tanggal);

        $this->render('absensi/index', $data);
    }

    public function simpan() {
        cek_akses('absensi', 'create');

        $tanggal = $this->input->post('tanggal');
        $karyawan_ids = (array) $this->input->post('karyawan_id');
        $statuses = (array) $this->input->post('status');
        $jam_masuks = (array) $this->input->post('jam_masuk');
        $jam_pulangs = (array) $this->input->post('jam_pulang');
        $keterangans = (array) $this->input->post('keterangan');

        if (empty($tanggal) || empty($karyawan_ids)) {
            $this->session->set_flashdata('error', 'Data absensi tidak lengkap.');
            redirect('absensi');
        }

        $rows = array();
        foreach ($karyawan_ids as $i => $karyawan_id) {
            $rows[] = array(
                'karyawan_id' => (int) $karyawan_id,
                'status'      => isset($statuses[$i]) ? $statuses[$i] : 'hadir',
                'jam_masuk'   => isset($jam_masuks[$i]) ? $jam_masuks[$i] : null,
                'jam_pulang'  => isset($jam_pulangs[$i]) ? $jam_pulangs[$i] : null,
                'keterangan'  => isset($keterangans[$i]) ? trim($keterangans[$i]) : null,
            );
        }

        $this->Absensi_model->simpan_massal($tanggal, $rows);

        $this->session->set_flashdata('success', 'Absensi tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil disimpan.');
        redirect('absensi?tanggal=' . $tanggal);
    }

    public function riwayat() {
        cek_akses('absensi', 'read');

        $filter = array(
            'tanggal_dari' => $this->input->get('tanggal_dari') ?: date('Y-m-01'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai') ?: date('Y-m-d'),
            'karyawan_id' => $this->input->get('karyawan_id'),
        );

        $data['page_title'] = 'Riwayat Absensi';
        $data['filter'] = $filter;
        $data['riwayat'] = $this->Absensi_model->get_riwayat($filter);
        $data['karyawan_list'] = $this->Karyawan_model->get_semua_aktif();

        $this->render('absensi/riwayat', $data);
    }
}