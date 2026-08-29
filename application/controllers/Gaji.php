<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gaji extends MY_Controller
{
    protected $active_menu = 'gaji';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Gaji_model');
        $this->load->model('Karyawan_model');
        $this->load->model('Absensi_model');
    }

    public function index()
    {
        
        $filter = array(
            'admin' => $this->input->get('admin'),
            'periode_awal' => $this->input->get('periode_awal'),
            'periode_akhir' => $this->input->get('periode_akhir'),
            'karyawan_id' => $this->input->get('karyawan_id'),
        );

        $data['page_title'] = 'Penggajian';
        $data['filter'] = $filter;
        $data['riwayat'] = $this->Gaji_model->get_riwayat($filter);
        $data['karyawan_list'] = $this->Karyawan_model->get_semua_aktif();
        $this->render('Gaji/index', $data);
    }

    public function cetak($id)
    {
        $gaji = $this->Gaji_model->get_by_id($id);
        if (!$gaji) {
            show_404();
        }

        require_once APPPATH . 'libraries/Slip_gaji_pdf.php';

        $pdf = new Slip_gaji_pdf('P', 'mm', array(80, 200));
        $pdf->set_data($gaji);
        $pdf->SetTitle('Slip Gaji - ' . $gaji->nama);
        $pdf->AddPage();
        $pdf->cetak_isi();
        $pdf->Output('I', 'Slip-Gaji-' . str_replace(' ', '_', $gaji->nama) . '-' . $gaji->periode_awal . '.pdf');
    }

    public function proses()
    {
        $periode_awal = $this->input->get('periode_awal') ?: date('Y-m-d', strtotime('monday this week'));
        $periode_akhir = $this->input->get('periode_akhir') ?: date('Y-m-d', strtotime('saturday this week'));

        $karyawan_list = $this->Karyawan_model->get_semua_aktif();
        $preview = array();

        foreach ($karyawan_list as $k) {
            $sudah = $this->Gaji_model->sudah_diproses($k->id, $periode_awal, $periode_akhir);
            $rekap = $this->Absensi_model->get_rekap($k->id, $periode_awal, $periode_akhir);

            $preview[] = array(
                'karyawan'         => $k,
                'sudah_diproses'   => (bool) $sudah,
                'total_hadir'      => $rekap ? (int) $rekap->total_hadir : 0,
                'total_jam_lembur' => $rekap ? (float) $rekap->total_jam_lembur : 0,
            );
        }

        $data['page_title'] = 'Proses Gaji Mingguan';
        $data['periode_awal'] = $periode_awal;
        $data['periode_akhir'] = $periode_akhir;
        $data['preview'] = $preview;

        $this->render('gaji/proses', $data);
    }

    public function simpan()
    {
        $periode_awal = $this->input->post('periode_awal');
        $periode_akhir = $this->input->post('periode_akhir');
        $karyawan_ids = (array) $this->input->post('karyawan_id');
        $potongans = (array) $this->input->post('potongan');
        $dipilih = (array) $this->input->post('bayar');

        if (empty($periode_awal) || empty($periode_akhir) || empty($karyawan_ids)) {
            $this->session->set_flashdata('error', 'Periode/karyawan tidak lengkap.');
            redirect('gaji/proses');
        }

        $diproses = 0;
        $dilewati = 0;

        foreach ($karyawan_ids as $i => $karyawan_id) {
            $karyawan_id = (int) $karyawan_id;

            // Hanya proses baris yang dicentang admin
            if (!in_array((string) $karyawan_id, $dipilih, true)) {
                continue;
            }

            // Cek ulang di server -- jangan percaya status "sudah_diproses" dari form,
            // karena bisa saja diproses karyawan lain di tab berbeda barusan.
            if ($this->Gaji_model->sudah_diproses($karyawan_id, $periode_awal, $periode_akhir)) {
                $dilewati++;
                continue;
            }

            $karyawan = $this->Karyawan_model->get_by_id($karyawan_id);
            if (!$karyawan) {
                continue;
            }

            $rekap = $this->Absensi_model->get_rekap($karyawan_id, $periode_awal, $periode_akhir);
            $total_hadir = $rekap ? (int) $rekap->total_hadir : 0;
            $total_jam_lembur = $rekap ? (float) $rekap->total_jam_lembur : 0;
            $potongan = isset($potongans[$i]) ? (float) $potongans[$i] : 0;

            $hasil = $this->Gaji_model->proses_dan_bayar(
                $karyawan, $periode_awal, $periode_akhir,
                $total_hadir, $total_jam_lembur, $potongan,
                $this->session->userdata('user_id')
            );

            if ($hasil !== false) {
                $diproses++;
            }
        }

        $pesan = $diproses . ' karyawan berhasil digaji.';
        if ($dilewati > 0) {
            $pesan .= ' ' . $dilewati . ' dilewati (sudah pernah digaji periode ini).';
        }
        $this->session->set_flashdata('success', $pesan);
        redirect('gaji');
    }
}