<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengguna extends MY_Controller
{
    protected $active_menu = 'pengguna';

    public function __construct()
    {
        parent::__construct();

        // Modul ini sengaja TIDAK pakai has_akses()/cek_akses() biasa seperti modul lain --
        // mengatur hak akses orang lain adalah kewenangan di atas modul CRUD biasa,
        // jadi cek langsung ke role, bukan ke matrix hak akses.
        if ($this->session->userdata('role') !== 'it_admin') {
            $this->session->set_flashdata('error', 'Halaman ini khusus untuk IT admin.');
            redirect('Dashboard');
        }

        $this->load->model('Pengguna_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['page_title'] = 'Manajemen Pengguna';
        $data['daftar_user'] = $this->Pengguna_model->get_all_users();

        $this->render('pengguna/index', $data);
    }

    public function simpan() {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[4]');
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[it_admin,admin,owner]');

        if (empty($id)) {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        }

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', implode('<br>', $this->form_validation->error_array()));
            redirect('pengguna');
        }

        $username = trim($this->input->post('username', true));

        $existing = $this->Pengguna_model->get_by_username($username);
        if ($existing && (empty($id) || (int) $existing->id !== (int) $id)) {
            $this->session->set_flashdata('error', 'Username sudah dipakai, pilih username lain.');
            redirect('pengguna');
        }

        $data = array(
            'username' => $username,
            'nama'     => trim($this->input->post('nama', true)),
            'role'     => $this->input->post('role'),
        );

        $password = $this->input->post('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!empty($id)) {
            $this->Pengguna_model->update_user($id, $data);
            $this->session->set_flashdata('success', 'Data pengguna berhasil diperbarui.');
        } else {
            $this->Pengguna_model->simpan_user($data);
            $this->session->set_flashdata('success', 'Pengguna baru berhasil ditambahkan.');
        }

        redirect('pengguna');
    }

    public function hapus($id)
    {
        if ((int) $id === (int) $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Tidak bisa menghapus akun sendiri yang sedang login.');
            redirect('Pengguna');
        }

        $this->Pengguna_model->hapus_user($id);
        $this->session->set_flashdata('success', 'Pengguna berhasil dihapus.');
        redirect('Pengguna');
    }

    public function akses($id)
    {
        $user = $this->Pengguna_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $data['page_title'] = 'Hak Akses';
        $data['user'] = $user;
        $data['daftar_modul'] = daftar_modul();
        $data['akses'] = $this->Pengguna_model->get_akses_untuk_form($id);

        $this->render('pengguna/akses', $data);
    }

    public function simpan_akses($id) {
        $user = $this->Pengguna_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $input_akses = (array) $this->input->post('akses');
        $this->Pengguna_model->simpan_akses($id, $input_akses);

        $this->session->set_flashdata('success', 'Hak akses ' . $user->nama . ' berhasil disimpan.');
        redirect('pengguna/akses/' . $id);
    }
}