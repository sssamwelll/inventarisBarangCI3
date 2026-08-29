# Desain Sistem — Aplikasi Admin Usaha Rosok

Stack: PHP CodeIgniter 3, Bootstrap 5.3.8, jQuery 3.6.0, Font Awesome 6, Google Font Poppins, FPDF 1.85, JavaScript.

---

## 1. Ringkasan Kebutuhan

Admin (kakak Anda) bertanggung jawab atas:
1. Mencatat transaksi beli/jual barang rosok (multi-item per nota, harga beda tiap barang)
2. Menghitung otomatis subtotal per item dan total per nota
3. Mencatat pembayaran (bayar ke penjual / terima dari pembeli)
4. Mencatat absensi & lembur karyawan
5. Menggaji karyawan berdasarkan absensi + lembur

Dari sini, sistem dipecah menjadi 6 modul: **Transaksi**, **Barang/Stok**, **Kas**, **Karyawan & Absensi**, **Penggajian**, **Laporan** — plus modul pendukung **User/Login**.

---

## 2. ERD (Entity Relationship Diagram)

### Daftar Tabel

**users**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| username | VARCHAR(50) UNIQUE | |
| password | VARCHAR(255) | di-hash pakai `password_hash()` |
| nama | VARCHAR(100) | |
| role | ENUM('admin','owner') | |
| created_at | DATETIME | |

**kategori_barang**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| nama_kategori | VARCHAR(50) | mis. Besi, Kertas, Plastik, Logam |

**barang**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| kategori_id | INT FK → kategori_barang | |
| nama_barang | VARCHAR(100) | |
| satuan | VARCHAR(20) | kg / pcs / lusin |
| harga_beli | DECIMAL(15,2) | harga acuan terbaru (bisa berubah) |
| harga_jual | DECIMAL(15,2) | harga acuan terbaru |
| stok | DECIMAL(15,2) | update otomatis dari transaksi |
| is_aktif | TINYINT(1) | soft-delete barang lama |

> Harga acuan di tabel `barang` HANYA untuk auto-fill saat transaksi baru. Harga final yang dipakai per transaksi disimpan terpisah di `transaksi_detail`, supaya nota lama tidak berubah walau harga acuan diupdate.

**transaksi**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| no_nota | VARCHAR(20) UNIQUE | format: TRX-YYYYMMDD-0001 |
| tipe | ENUM('beli','jual') | |
| tanggal | DATETIME | |
| nama_pihak | VARCHAR(100) | nama penjual/pembeli |
| no_hp | VARCHAR(20) | opsional |
| status_bayar | ENUM('lunas','hutang','piutang') | |
| total | DECIMAL(15,2) | hasil SUM semua subtotal detail |
| user_id | INT FK → users | admin yang input |
| created_at | DATETIME | |

**transaksi_detail**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| transaksi_id | INT FK → transaksi | |
| barang_id | INT FK → barang | |
| qty | DECIMAL(15,2) | |
| harga_satuan | DECIMAL(15,2) | harga saat transaksi (bisa diedit manual) |
| subtotal | DECIMAL(15,2) | qty × harga_satuan |

**kas**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| tanggal | DATE | |
| tipe | ENUM('masuk','keluar') | |
| kategori | ENUM('transaksi','gaji','operasional','lainnya') | |
| keterangan | VARCHAR(255) | |
| jumlah | DECIMAL(15,2) | |
| ref_id | INT NULL | id transaksi/gaji terkait (polymorphic ringan) |
| ref_type | VARCHAR(20) NULL | 'transaksi' / 'gaji' |
| user_id | INT FK → users | |

**karyawan**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| nama | VARCHAR(100) | |
| jabatan | VARCHAR(50) | |
| no_hp | VARCHAR(20) | |
| tanggal_masuk | DATE | |
| gaji_pokok | DECIMAL(15,2) | per bulan atau per hari, sesuaikan kebijakan |
| tarif_lembur | DECIMAL(15,2) | per jam |
| status | ENUM('aktif','nonaktif') | |

**absensi**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| karyawan_id | INT FK → karyawan | |
| tanggal | DATE | |
| status | ENUM('hadir','izin','sakit','alpa') | |
| jam_masuk | TIME NULL | |
| jam_pulang | TIME NULL | |
| jam_lembur | DECIMAL(5,2) | jumlah jam lembur hari itu |
| keterangan | VARCHAR(255) NULL | |

**gaji**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | |
| karyawan_id | INT FK → karyawan | |
| periode_awal | DATE | |
| periode_akhir | DATE | |
| total_hadir | INT | dihitung dari absensi |
| total_jam_lembur | DECIMAL(6,2) | dihitung dari absensi |
| gaji_pokok | DECIMAL(15,2) | snapshot saat digaji |
| tunjangan_lembur | DECIMAL(15,2) | total_jam_lembur × tarif_lembur |
| potongan | DECIMAL(15,2) | opsional (alpa dsb) |
| total_gaji | DECIMAL(15,2) | gaji_pokok + tunjangan_lembur - potongan |
| status_bayar | ENUM('belum','sudah') | |
| tanggal_bayar | DATE NULL | |
| user_id | INT FK → users | |

Relasi utama:
- `kategori_barang` 1—N `barang`
- `barang` 1—N `transaksi_detail`
- `transaksi` 1—N `transaksi_detail`, dan 1—1 `kas` (opsional, saat lunas)
- `karyawan` 1—N `absensi`, 1—N `gaji`
- `gaji` 1—1 `kas` (saat dibayarkan)
- `users` 1—N `transaksi`, `kas`, `gaji` (siapa yang input)

*(Diagram visual ERD sudah ditampilkan di chat)*

---

## 3. DFD & Flowchart

- **DFD Context Diagram**: menunjukkan 4 entitas eksternal (Admin, Owner, Supplier/Pembeli, Karyawan) yang bertukar data dengan sistem — sudah ditampilkan di chat.
- **Flowchart transaksi**: alur input transaksi beli/jual dari login sampai cetak nota — sudah ditampilkan di chat.
- **DFD Level 1** (proses turunan, bisa dikembangkan lagi saat implementasi):
  1. Proses Kelola Transaksi → data store: `transaksi`, `transaksi_detail`, `barang` (update stok)
  2. Proses Kelola Kas → data store: `kas`
  3. Proses Kelola Absensi → data store: `absensi`, `karyawan`
  4. Proses Kelola Penggajian → data store: `gaji`, ambil dari `absensi`, tulis ke `kas`
  5. Proses Laporan → baca semua data store, tidak menulis

### Flowchart Penggajian (ringkas)
1. Admin pilih karyawan & periode (misal 1 bulan)
2. Sistem tarik data `absensi` pada periode tsb → hitung total hadir & total jam lembur
3. Sistem hitung: `gaji_pokok (prorata jika perlu) + (total_jam_lembur × tarif_lembur) − potongan`
4. Admin review & konfirmasi → simpan ke `gaji`
5. Saat dibayar → sistem catat otomatis ke `kas` (tipe keluar, kategori gaji)
6. Cetak slip gaji (FPDF)

---

## 4. Rancangan Antarmuka (Wireframe — deskripsi struktur halaman)

### Layout umum
- Sidebar kiri (menu: Dashboard, Transaksi, Barang, Kas, Karyawan, Absensi, Gaji, Laporan, Logout)
- Topbar: nama user login, tanggal
- Font: Poppins, warna netral + 1 warna aksen (mis. hijau tua khas rosok/industri)

### Halaman kunci
1. **Dashboard** — kartu ringkasan (total transaksi hari ini, saldo kas, jumlah karyawan hadir), grafik sederhana (opsional pakai Chart.js)
2. **Form Transaksi** — dropdown tipe (beli/jual), input nama pihak, tabel dinamis tambah-barang (search barang via jQuery autocomplete, qty, harga otomatis muncul tapi bisa diedit, subtotal otomatis), baris "Total" di bawah otomatis terupdate tiap ada perubahan (pakai JS), tombol Simpan & Cetak Nota
3. **Master Barang** — tabel datatable (search, sort), tombol tambah/edit/nonaktifkan barang, riwayat harga opsional
4. **Kas** — tabel mutasi kas dengan filter tanggal, saldo berjalan
5. **Karyawan** — daftar karyawan, tombol tambah/edit
6. **Absensi** — form input harian per karyawan (bisa checklist per hari dalam 1 bulan), atau input satu-satu
7. **Penggajian** — pilih karyawan + periode → sistem tampilkan rekap otomatis (hadir, lembur) → tombol proses gaji → cetak slip (FPDF)
8. **Laporan** — filter tanggal, export PDF/Excel

*(Saya bisa buatkan mockup visual untuk halaman tertentu — tinggal sebutkan halaman mana yang mau dilihat dulu.)*

---

## 5. Struktur Folder CodeIgniter 3 (usulan)

```
application/
├── controllers/
│   ├── Auth.php
│   ├── Dashboard.php
│   ├── Transaksi.php
│   ├── Barang.php
│   ├── Kas.php
│   ├── Karyawan.php
│   ├── Absensi.php
│   ├── Gaji.php
│   └── Laporan.php
├── models/
│   ├── User_model.php
│   ├── Barang_model.php
│   ├── Transaksi_model.php
│   ├── Kas_model.php
│   ├── Karyawan_model.php
│   ├── Absensi_model.php
│   └── Gaji_model.php
├── views/
│   ├── layout/ (header, sidebar, footer)
│   ├── transaksi/
│   ├── barang/
│   ├── kas/
│   ├── karyawan/
│   ├── absensi/
│   ├── gaji/
│   └── laporan/
├── libraries/
│   └── Fpdf/ (fpdf 1.85)
assets/
├── vendor/bootstrap5/
├── vendor/jquery/
├── vendor/fontawesome6/
└── css/ (custom + poppins font)
```

---

## 6. Catatan Implementasi Penting

- **Perhitungan otomatis** subtotal & total sebaiknya dihitung 2x: di JS (untuk tampilan real-time) DAN di server/model saat simpan (jangan percaya angka dari client — hitung ulang di PHP sebelum insert, untuk keamanan data uang).
- **Stok barang**: update via trigger logic di model — saat transaksi tipe 'beli' → stok bertambah; tipe 'jual' → stok berkurang (validasi stok cukup sebelum simpan transaksi jual).
- **No nota otomatis**: generate di model, format `TRX-YYYYMMDD-XXXX`, cek nomor urut harian.
- **Histori harga** (opsional tapi disarankan): tabel tambahan `histori_harga` kalau ke depan mau lihat tren harga per barang dari waktu ke waktu.
