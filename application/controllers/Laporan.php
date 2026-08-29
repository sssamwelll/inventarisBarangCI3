<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends MY_Controller
{
    protected $active_menu = 'laporan';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Laporan_model');
    }

    public function index()
    {
        $tanggal_dari = $this->input->get('tanggal_dari') ?: date('Y-m-01');
        $tanggal_sampai = $this->input->get('tanggal_sampai') ?: date('Y-m-d');

        $data = $this->siapkan_data_laporan($tanggal_dari, $tanggal_sampai);
        $data['page_title'] = 'Laporan';

        $this->render('laporan/index', $data);
    }

    public function cetak()
    {
        $tanggal_dari = $this->input->get('tanggal_dari') ?: date('Y-m-01');
        $tanggal_sampai = $this->input->get('tanggal_sampai') ?: date('Y-m-d');

        $data = $this->siapkan_data_laporan($tanggal_dari, $tanggal_sampai);

        require_once APPPATH . 'libraries/Laporan_pdf.php';

        $pdf = new Laporan_pdf('P', 'mm', 'A4');
        $pdf->set_data($data);
        $pdf->SetTitle('Laporan Usaha Rosok');
        $pdf->AddPage();
        $pdf->cetak_isi();
        $pdf->Output('I', 'Laporan-UsahaRosok-' . $tanggal_dari . '_sd_' . $tanggal_sampai . '.pdf');
    }

    /**
     * Susun semua data laporan dalam satu tempat, dipakai bareng oleh index() (tampilan web)
     * dan cetak() (PDF), supaya angkanya dijamin selalu sama antara yang dilihat di layar
     * dengan yang dicetak.
     */
    private function siapkan_data_laporan($tanggal_dari, $tanggal_sampai)
    {
        // --- Ringkasan transaksi (accrual) ---
        $ringkasan_transaksi = $this->Laporan_model->get_ringkasan_transaksi($tanggal_dari, $tanggal_sampai);
        $total_beli = 0;
        $total_jual = 0;
        $nota_beli = 0;
        $nota_jual = 0;

        foreach ($ringkasan_transaksi as $r) {
            if ($r->tipe === 'beli') {
                $total_beli = (float) $r->total_nominal;
                $nota_beli = (int) $r->jumlah_nota;
            } elseif ($r->tipe === 'jual') {
                $total_jual = (float) $r->total_nominal;
                $nota_jual = (int) $r->jumlah_nota;
            }
        }
        $laba_kotor = $total_jual - $total_beli;

        // --- Realisasi kas (cash basis) ---
        $ringkasan_kas = $this->Laporan_model->get_ringkasan_kas($tanggal_dari, $tanggal_sampai);
        $kas_keluar_gaji = 0;
        $kas_keluar_operasional = 0;
        $kas_keluar_lainnya = 0;

        foreach ($ringkasan_kas as $r) {
            $jumlah = (float) $r->total;
            if ($r->tipe === 'keluar' && $r->kategori === 'gaji') {
                $kas_keluar_gaji = $jumlah;
            } elseif ($r->tipe === 'keluar' && $r->kategori === 'operasional') {
                $kas_keluar_operasional = $jumlah;
            } elseif ($r->tipe === 'keluar' && $r->kategori === 'lainnya') {
                $kas_keluar_lainnya = $jumlah;
            }
        }
        $laba_bersih = $laba_kotor - $kas_keluar_gaji - $kas_keluar_operasional - $kas_keluar_lainnya;

        return array(
            'tanggal_dari'           => $tanggal_dari,
            'tanggal_sampai'         => $tanggal_sampai,
            'total_beli'             => $total_beli,
            'total_jual'             => $total_jual,
            'nota_beli'              => $nota_beli,
            'nota_jual'              => $nota_jual,
            'laba_kotor'             => $laba_kotor,
            'kas_keluar_gaji'        => $kas_keluar_gaji,
            'kas_keluar_operasional' => $kas_keluar_operasional,
            'kas_keluar_lainnya'     => $kas_keluar_lainnya,
            'laba_bersih'            => $laba_bersih,
            'breakdown_kategori'     => $this->susun_breakdown_kategori($tanggal_dari, $tanggal_sampai),
            'rekap_harian'           => $this->susun_rekap_harian($tanggal_dari, $tanggal_sampai),
        );
    }

    private function susun_breakdown_kategori($tanggal_dari, $tanggal_sampai)
    {
        $rows = $this->Laporan_model->get_breakdown_kategori($tanggal_dari, $tanggal_sampai);
        $hasil = array();

        foreach ($rows as $r) {
            if (!isset($hasil[$r->nama_kategori])) {
                $hasil[$r->nama_kategori] = array(
                    'nama_kategori' => $r->nama_kategori,
                    'qty_beli' => 0, 'nominal_beli' => 0,
                    'qty_jual' => 0, 'nominal_jual' => 0,
                );
            }
            if ($r->tipe === 'beli') {
                $hasil[$r->nama_kategori]['qty_beli'] = (float) $r->total_qty;
                $hasil[$r->nama_kategori]['nominal_beli'] = (float) $r->total_nominal;
            } else {
                $hasil[$r->nama_kategori]['qty_jual'] = (float) $r->total_qty;
                $hasil[$r->nama_kategori]['nominal_jual'] = (float) $r->total_nominal;
            }
        }

        return array_values($hasil);
    }

    private function susun_rekap_harian($tanggal_dari, $tanggal_sampai)
    {
        $rows = $this->Laporan_model->get_rekap_harian($tanggal_dari, $tanggal_sampai);
        $hasil = array();

        foreach ($rows as $r) {
            if (!isset($hasil[$r->tgl])) {
                $hasil[$r->tgl] = array('tanggal' => $r->tgl, 'total_beli' => 0, 'total_jual' => 0);
            }
            if ($r->tipe === 'beli') {
                $hasil[$r->tgl]['total_beli'] = (float) $r->total_nominal;
            } else {
                $hasil[$r->tgl]['total_jual'] = (float) $r->total_nominal;
            }
        }

        ksort($hasil);
        return array_values($hasil);
    }
}