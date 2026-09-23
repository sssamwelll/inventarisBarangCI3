<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karyawan extends MY_Controller {
    protected $active_menu = 'karyawan';

    public function __construct() {
        parent::__construct();
        cek_akses('karyawan', 'read');

        $this->load->model('Karyawan_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['page_title'] = 'Data Karyawan';
        $data['karyawan'] = $this->Karyawan_model->get_semua();

        $this->render('karyawan/index', $data);
    }

    public function simpan() {
        cek_akses('karyawan', 'create');

        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
        $this->form_validation->set_rules('no_hp', 'No HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('tanggal_masuk', 'Tanggal masuk', 'required');
        $this->form_validation->set_rules('gaji_pokok', 'Gaji per hari', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('tarif_lembur', 'Tarif lembur', 'required|numeric|greater_than_equal_to[0]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors('<span>', '</span> '));
            redirect('Karyawan');
        }

        $id = $this->input->post('id');

        $data = array(
            'nama'          => trim($this->input->post('nama', true)),
            'jabatan'       => trim($this->input->post('jabatan', true)),
            'no_hp'         => trim($this->input->post('no_hp', true)),
            'tanggal_masuk' => $this->input->post('tanggal_masuk'),
            'gaji_pokok'    => (float) $this->input->post('gaji_pokok'),
            'tarif_lembur'  => (float) $this->input->post('tarif_lembur'),
        );

        if (!empty($id)) {
            $this->Karyawan_model->update_data($id, $data);
            $this->session->set_flashdata('success', 'Data karyawan berhasil diperbarui.');
        } else {
            $data['status'] = 'aktif';
            $this->Karyawan_model->simpan_baru($data);
            $this->session->set_flashdata('success', 'Karyawan baru berhasil ditambahkan.');
        }

        redirect('Karyawan');
    }

    public function nonaktifkan($id) {
        cek_akses('karyawan', 'delete');

        $this->Karyawan_model->set_status($id, 'nonaktif');
        $this->session->set_flashdata('success', 'Karyawan dinonaktifkan (riwayat absensi & gaji tetap tersimpan).');
        redirect('Karyawan');
    }

    public function aktifkan($id) {
        cek_akses('karyawan', 'delete');

        $this->Karyawan_model->set_status($id, 'aktif');
        $this->session->set_flashdata('success', 'Karyawan diaktifkan kembali.');
        redirect('Karyawan');
    }
}