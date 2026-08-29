<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    protected $active_menu = 'transaksi';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaksi_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['page_title'] = 'Transaksi';
        $data['transaksi'] = $this->Transaksi_model->get_transaksi(25);

        $this->render('transaksi/index', $data);
    }

    public function cetak($id)
    {
        $transaksi = $this->Transaksi_model->get_transaksi_by_id($id);
        if (!$transaksi) {
            show_404();
        }

        $detail = $this->Transaksi_model->get_transaksi_detail($id);

        require_once APPPATH . 'libraries/Nota_pdf.php';

        $pdf = new Nota_pdf('P', 'mm', array(80, 200));
        $pdf->set_data($transaksi, $detail);
        $pdf->SetTitle('Nota ' . $transaksi->no_nota);
        $pdf->AddPage();
        $pdf->cetak_isi();
        $pdf->Output('I', 'Nota-' . $transaksi->no_nota . '.pdf');
    }

    public function tambah()
    {
        $data['page_title'] = 'Transaksi Baru';
        $data['barang_list'] = $this->Transaksi_model->get_barang_options();
        $data['form_rows'] = $this->build_form_rows();

        $this->render('transaksi/tambah', $data);
    }

    public function simpan()
    {
        $this->form_validation->set_rules('tipe', 'Tipe transaksi', 'required|in_list[beli,jual]');
        $this->form_validation->set_rules('nama_pihak', 'Nama pihak', 'required|trim');
        $this->form_validation->set_rules('status_bayar', 'Status bayar', 'required|in_list[lunas,hutang,piutang]');
        $this->form_validation->set_rules('no_hp', 'No HP', 'trim|max_length[20]');

        if (!$this->form_validation->run()) {
            return $this->tambah();
        }

        $item_error = '';
        $items = $this->collect_items($item_error);
        if (empty($items)) {
            if ($item_error !== '') {
                $this->session->set_flashdata('error', $item_error);
            }
            else {
                $this->session->set_flashdata('error', 'Minimal satu barang harus diisi.');
            }
            return $this->tambah();
        }

        $header = array(
            'tipe' => $this->input->post('tipe', true),
            'nama_pihak' => trim((string) $this->input->post('nama_pihak', true)),
            'no_hp' => trim((string) $this->input->post('no_hp', true)),
            'status_bayar' => $this->input->post('status_bayar', true),
        );

        $result = $this->Transaksi_model->save_transaksi($header, $items);
        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
            return $this->tambah();
        }

        $this->session->set_flashdata('success', 'Transaksi ' . $result['no_nota'] . ' berhasil disimpan.');
        redirect('Transaksi/cetak/' . $result['transaksi_id']);
    }

    private function build_form_rows()
    {
        $barang_ids = (array) $this->input->post('barang_id');
        $qtys = (array) $this->input->post('qty');
        $harga_satuans = (array) $this->input->post('harga_satuan');

        $row_count = max(count($barang_ids), count($qtys), count($harga_satuans), 1);
        $rows = array();

        for ($i = 0; $i < $row_count; $i++) {
            $rows[] = array(
                'barang_id' => isset($barang_ids[$i]) ? $barang_ids[$i] : '',
                'qty' => isset($qtys[$i]) ? $qtys[$i] : '',
                'harga_satuan' => isset($harga_satuans[$i]) ? $harga_satuans[$i] : '',
            );
        }

        return $rows;
    }

    private function collect_items(&$error = '')
    {
        $barang_ids = (array) $this->input->post('barang_id');
        $qtys = (array) $this->input->post('qty');
        $harga_satuans = (array) $this->input->post('harga_satuan');

        $items = array();
        $row_count = max(count($barang_ids), count($qtys), count($harga_satuans));

        for ($i = 0; $i < $row_count; $i++) {
            $barang_id = isset($barang_ids[$i]) ? (int) $barang_ids[$i] : 0;
            $qty = isset($qtys[$i]) ? (float) str_replace(',', '.', (string) $qtys[$i]) : 0;
            $harga_satuan = isset($harga_satuans[$i]) ? (float) str_replace(',', '.', (string) $harga_satuans[$i]) : 0;

            if ($barang_id <= 0 && $qty <= 0 && $harga_satuan <= 0) {
                continue;
            }

            if ($barang_id <= 0 || $qty <= 0) {
                $error = 'Setiap baris transaksi yang diisi wajib memiliki barang dan qty.';
                return array();
            }

            $items[] = array(
                'barang_id' => $barang_id,
                'qty' => $qty,
                'harga_satuan' => $harga_satuan,
            );
        }

        return $items;
    }
}
