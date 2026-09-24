# Sistem Reservasi & Pelaporan Fasilitas Kampus Terpadu (Laravel 11 + MySQL + Tailwind CSS)

Aplikasi web lengkap untuk mengelola peminjaman dan pelaporan fasilitas kampus (ruang kelas, laboratorium, sarana olahraga, dan aula serbaguna) dengan arsitektur **MVC**, **Clean Architecture (Service Layer)**, dan proteksi **Middleware Role**.

---

## Aktor & Hak Akses
1. **Pengunjung**: Jelajah katalog bergaya Traveloka, cek ketersediaan slot 30 menit (07.00–20.00 WIB) bebas bentrok jadwal.
2. **Pengguna (Mahasiswa / Dosen / Staf)**: Registrasi mandiri (*pending*), login satu pintu, formulir reservasi bebas bentrok, riwayat peminjaman & pembatalan, formulir laporan kerusakan sarana dengan foto.
3. **Petugas**: Antrian persetujuan/penolakan reservasi, pembatalan darurat (*emergency cancel*) dengan alasan resmi, penanganan laporan (*baru* -> *diproses* -> *selesai / ditolak*), dan pengubahan status operasional fasilitas (*aktif* <-> *dalam perbaikan*).
4. **Admin**: Verifikasi/penolakan registrasi mandiri, pendaftaran petugas langsung, pendaftaran pengguna langsung terverifikasi, manajemen master data fasilitas, dan rekap analitik okupansi & kerusakan dengan ekspor CSV.

---

## Panduan Instalasi & Menjalankan Aplikasi

### Ekstrak File & Masuk Direktori
```bash
cd sistem-fasilitas-kampus
```

### Pasang Dependensi Composer
```bash
composer install
```

### Konfigurasi Database MySQL (.env)
Salin berkas konfigurasi lingkungan:
```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan sesuaikan kredensial MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kampus_fasilitas
DB_USERNAME=root
DB_PASSWORD=
```

Database `kampus_fasilitas` telah dibuat di MySQL / phpMyAdmin:
```sql
CREATE DATABASE kampus_fasilitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Jalankan Migrasi & Seeder Database
```bash
php artisan migrate:fresh --seed
```

Data akun uji coba bawaan seeder:
- **Admin**: `admin@kampus.ac.id` | Password: `password`
- **Petugas**: `petugas@kampus.ac.id` | Password: `password`
- **Pengguna (Verified)**: `mahasiswa1@kampus.ac.id` | Password: `password`
- **Pengguna (Pending Test)**: `mahasiswa4@kampus.ac.id` | Password: `password`

### Pasang & Kompilasi Aset Frontend (Tailwind CSS)
```bash
npm install
npm run build
```
*(Atau gunakan `npm run dev` saat pengembangan aktif)*

### Jalankan Server Web Lokal
```bash
php artisan serve
```
Buka peramban di: **http://localhost:8000**

---

## Menjalankan Automated Feature Tests
```bash
php artisan test
```
Semua pengujian pencegahan bentrok slot (*ReservationConflictTest*) dan verifikasi akun akan tereksekusi dengan sukses.
