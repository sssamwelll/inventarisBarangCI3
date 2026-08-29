<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    // Menampilkan Halaman Login
    public function index() {
        // Jika session sudah ada, langsung tendang ke Dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('Auth/login');
    }

    // SEMENTARA
    // public function daftar() {
    //     $username = $this->input->get('username');
    //     $password = $this->input->get('password');
        
    //     $hash = password_hash($password, PASSWORD_DEFAULT);

    //     $data_daftar = [
    //         'id' => 223240041,
    //         'username' => $username,
    //         'password' => $hash,
    //         'nama' => 'Kristina',
    //         'role' => 'admin'
    //     ];

    //     $this->User_model->simpan_user($data_daftar);
    // }

    // Memproses Inputan Form Login
    public function process() {
        $username = $this->input->post('username', true);
        $password = $this->input->post('password', true);

        $user = $this->User_model->cek_username($username);

        if ($user) {
            // Verifikasi kecocokan hash password
            if (password_verify($password, $user->password)) {
                
                // Daftarkan session
                $session_data = array(
                    'user_id'   => $user->id,
                    'username'  => $user->username,
                    'nama'      => $user->nama,
                    'role'      => $user->role,
                    'logged_in' => TRUE
                );
                
                $this->session->set_userdata($session_data);
                redirect('Dashboard');
                
            } else {
                $this->session->set_flashdata('error', 'Password yang Anda masukkan salah!');
                redirect('Auth');
            }
        } else {
            $this->session->set_flashdata('error', 'Username tidak terdaftar!');
            redirect('Auth');
        }
    }

    // Memproses Logout
    public function logout() {
        $this->session->sess_destroy();
        redirect('Auth');
    }
}