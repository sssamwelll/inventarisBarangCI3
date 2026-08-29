<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model
{
    public function get_barang_options()
    {
        return $this->db->select('barang.id, barang.nama_barang, barang.satuan, barang.harga_beli, barang.harga_jual, barang.stok, kategori_barang.nama_kategori')
            ->from('barang')
            ->join('kategori_barang', 'kategori_barang.id = barang.kategori_id', 'left')
            ->where('barang.is_aktif', 1)
            ->order_by('kategori_barang.nama_kategori', 'ASC')
            ->order_by('barang.nama_barang', 'ASC')
            ->get()
            ->result();
    }

    public function get_transaksi($limit = 25)
    {
        return $this->db->select('transaksi.*, users.nama AS nama_user')
            ->from('transaksi')
            ->join('users', 'users.id = transaksi.user_id', 'left')
            ->order_by('transaksi.tanggal', 'DESC')
            ->order_by('transaksi.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function get_transaksi_by_id($id)
    {
        return $this->db->select('transaksi.*, users.nama AS nama_user')
            ->from('transaksi')
            ->join('users', 'users.id = transaksi.user_id', 'left')
            ->where('transaksi.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_transaksi_detail($transaksi_id)
    {
        return $this->db->select('transaksi_detail.*, barang.nama_barang, barang.satuan')
            ->from('transaksi_detail')
            ->join('barang', 'barang.id = transaksi_detail.barang_id', 'left')
            ->where('transaksi_detail.transaksi_id', (int) $transaksi_id)
            ->order_by('transaksi_detail.id', 'ASC')
            ->get()
            ->result();
    }

    public function generate_no_nota($tanggal = null)
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $prefix = 'TRX-' . date('Ymd', strtotime($tanggal)) . '-';

        $this->db->like('no_nota', $prefix, 'after');
        $jumlah_hari_ini = $this->db->count_all_results('transaksi');

        return $prefix . str_pad((string) ($jumlah_hari_ini + 1), 4, '0', STR_PAD_LEFT);
    }

    public function save_transaksi(array $header, array $items)
    {
        if (empty($items)) {
            return array('success' => false, 'message' => 'Transaksi harus memiliki minimal satu item.');
        }

        $tipe = isset($header['tipe']) ? $header['tipe'] : '';
        $status_bayar = isset($header['status_bayar']) ? $header['status_bayar'] : 'lunas';
        $nama_pihak = isset($header['nama_pihak']) ? trim((string) $header['nama_pihak']) : '';
        $no_hp = isset($header['no_hp']) ? trim((string) $header['no_hp']) : null;

        if ($nama_pihak === '') {
            return array('success' => false, 'message' => 'Nama pihak wajib diisi.');
        }

        $user_id = (int) $this->session->userdata('user_id');
        if ($user_id <= 0) {
            return array('success' => false, 'message' => 'Sesi login belum tersedia. Silakan login ulang.');
        }

        $this->db->trans_begin();

        $tanggal_sekarang = date('Y-m-d H:i:s');
        $no_nota = $this->generate_no_nota($tanggal_sekarang);
        $total = 0;
        $normalized_items = array();

        foreach ($items as $item) {
            $barang_id = isset($item['barang_id']) ? (int) $item['barang_id'] : 0;
            $qty = isset($item['qty']) ? (float) $item['qty'] : 0;
            $harga_satuan = isset($item['harga_satuan']) ? (float) $item['harga_satuan'] : 0;

            if ($barang_id <= 0 || $qty <= 0) {
                $this->db->trans_rollback();
                return array('success' => false, 'message' => 'Item transaksi belum lengkap.');
            }

            $barang = $this->db->where('id', $barang_id)->where('is_aktif', 1)->get('barang')->row();
            if (!$barang) {
                $this->db->trans_rollback();
                return array('success' => false, 'message' => 'Barang dengan ID ' . $barang_id . ' tidak ditemukan atau tidak aktif.');
            }

            if ($harga_satuan <= 0) {
                $harga_satuan = $tipe === 'beli' ? (float) $barang->harga_beli : (float) $barang->harga_jual;
                if ($harga_satuan <= 0) {
                    $harga_satuan = $tipe === 'beli' ? (float) $barang->harga_jual : (float) $barang->harga_beli;
                }
            }

            if ($harga_satuan <= 0) {
                $this->db->trans_rollback();
                return array('success' => false, 'message' => 'Harga untuk barang ' . $barang->nama_barang . ' belum tersedia.');
            }

            if ($tipe === 'jual' && (float) $barang->stok < $qty) {
                $this->db->trans_rollback();
                return array('success' => false, 'message' => 'Stok barang ' . $barang->nama_barang . ' tidak cukup.');
            }

            $subtotal = $qty * $harga_satuan;
            $total += $subtotal;

            $normalized_items[] = array(
                'barang_id' => $barang_id,
                'qty' => $qty,
                'harga_satuan' => $harga_satuan,
                'subtotal' => $subtotal,
            );
        }

        $transaksi_data = array(
            'no_nota' => $no_nota,
            'tipe' => $tipe,
            'tanggal' => $tanggal_sekarang,
            'nama_pihak' => $nama_pihak,
            'no_hp' => $no_hp !== '' ? $no_hp : null,
            'status_bayar' => $status_bayar,
            'total' => $total,
            'user_id' => $user_id,
            'created_at' => $tanggal_sekarang,
        );

        $this->db->insert('transaksi', $transaksi_data);
        $transaksi_id = $this->db->insert_id();

        foreach ($normalized_items as $item) {
            $this->db->insert('transaksi_detail', array(
                'transaksi_id' => $transaksi_id,
                'barang_id' => $item['barang_id'],
                'qty' => $item['qty'],
                'harga_satuan' => $item['harga_satuan'],
                'subtotal' => $item['subtotal'],
            ));

            if ($tipe === 'beli') {
                $this->db->set('stok', 'stok + ' . $item['qty'], false)->where('id', $item['barang_id'])->update('barang');
            } else {
                $this->db->set('stok', 'stok - ' . $item['qty'], false)->where('id', $item['barang_id'])->update('barang');
            }
        }

        if ($status_bayar === 'lunas') {
            $kas_tipe = $tipe === 'beli' ? 'keluar' : 'masuk';
            $this->db->insert('kas', array(
                'tanggal' => date('Y-m-d'),
                'tipe' => $kas_tipe,
                'kategori' => 'transaksi',
                'keterangan' => 'Transaksi ' . $tipe . ' ' . $no_nota,
                'jumlah' => $total,
                'ref_id' => $transaksi_id,
                'ref_type' => 'transaksi',
                'user_id' => $user_id,
            ));
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return array('success' => false, 'message' => 'Transaksi gagal disimpan. Silakan coba lagi.');
        }

        $this->db->trans_commit();

        return array(
            'success' => true,
            'transaksi_id' => $transaksi_id,
            'no_nota' => $no_nota,
            'total' => $total,
        );
    }
}
