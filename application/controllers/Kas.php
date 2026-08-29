<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kas extends MY_Controller
{
    protected $active_menu = 'kas';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Kas_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $tanggal_dari = $this->input->get('tanggal_dari') ?: date('Y-m-01');
        $tanggal_sampai = $this->input->get('tanggal_sampai') ?: date('Y-m-d');

        $filter = array(
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
            'kategori' => $this->input->get('kategori'),
            'tipe' => $this->input->get('tipe'),
        );

        $saldo_berjalan = $this->Kas_model->get_saldo_sebelum($tanggal_dari);
        $mutasi = $this->Kas_model->get_mutasi($filter);

        foreach ($mutasi as $m) {
            $saldo_berjalan += ($m->tipe === 'masuk') ? (float) $m->jumlah : -(float) $m->jumlah;
            $m->saldo_berjalan = $saldo_berjalan;
        }

        $data['page_title'] = 'Kas';
        $data['filter'] = $filter;
        $data['saldo_awal'] = $this->Kas_model->get_saldo_sebelum($tanggal_dari);
        $data['saldo_akhir'] = $saldo_berjalan;
        $data['mutasi'] = $mutasi;

        $this->render('kas/index', $data);
    }

    public function simpan()
    {
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[masuk,keluar]');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required|in_list[operasional,lainnya]');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required|numeric|greater_than[0]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors('<span>', '</span> '));
            redirect('kas');
        }

        $data = array(
            'tanggal'    => $this->input->post('tanggal'),
            'tipe'       => $this->input->post('tipe'),
            'kategori'   => $this->input->post('kategori'),
            'keterangan' => trim($this->input->post('keterangan', true)),
            'jumlah'     => (float) $this->input->post('jumlah'),
            'ref_id'     => null,
            'ref_type'   => null,
            'user_id'    => $this->session->userdata('user_id'),
        );

        $this->Kas_model->simpan_manual($data);
        $this->session->set_flashdata('success', 'Mutasi kas berhasil dicatat.');
        redirect('kas');
    }

    public function hapus($id)
    {
        $result = $this->Kas_model->hapus_manual($id);
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Mutasi kas berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        redirect('kas');
    }
}