<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller untuk seluruh halaman admin (setelah login).
 * Semua controller modul (Transaksi, Barang, Kas, dll) nanti extends class ini,
 * bukan CI_Controller langsung, supaya sidebar & proteksi login otomatis konsisten.
 */
class MY_Controller extends CI_Controller
{
    protected $active_menu = '';

    public function __construct()
    {
        parent::__construct();

        // TODO: setelah modul Auth dibuat, aktifkan proteksi login di sini:
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
    }

    /**
     * Render halaman dengan layout admin (header + sidebar + konten + footer).
     *
     * @param string $view   path view konten, mis. 'dashboard/index'
     * @param array  $data   data yang dikirim ke view
     */
    protected function render($view, $data = array())
    {
        $data['active_menu'] = $this->active_menu;

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view($view, $data);
        $this->load->view('layout/footer', $data);
    }
}
