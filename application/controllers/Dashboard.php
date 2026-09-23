<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    protected $active_menu = 'dashboard';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dashboard_model');
    }

    public function index()
    {
        $data['jumlah_beli_hari_ini'] = $this->Dashboard_model->jumlah_transaksi_hari_ini('beli');
        $data['jumlah_jual_hari_ini'] = $this->Dashboard_model->jumlah_transaksi_hari_ini('jual');
        $data['nominal_beli_hari_ini'] = $this->Dashboard_model->total_nominal_transaksi_hari_ini('beli');
        $data['nominal_jual_hari_ini'] = $this->Dashboard_model->total_nominal_transaksi_hari_ini('jual');
        $data['saldo_kas'] = $this->Dashboard_model->saldo_kas();
        $data['karyawan_hadir'] = $this->Dashboard_model->karyawan_hadir_hari_ini();
        $data['total_karyawan'] = $this->Dashboard_model->total_karyawan_aktif();
        $data['transaksi_terbaru'] = $this->Dashboard_model->transaksi_terbaru();
        $data['page_title'] = 'Dashboard';

        $this->render('dashboard/index', $data);
    }
}
