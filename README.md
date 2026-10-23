# SIAP — Sistem Informasi Administrasi Pelatihan

Starter application PHP native + MySQL/MariaDB untuk administrasi pelatihan.

## Modul yang tersedia
- Login dan peran pengguna (Admin/Staf)
- Dashboard ringkasan
- Data pelatihan: tambah, lihat, edit, hapus, pencarian
- Data peserta: tambah, lihat, edit, hapus, pencarian
- Pendaftaran peserta ke kegiatan
- Presensi harian (Hadir, Izin, Sakit, Alpa)
- Register nomor sertifikat
- Manajemen akun pengguna oleh Admin

## Teknologi
- PHP 8.1+ (PDO)
- MySQL/MariaDB
- HTML/CSS responsif
- Cocok untuk pengembangan lokal di XAMPP dan editor seperti VS Code/Notepad++.

## Instalasi di Windows + XAMPP
1. Ekstrak folder `SIAP` ke `C:\\xampp\\htdocs\\siap`.
2. Buka XAMPP Control Panel, jalankan Apache dan MySQL.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih **Import**, pilih `database/siap_db.sql`, lalu jalankan import.
5. Periksa `config/database.php`: database `siap_db`, user `root`, password kosong (default XAMPP). Sesuaikan jika konfigurasi MySQL Anda berbeda.
6. Buka `http://localhost/siap/login.php`.
7. Login awal: `admin@siap.local` / `SIAPadmin2026!`.
8. Setelah login, segera buat akun admin baru dan ubah/hapus akun awal. Untuk produksi, jangan gunakan kredensial contoh.

## Catatan penting
- Ini adalah MVP awal yang dapat dikembangkan, bukan sistem produksi yang sudah diaudit.
- Sertifikat saat ini berupa pencatatan nomor dan register, belum menghasilkan PDF otomatis dengan tanda tangan/QR.
- Belum termasuk upload dokumen KTP/ijazah/foto/CV, pembayaran, email/WhatsApp gateway, ekspor Excel, atau audit log.
- Sebelum dihosting: aktifkan HTTPS, gunakan password database yang kuat, batasi akses phpMyAdmin, nonaktifkan error detail, siapkan backup terjadwal, uji otorisasi, dan ganti kredensial awal.
- Gunakan data dummy saat pengujian. Data peserta seperti nomor identitas adalah data pribadi dan harus dilindungi.

## Pengembangan berikutnya yang disarankan
1. Upload dokumen persyaratan peserta dengan validasi MIME/ukuran dan penyimpanan aman.
2. Template sertifikat PDF dengan nomor unik dan QR verifikasi.
3. Ekspor laporan ke Excel/PDF.
4. Rekap kehadiran dan kelulusan.
5. Pembayaran, kuitansi, dan laporan keuangan kegiatan.
6. Hak akses rinci, audit log, dan pencadangan otomatis.


## Pengembangan divisi (versi SIAP Divisi)
Versi ini menambahkan dashboard berdasarkan role dan modul Prospek Peserta, RAB, Inventaris, Data Personel HRD, serta Dokumen HRD.

Role yang tersedia:
- `admin`: Super Admin, dapat mengakses semua divisi dan mengelola pengguna.
- `admin_humas`: Dashboard Humas, data peserta, prospek, dan kegiatan.
- `admin_keuangan`: Dashboard Keuangan dan input RAB.
- `admin_umum`: Dashboard Umum dan stok barang.
- `admin_hrd`: Dashboard HRD, data personel, dan dokumen pendukung.
- `staff`: akses operasional pelatihan yang sudah tersedia.

### Jika database SIAP lama sudah dipakai
1. Backup database `siap_db` terlebih dahulu melalui phpMyAdmin (Export).
2. Buka phpMyAdmin, pilih database `siap_db`, lalu tab Import.
3. Import file `database/migration_divisi.sql` dari paket ini. File ini menambah tabel divisi dan mengubah kolom role menjadi VARCHAR.
4. Login dengan akun Super Admin lama, buka menu Pengguna, lalu buat akun untuk tiap divisi. Pilih role sesuai divisi dan tetapkan kata sandi kuat yang berbeda untuk tiap akun.
5. Ganti file aplikasi dengan versi baru setelah membuat salinan/backup folder aplikasi lama.

### Jika instalasi baru
Import `database/siap_db.sql`. Akun awal yang dijelaskan di README lama tetap menjadi Super Admin; segera ganti kredensial awal setelah login.

Dokumen HRD yang diunggah hanya diizinkan PDF/JPG/PNG/DOC/DOCX maksimal 10 MB. File disimpan di `storage/hrd_documents` dan dilindungi dari akses langsung; unduh melalui halaman yang memerlukan login HRD/Super Admin.
