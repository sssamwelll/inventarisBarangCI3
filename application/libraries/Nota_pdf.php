<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/fpdf185/fpdf.php';

/**
 * Nota_pdf
 * Turunan FPDF khusus untuk mencetak nota transaksi beli/jual.
 * Ukuran kertas: struk 80mm (lebar 80mm, tinggi 200mm — otomatis nambah halaman
 * kalau isi lebih panjang, karena AutoPageBreak aktif).
 *
 * Cara pakai (dari controller):
 *   require_once APPPATH . 'libraries/Nota_pdf.php';
 *   $pdf = new Nota_pdf('P', 'mm', array(80, 200));
 *   $pdf->set_data($transaksi, $detail);
 *   $pdf->AddPage();
 *   $pdf->cetak_isi();
 *   $pdf->Output('I', 'Nota-' . $transaksi->no_nota . '.pdf');
 */
class Nota_pdf extends FPDF
{
    private $transaksi;
    private $detail;

    public function __construct($orientation = 'P', $unit = 'mm', $size = array(80, 200))
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetAutoPageBreak(true, 12);
        $this->SetMargins(5, 5, 5);
    }

    public function set_data($transaksi, $detail)
    {
        $this->transaksi = $transaksi;
        $this->detail = $detail;
    }

    // Dipanggil otomatis oleh FPDF setiap AddPage()
    public function Header()
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, 'KARYA LIMBAH JAYA', 0, 1, 'C');

        $this->SetFont('Arial', '', 7);
        $tipe_label = isset($this->transaksi->tipe) ? ucfirst($this->transaksi->tipe) : '';
        $this->Cell(0, 4, 'Nota ' . $tipe_label . ' Barang', 0, 1, 'C');

        $this->Ln(1);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(5, $this->GetY(), $this->GetPageWidth() - 5, $this->GetY());
        $this->Ln(2);
    }

    // Dipanggil otomatis oleh FPDF di setiap akhir halaman
    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 6);
        $this->Cell(0, 4, 'Dicetak otomatis - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    /**
     * Isi utama nota: info transaksi, tabel barang, total, tanda tangan.
     * Lebar konten = 80mm - margin kiri kanan (5+5) = 70mm.
     */
    public function cetak_isi()
    {
        $t = $this->transaksi;

        $this->SetFont('Arial', '', 8);
        $this->Cell(18, 4, 'No. Nota', 0, 0);
        $this->Cell(0, 4, ': ' . $t->no_nota, 0, 1);

        $this->Cell(18, 4, 'Tanggal', 0, 0);
        $this->Cell(0, 4, ': ' . date('d/m/Y H:i', strtotime($t->tanggal)), 0, 1);

        $this->Cell(18, 4, 'Pihak', 0, 0);
        $this->Cell(0, 4, ': ' . $t->nama_pihak, 0, 1);

        if (!empty($t->no_hp)) {
            $this->Cell(18, 4, 'No HP', 0, 0);
            $this->Cell(0, 4, ': ' . $t->no_hp, 0, 1);
        }

        $this->Cell(18, 4, 'Status', 0, 0);
        $this->Cell(0, 4, ': ' . ucfirst($t->status_bayar), 0, 1);
        $this->Ln(2);

        // Header tabel item (28 + 12 + 17 + 13 = 70mm, pas dengan lebar konten)
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(28, 5, 'Barang', 0, 0, 'L', true);
        $this->Cell(12, 5, 'Qty', 0, 0, 'R', true);
        $this->Cell(17, 5, 'Harga', 0, 0, 'R', true);
        $this->Cell(13, 5, 'Subtotal', 0, 1, 'R', true);

        $this->SetFont('Arial', '', 7.5);
        foreach ($this->detail as $d) {
            $nama = $d->nama_barang;
            if (mb_strlen($nama) > 16) {
                $nama = mb_substr($nama, 0, 15) . '.';
            }
            $this->Cell(28, 4.5, $nama, 0, 0, 'L');
            $this->Cell(12, 4.5, angka($d->qty), 0, 0, 'R');
            $this->Cell(17, 4.5, angka($d->harga_satuan), 0, 0, 'R');
            $this->Cell(13, 4.5, angka($d->subtotal), 0, 1, 'R');
        }

        $this->Ln(1);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(5, $this->GetY(), $this->GetPageWidth() - 5, $this->GetY());
        $this->Ln(2);

        if ($t->potongan > 0) {
            $this->SetFont('Arial', '', 8);
            $this->Cell(45, 4, 'Total Barang', 0, 0, 'R');
            $this->Cell(25, 4, rupiah($t->total, ''), 0, 1, 'R');

            $this->Cell(45, 4, 'Potongan', 0, 0, 'R');
            $this->Cell(25, 4, '-' . rupiah($t->potongan, ''), 0, 1, 'R');

            $this->SetFont('Arial', 'B', 9);
            $this->Cell(45, 5, 'TOTAL DIBAYAR', 0, 0, 'R');
            $this->Cell(25, 5, rupiah($t->total - $t->potongan), 0, 1, 'R');
        } else {
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(45, 5, 'TOTAL', 0, 0, 'R');
            $this->Cell(25, 5, rupiah($t->total), 0, 1, 'R');
        }

        // Kolom tanda tangan
        $lawan = $t->tipe === 'beli' ? 'Penjual' : 'Pembeli';
        $this->Ln(6);
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(35, 4, 'Admin', 0, 0, 'C');
        $this->Cell(35, 4, $lawan, 0, 1, 'C');
        $this->Ln(12);
        $this->Cell(35, 4, '(' . $t->nama_user . ')', 0, 0, 'C');
        $this->Cell(35, 4, '(' . $t->nama_pihak . ')', 0, 1, 'C');
    }
}