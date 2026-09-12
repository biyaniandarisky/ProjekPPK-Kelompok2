<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::create([
            'name' => 'Administrator Kampus Utama',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status_verifikasi' => 'verified',
        ]);

        // 2. Akun Petugas Sarana
        $petugas = User::create([
            'name' => 'Budi Santoso (Petugas Sarpras)',
            'email' => 'petugas@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'status_verifikasi' => 'verified',
        ]);

        // 3. Akun Pengguna Terverifikasi
        $mhs1 = User::create([
            'name' => 'Ahmad Fauzi (Mahasiswa TI)',
            'email' => 'mahasiswa1@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        $mhs2 = User::create([
            'name' => 'Rina Sasmita (Mahasiswi SI)',
            'email' => 'mahasiswa2@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        // 4. Akun Pengguna Pending (Uji tolak verifikasi)
        User::create([
            'name' => 'Bambang Sudarsono (Mahasiswa Baru)',
            'email' => 'mahasiswa4@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'pending',
        ]);

        // 5. Data Fasilitas Master
        $f1 = Facility::create([
            'nama_fasilitas' => 'Ruang Kuliah Teori B101',
            'tipe' => 'Ruangan',
            'lokasi' => 'Gedung Rektorat Lantai 1',
            'kapasitas' => 50,
            'deskripsi' => 'Dilengkapi proyektor laser, sistem audio nirkabel, AC inverter, dan papan tulis ganda.',
            'foto' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
            'status' => 'aktif',
        ]);

        $f2 = Facility::create([
            'nama_fasilitas' => 'Laboratorium Rekayasa Perangkat Lunak',
            'tipe' => 'Laboratorium',
            'lokasi' => 'Gedung Sains & Teknologi Lantai 3',
            'kapasitas' => 35,
            'deskripsi' => '35 PC Core i7, koneksi LAN Gigabit, pendingin udara ganda, dan smart TV presentasi.',
            'foto' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80',
            'status' => 'aktif',
        ]);

        $f3 = Facility::create([
            'nama_fasilitas' => 'Gelanggang Olahraga & Lapangan Basket Indoor',
            'tipe' => 'Olahraga',
            'lokasi' => 'Kompleks Olahraga Barat Kampus',
            'kapasitas' => 200,
            'deskripsi' => 'Lantai vinyl standar FIBA, tribun penonton, ruang ganti bersih, dan pencahayaan LED.',
            'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80',
            'status' => 'aktif',
        ]);

        $f4 = Facility::create([
            'nama_fasilitas' => 'Auditorium Utama Graha Cendekia',
            'tipe' => 'Fasilitas Umum',
            'lokasi' => 'Pusat Kegiatan Mahasiswa Kampus',
            'kapasitas' => 500,
            'deskripsi' => 'Panggung akustik megah, lighting teater, proyektor raksasa, cocok untuk wisuda dan seminar.',
            'foto' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80',
            'status' => 'aktif',
        ]);

        $f5 = Facility::create([
            'nama_fasilitas' => 'Laboratorium Jaringan & Keamanan Siber',
            'tipe' => 'Laboratorium',
            'lokasi' => 'Gedung Sains & Teknologi Lantai 2',
            'kapasitas' => 30,
            'deskripsi' => 'Rak server rackmount, switch manageable Cisco, dan kabel patch fiber optic.',
            'foto' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
            'status' => 'dalam_perbaikan',
        ]);

        // 6. Data Reservasi Awal
        $besok = now()->addDay()->toDateString();
        Reservation::create([
            'facility_id' => $f1->id,
            'user_id' => $mhs1->id,
            'petugas_id' => $petugas->id,
            'tanggal' => $besok,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'tujuan' => 'Kuliah Praktikum Pemrograman Web Lanjutan Semester 4',
            'status' => 'approved',
        ]);

        Reservation::create([
            'facility_id' => $f2->id,
            'user_id' => $mhs2->id,
            'petugas_id' => null,
            'tanggal' => $besok,
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'tujuan' => 'Rapat Kerja Himpunan Mahasiswa Informatika',
            'status' => 'pending',
        ]);

        // 7. Data Laporan Awal
        Report::create([
            'facility_id' => $f1->id,
            'user_id' => $mhs1->id,
            'petugas_id' => $petugas->id,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi' => 'Proyektor ruang berkedip dan warna tampilan cenderung menguning.',
            'status_laporan' => 'selesai',
            'catatan_resolusi' => 'Kabel HDMI dan bohlam lampu proyektor telah diganti oleh teknisi.',
        ]);

        Report::create([
            'facility_id' => $f5->id,
            'user_id' => $mhs2->id,
            'petugas_id' => $petugas->id,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi' => 'Pendingin ruangan AC mati total sehingga lab panas.',
            'status_laporan' => 'diproses',
            'catatan_resolusi' => 'Menunggu penggantian kompresor AC oleh pihak rekanan teknis.',
        ]);
    }
}