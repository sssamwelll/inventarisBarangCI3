<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/fpdf185/fpdf.php';

/**
 * Laporan_pdf
 * Cetak laporan ringkasan usaha (A4) -- beda dari Nota_pdf/Slip_gaji_pdf yang pakai
 * ukuran struk 80mm, laporan ini untuk diprint biasa & diserahkan ke pemilik usaha.
 */
class Laporan_pdf extends FPDF
{
    private $data;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetAutoPageBreak(true, 15);
        $this->SetMargins(15, 15, 15);
    }

    public function set_data($data)
    {
        $this->data = $data;
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, 'KARYA LIMBAH JAYA', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Laporan Usaha', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 9);
        $periode = date('d F Y', strtotime($this->data['tanggal_dari'])) . ' - ' . date('d F Y', strtotime($this->data['tanggal_sampai']));
        $this->Cell(0, 6, $periode, 0, 1, 'C');
        $this->Ln(3);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' - Dicetak otomatis ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    public function cetak_isi()
    {
        $d = $this->data;

        // --- Ringkasan ---
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, 'Ringkasan', 0, 1);
        $this->Ln(1);

        $this->baris_ringkasan('Total Pembelian (' . $d['nota_beli'] . ' nota)', rupiah($d['total_beli']));
        $this->baris_ringkasan('Total Penjualan (' . $d['nota_jual'] . ' nota)', rupiah($d['total_jual']));
        $this->baris_ringkasan('Laba Kotor', rupiah($d['laba_kotor']), true);
        $this->Ln(2);
        $this->baris_ringkasan('Pengeluaran Gaji', '-' . rupiah($d['kas_keluar_gaji'], ''));
        $this->baris_ringkasan('Pengeluaran Operasional', '-' . rupiah($d['kas_keluar_operasional'], ''));
        $this->baris_ringkasan('Pengeluaran Lainnya', '-' . rupiah($d['kas_keluar_lainnya'], ''));
        $this->baris_ringkasan('Laba Bersih', rupiah($d['laba_bersih']), true);
        $this->Ln(6);

        // --- Breakdown per kategori barang ---
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, 'Breakdown per Kategori Barang', 0, 1);
        $this->Ln(1);

        $this->SetFont('Arial', 'B', 8.5);
        $this->SetFillColor(235, 235, 235);
        $this->Cell(50, 6, 'Kategori', 1, 0, 'L', true);
        $this->Cell(32, 6, 'Qty Beli', 1, 0, 'R', true);
        $this->Cell(33, 6, 'Nominal Beli', 1, 0, 'R', true);
        $this->Cell(32, 6, 'Qty Jual', 1, 0, 'R', true);
        $this->Cell(33, 6, 'Nominal Jual', 1, 1, 'R', true);

        $this->SetFont('Arial', '', 8.5);
        if (empty($d['breakdown_kategori'])) {
            $this->Cell(180, 6, 'Tidak ada transaksi pada periode ini.', 1, 1, 'C');
        } else {
            foreach ($d['breakdown_kategori'] as $b) {
                $this->Cell(50, 6, $b['nama_kategori'], 1, 0, 'L');
                $this->Cell(32, 6, angka($b['qty_beli'], 1), 1, 0, 'R');
                $this->Cell(33, 6, rupiah($b['nominal_beli'], ''), 1, 0, 'R');
                $this->Cell(32, 6, angka($b['qty_jual'], 1), 1, 0, 'R');
                $this->Cell(33, 6, rupiah($b['nominal_jual'], ''), 1, 1, 'R');
            }
        }
        $this->Ln(6);

        // --- Rekap harian ---
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, 'Rekap Harian', 0, 1);
        $this->Ln(1);

        $this->SetFont('Arial', 'B', 8.5);
        $this->SetFillColor(235, 235, 235);
        $this->Cell(35, 6, 'Tanggal', 1, 0, 'L', true);
        $this->Cell(50, 6, 'Total Beli', 1, 0, 'R', true);
        $this->Cell(50, 6, 'Total Jual', 1, 0, 'R', true);
        $this->Cell(45, 6, 'Laba Harian', 1, 1, 'R', true);

        $this->SetFont('Arial', '', 8.5);
        if (empty($d['rekap_harian'])) {
            $this->Cell(180, 6, 'Tidak ada transaksi pada periode ini.', 1, 1, 'C');
        } else {
            foreach ($d['rekap_harian'] as $h) {
                $laba = $h['total_jual'] - $h['total_beli'];
                $this->Cell(35, 6, date('d/m/Y', strtotime($h['tanggal'])), 1, 0, 'L');
                $this->Cell(50, 6, rupiah($h['total_beli'], ''), 1, 0, 'R');
                $this->Cell(50, 6, rupiah($h['total_jual'], ''), 1, 0, 'R');
                $this->Cell(45, 6, rupiah($laba, ''), 1, 1, 'R');
            }
        }
    }

    private function baris_ringkasan($label, $nilai, $tebal = false)
    {
        $this->SetFont('Arial', $tebal ? 'B' : '', 10);
        $this->Cell(90, 6, $label, 0, 0);
        $this->Cell(0, 6, $nilai, 0, 1, 'R');
    }
}