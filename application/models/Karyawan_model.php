<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karyawan_model extends CI_Model {

    private $table = 'karyawan';

    public function get_semua()
    {
        return $this->db->order_by('status', 'ASC')
            ->order_by('nama', 'ASC')
            ->get($this->table)->result();
    }

    public function get_semua_aktif()
    {
        return $this->db->where('status', 'aktif')
            ->order_by('nama', 'ASC')
            ->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function simpan_baru($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update_data($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function set_status($id, $status)
    {
        return $this->db->where('id', $id)->update($this->table, array('status' => $status));
    }
}