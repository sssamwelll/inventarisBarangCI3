<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi extends MY_Controller
{
    protected $active_menu = 'transaksi';

    public function __construct()
    {
        parent::__construct();
        cek_akses('transaksi', 'read');

        $this->load->model('Transaksi_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->load->library('pagination');

        $per_page = 8;
        $halaman = max(1, (int) $this->input->get('halaman'));
        $offset = ($halaman - 1) * $per_page;

        $total_rows = $this->Transaksi_model->count_all_transaksi();

        $config = default_pagination_config(base_url('transaksi'), $total_rows, $per_page);
        $this->pagination->initialize($config);

        $data['page_title'] = 'Transaksi';
        $data['transaksi'] = $this->Transaksi_model->get_transaksi($per_page, $offset);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['total_rows'] = $total_rows;

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
        // cek_akses('transaksi', 'create');
        return $this->tampilkan_form();

        $this->render('transaksi/tambah', $data);
    }

    public function cari_nama_pihak()
    {
        $keyword = $this->input->get('term');
        $hasil = $this->Transaksi_model->cari_nama_pihak($keyword);

        $daftar_nama = array();
        foreach ($hasil as $h) {
            $daftar_nama[] = $h->nama_pihak;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($daftar_nama));
    }

     public function cek_harga_langganan()
    {
        $nama_pihak = trim((string) $this->input->get('nama_pihak'));
        $barang_id = (int) $this->input->get('barang_id');
        $tipe = $this->input->get('tipe');

        $response = array('ada' => false);

        if ($nama_pihak !== '' && $barang_id > 0 && in_array($tipe, array('beli', 'jual'), true)) {
            $row = $this->Transaksi_model->get_harga_langganan($nama_pihak, $barang_id, $tipe);
            if ($row) {
                $response = array('ada' => true, 'harga_satuan' => (float) $row->harga_satuan);
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function edit($id)
    {
        cek_akses('transaksi', 'update');

        $transaksi = $this->Transaksi_model->get_transaksi_by_id($id);
        if (!$transaksi) {
            show_404();
        }

        $detail = $this->Transaksi_model->get_transaksi_detail($id);
        $form_rows = array();
        foreach ($detail as $d) {
            $form_rows[] = array('barang_id' => $d->barang_id, 'qty' => $d->qty, 'harga_satuan' => $d->harga_satuan);
        }

        return $this->tampilkan_form($transaksi, $form_rows);
    }

    private function tampilkan_form($transaksi = null, $form_rows = null)
    {
        $data['page_title'] = $transaksi ? 'Edit Transaksi' : 'Transaksi Baru';
        $data['is_edit'] = (bool) $transaksi;
        $data['transaksi'] = $transaksi;
        $data['transaksi_id'] = $transaksi ? $transaksi->id : null;
        $data['barang_list'] = $this->Transaksi_model->get_barang_options();
        $data['form_rows'] = $form_rows !== null ? $form_rows : $this->build_form_rows();
        // $data['extra_scripts'] = '<script src="' . base_url('assets/vendor/jquery360/jquery-ui.min.js') . '"></script>';

        $this->render('transaksi/tambah', $data);
    }

    private function respond_gagal($message, $render_ulang = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(array('success' => false, 'message' => $message)));
            return;
        }

        $this->session->set_flashdata('error', $message);

        if ($render_ulang !== null) {
            $render_ulang();
        } else {
            $this->tambah();
        }
    }

    public function simpan()
    {
        cek_akses('transaksi', 'create');

        $this->form_validation->set_rules('tipe', 'Tipe transaksi', 'required|in_list[beli,jual]');
        $this->form_validation->set_rules('nama_pihak', 'Nama pihak', 'required|trim');
        $this->form_validation->set_rules('status_bayar', 'Status bayar', 'required|in_list[lunas,hutang,piutang]');
        $this->form_validation->set_rules('no_hp', 'No HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('potongan', 'Potongan', 'numeric|greater_than_equal_to[0]');

        if (!$this->form_validation->run()) {
            return $this->respond_gagal(implode('<br>', $this->form_validation->error_array()));
        }

        $item_error = '';
        $items = $this->collect_items($item_error);
        if (empty($items)) {
            return $this->respond_gagal($item_error !== '' ? $item_error : 'Minimal satu barang harus diisi.');
        }

        $header = array(
            'tipe' => $this->input->post('tipe', true),
            'nama_pihak' => trim((string) $this->input->post('nama_pihak', true)),
            'no_hp' => trim((string) $this->input->post('no_hp', true)),
            'status_bayar' => $this->input->post('status_bayar', true),
            'potongan' => $this->input->post('potongan', true),
            'catatan_potongan' => trim((string) $this->input->post('catatan_potongan', true)),
        );

        $result = $this->Transaksi_model->save_transaksi($header, $items);
        if (!$result['success']) {
            return $this->respond_gagal($result['message']);
        }

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => true,
                    'no_nota' => $result['no_nota'],
                    'cetak_url' => base_url('Transaksi/cetak/' . $result['transaksi_id']),
                )));
            return;
        }

        $this->session->set_flashdata('success', 'Transaksi ' . $result['no_nota'] . ' berhasil disimpan.');
        redirect('Transaksi/cetak/' . $result['transaksi_id']);
    }

    public function update($id)
    {
        cek_akses('transaksi', 'update');

        $this->form_validation->set_rules('tipe', 'Tipe transaksi', 'required|in_list[beli,jual]');
        $this->form_validation->set_rules('nama_pihak', 'Nama pihak', 'required|trim');
        $this->form_validation->set_rules('status_bayar', 'Status bayar', 'required|in_list[lunas,hutang,piutang]');
        $this->form_validation->set_rules('no_hp', 'No HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('potongan', 'Potongan', 'numeric|greater_than_equal_to[0]');

        if (!$this->form_validation->run()) {
            return $this->respond_gagal(implode('<br>', $this->form_validation->error_array()), function () use ($id) {
                $this->edit($id);
            });
        }

        $item_error = '';
        $items = $this->collect_items($item_error);
        if (empty($items)) {
            return $this->respond_gagal($item_error !== '' ? $item_error : 'Minimal satu barang harus diisi.', function () use ($id) {
                $this->edit($id);
            });
        }

        $header = array(
            'tipe' => $this->input->post('tipe', true),
            'nama_pihak' => trim((string) $this->input->post('nama_pihak', true)),
            'no_hp' => trim((string) $this->input->post('no_hp', true)),
            'status_bayar' => $this->input->post('status_bayar', true),
            'potongan' => $this->input->post('potongan', true),
            'catatan_potongan' => trim((string) $this->input->post('catatan_potongan', true)),
        );

        $result = $this->Transaksi_model->update_transaksi($id, $header, $items);
        if (!$result['success']) {
            return $this->respond_gagal($result['message'], function () use ($id) {
                $this->edit($id);
            });
        }

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => true,
                    'no_nota' => $result['no_nota'],
                    'cetak_url' => base_url('Transaksi/cetak/' . $id),
                )));
            return;
        }

        $this->session->set_flashdata('success', 'Transaksi ' . $result['no_nota'] . ' berhasil diperbarui.');
        redirect('Transaksi/cetak/' . $id);
    }

    public function hapus($id)
    {
        cek_akses('transaksi', 'delete');

        $result = $this->Transaksi_model->hapus_transaksi($id);

        if ($result['success']) {
            $this->session->set_flashdata('success', 'Transaksi ' . $result['no_nota'] . ' berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('transaksi');
    }

    /**
     * Endpoint AJAX untuk tombol expand di daftar transaksi -- kirim detail barang
     * cuma pas di-klik (bukan sekaligus semua saat halaman dimuat), supaya ringan.
     */
    public function detail_ajax($id)
    {
        cek_akses('transaksi', 'read');

        $transaksi = $this->Transaksi_model->get_transaksi_by_id($id);
        if (!$transaksi) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Transaksi tidak ditemukan.')));
            return;
        }

        $detail = $this->Transaksi_model->get_transaksi_detail($id);
        $items = array();
        foreach ($detail as $d) {
            $items[] = array(
                'nama_barang' => $d->nama_barang,
                'satuan' => $d->satuan,
                'qty' => (float) $d->qty,
                'harga_satuan' => (float) $d->harga_satuan,
                'subtotal' => (float) $d->subtotal,
            );
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'items' => $items,
                'total' => (float) $transaksi->total,
                'potongan' => (float) $transaksi->potongan,
                'total_bayar' => (float) $transaksi->total - (float) $transaksi->potongan,
            )));
    }

    private function build_form_rows()
    {
        return array(array('barang_id' => '', 'qty' => '', 'harga_satuan' => ''));
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

            $items[] = array('barang_id' => $barang_id, 'qty' => $qty, 'harga_satuan' => $harga_satuan);
        }

        return $items;
    }
}