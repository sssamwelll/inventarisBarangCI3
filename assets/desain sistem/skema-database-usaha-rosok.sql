-- =========================================================
-- Skema Database: Aplikasi Admin Usaha Rosok
-- Untuk PHP CodeIgniter 3 + MySQL
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_usaha_rosok CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_usaha_rosok;

-- ---------------------------------------------------------
-- Tabel: users
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin','owner') NOT NULL DEFAULT 'admin',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: kategori_barang
-- ---------------------------------------------------------
CREATE TABLE kategori_barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: barang
-- ---------------------------------------------------------
CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL DEFAULT 'kg',
    harga_beli DECIMAL(15,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_aktif TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_barang_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_barang(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: transaksi
-- ---------------------------------------------------------
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_nota VARCHAR(20) NOT NULL UNIQUE,
    tipe ENUM('beli','jual') NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nama_pihak VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    status_bayar ENUM('lunas','hutang','piutang') NOT NULL DEFAULT 'lunas',
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: transaksi_detail
-- ---------------------------------------------------------
CREATE TABLE transaksi_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    barang_id INT NOT NULL,
    qty DECIMAL(15,2) NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    CONSTRAINT fk_detail_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    CONSTRAINT fk_detail_barang FOREIGN KEY (barang_id) REFERENCES barang(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: kas
-- ---------------------------------------------------------
CREATE TABLE kas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    tipe ENUM('masuk','keluar') NOT NULL,
    kategori ENUM('transaksi','gaji','operasional','lainnya') NOT NULL DEFAULT 'lainnya',
    keterangan VARCHAR(255) DEFAULT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    ref_id INT DEFAULT NULL,
    ref_type VARCHAR(20) DEFAULT NULL,
    user_id INT NOT NULL,
    CONSTRAINT fk_kas_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: karyawan
-- ---------------------------------------------------------
CREATE TABLE karyawan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(50) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    tanggal_masuk DATE DEFAULT NULL,
    gaji_pokok DECIMAL(15,2) NOT NULL DEFAULT 0,
    tarif_lembur DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: absensi
-- ---------------------------------------------------------
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    karyawan_id INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('hadir','izin','sakit','alpa') NOT NULL DEFAULT 'hadir',
    jam_masuk TIME DEFAULT NULL,
    jam_pulang TIME DEFAULT NULL,
    jam_lembur DECIMAL(5,2) NOT NULL DEFAULT 0,
    keterangan VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_absensi_karyawan FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
    UNIQUE KEY uq_absensi_harian (karyawan_id, tanggal)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: gaji
-- ---------------------------------------------------------
CREATE TABLE gaji (
    id INT AUTO_INCREMENT PRIMARY KEY,
    karyawan_id INT NOT NULL,
    periode_awal DATE NOT NULL,
    periode_akhir DATE NOT NULL,
    total_hadir INT NOT NULL DEFAULT 0,
    total_jam_lembur DECIMAL(6,2) NOT NULL DEFAULT 0,
    gaji_pokok DECIMAL(15,2) NOT NULL DEFAULT 0,
    tunjangan_lembur DECIMAL(15,2) NOT NULL DEFAULT 0,
    potongan DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_gaji DECIMAL(15,2) NOT NULL DEFAULT 0,
    status_bayar ENUM('belum','sudah') NOT NULL DEFAULT 'belum',
    tanggal_bayar DATE DEFAULT NULL,
    user_id INT NOT NULL,
    CONSTRAINT fk_gaji_karyawan FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
    CONSTRAINT fk_gaji_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Data awal (contoh)
-- ---------------------------------------------------------
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$abcdefghijklmnopqrstuv', 'Admin Rosok', 'admin');
-- Catatan: ganti hash password di atas dengan hasil password_hash() PHP yang sebenarnya sebelum digunakan.

INSERT INTO kategori_barang (nama_kategori) VALUES
('Besi'), ('Kertas/Kardus'), ('Plastik'), ('Logam Non-Besi'), ('Lainnya');
