<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/fpdf185/fpdf.php';

/**
 * Slip_gaji_pdf
 * Turunan FPDF untuk cetak slip gaji mingguan.
 * Struktur sama persis dengan Nota_pdf, supaya mudah dipahami: constructor set margin,
 * Header()/Footer() dipanggil otomatis oleh FPDF, cetak_isi() berisi konten utama.
 */
class Slip_gaji_pdf extends FPDF
{
    private $gaji;

    public function __construct($orientation = 'P', $unit = 'mm', $size = array(80, 200))
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetAutoPageBreak(true, 12);
        $this->SetMargins(5, 5, 5);
    }

    public function set_data($gaji)
    {
        $this->gaji = $gaji;
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'KARYA LIMBAH JAYA', 0, 1, 'C');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 4, 'Slip Gaji Mingguan', 0, 1, 'C');
        $this->Ln(1);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(5, $this->GetY(), $this->GetPageWidth() - 5, $this->GetY());
        $this->Ln(2);
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 6);
        $this->Cell(0, 4, 'Dicetak otomatis - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    public function cetak_isi()
    {
        $g = $this->gaji;

        $this->SetFont('Arial', '', 8);
        $this->Cell(20, 4, 'Nama', 0, 0);
        $this->Cell(0, 4, ': ' . $g->nama, 0, 1);
        $this->Cell(20, 4, 'Jabatan', 0, 0);
        $this->Cell(0, 4, ': ' . ($g->jabatan ?: '-'), 0, 1);
        $this->Cell(20, 4, 'Periode', 0, 0);
        $this->Cell(0, 4, ': ' . date('d/m/Y', strtotime($g->periode_awal)) . ' - ' . date('d/m/Y', strtotime($g->periode_akhir)), 0, 1);
        $this->Ln(2);

        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(45, 5, 'Komponen', 0, 0, 'L', true);
        $this->Cell(25, 5, 'Jumlah', 0, 1, 'R', true);

        $this->SetFont('Arial', '', 7.5);
        $this->Cell(45, 4.5, 'Hadir (' . angka($g->total_hadir) . ' hari)', 0, 0);
        $this->Cell(25, 4.5, rupiah($g->gaji_pokok), 0, 1, 'R');

        $this->Cell(45, 4.5, 'Lembur (' . angka($g->total_jam_lembur, 2) . ' jam)', 0, 0);
        $this->Cell(25, 4.5, rupiah($g->tunjangan_lembur), 0, 1, 'R');

        if ($g->potongan > 0) {
            $this->Cell(45, 4.5, 'Potongan', 0, 0);
            $this->Cell(25, 4.5, '-' . rupiah($g->potongan, ''), 0, 1, 'R');
        }

        $this->Ln(1);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(5, $this->GetY(), $this->GetPageWidth() - 5, $this->GetY());
        $this->Ln(2);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 5, 'TOTAL DITERIMA', 0, 0, 'R');
        $this->Cell(25, 5, rupiah($g->total_gaji), 0, 1, 'R');

        $this->Ln(8);
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(35, 4, 'Admin', 0, 0, 'C');
        $this->Cell(35, 4, 'Karyawan', 0, 1, 'C');
        $this->Ln(12);
        $this->Cell(35, 4, '('. $t->nama . ')', 0, 0, 'C');
        $this->Cell(35, 4, '(' . $g->nama . ')', 0, 1, 'C');
    }
}