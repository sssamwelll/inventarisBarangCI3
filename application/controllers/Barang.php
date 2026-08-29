<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barang extends MY_Controller
{
    protected $active_menu = 'barang';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Barang_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['page_title'] = 'Master Barang';
        $data['barang'] = $this->Barang_model->get_semua_barang();
        $data['kategori'] = $this->Barang_model->get_semua_kategori();

        $this->render('barang/index', $data);
    }

    public function simpan()
    {
        $this->form_validation->set_rules('nama_barang', 'Nama Barang', 'required|trim');
        $this->form_validation->set_rules('kategori_id', 'Kategori', 'required|numeric');
        $this->form_validation->set_rules('satuan', 'Satuan', 'required|trim');
        $this->form_validation->set_rules('harga_beli', 'Harga Beli', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('harga_jual', 'Harga Jual', 'required|numeric|greater_than_equal_to[0]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<span>', '</span> '));
            redirect('barang');
        }

        $id = $this->input->post('id'); // Jika ada ID, berarti Update. Jika tidak, Insert.
        
        $data = [
            'nama_barang' => htmlspecialchars($this->input->post('nama_barang', true)),
            'kategori_id' => $this->input->post('kategori_id'),
            'satuan'      => htmlspecialchars($this->input->post('satuan', true)),
            'harga_beli'  => $this->input->post('harga_beli'),
            'harga_jual'  => $this->input->post('harga_jual')
        ];

        if (!empty($id)) {
            $this->Barang_model->update_data($id, $data);
            $this->session->set_flashdata('success', 'Data barang dan harga berhasil diperbarui.');
        } else {
            // Default stok awal selalu 0 saat buat baru
            $data['stok'] = 0; 
            $this->Barang_model->simpan_baru($data);
            $this->session->set_flashdata('success', 'Barang rosok baru berhasil ditambahkan.');
        }

        redirect('barang');
    }

    public function hapus($id)
    {
        if ($id) {
            $this->Barang_model->hapus_sementara($id);
            $this->session->set_flashdata('success', 'Barang berhasil dihapus dari daftar aktif.');
        }
        redirect('barang');
    }
}