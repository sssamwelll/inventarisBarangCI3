<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    
    public function cek_username($username) {
        // Ambil satu baris data user berdasarkan username
        return $this->db->get_where('users', ['username' => $username])->row();
    }

    // public function simpan_user($data) {
    //     return $this->db->insert('users', $data);
    // }
    
}