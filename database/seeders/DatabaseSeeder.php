<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* =========================================================
         |  1. AKUN DEFAULT
         ========================================================= */

        // Admin
        $admin = User::create([
            'name'              => 'Administrator Kampus',
            'email'             => 'admin@kampus.ac.id',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'status_verifikasi' => 'verified',
        ]);

        // Petugas
        $petugas = User::create([
            'name'              => 'Budi Santoso (Petugas Sarpras)',
            'email'             => 'petugas@kampus.ac.id',
            'nim_nip'           => '198501012010011001',
            'no_hp'             => '081234567890',
            'unit'              => 'Sarpras',
            'tipe_pengguna'     => 'petugas',
            'password'          => Hash::make('password'),
            'role'              => 'petugas',
            'status_verifikasi' => 'verified',
        ]);

        // Pengguna verified 1
        $mhs1 = User::create([
            'name'              => 'Ahmad Fauzi (Mahasiswa TI)',
            'email'             => 'mahasiswa1@kampus.ac.id',
            'nim_nip'           => '2021110001',
            'no_hp'             => '081234567891',
            'tipe_pengguna'     => 'mahasiswa',
            'password'          => Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        // Pengguna verified 2
        $mhs2 = User::create([
            'name'              => 'Rina Sasmita (Mahasiswi SI)',
            'email'             => 'mahasiswa2@kampus.ac.id',
            'nim_nip'           => '2021110002',
            'no_hp'             => '081234567892',
            'tipe_pengguna'     => 'mahasiswa',
            'password'          => Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        // Dosen
        $dosen = User::create([
            'name'              => 'Dr. Siti Aminah (Dosen TI)',
            'email'             => 'dosen1@kampus.ac.id',
            'nim_nip'           => '198001012005012001',
            'no_hp'             => '081234567893',
            'tipe_pengguna'     => 'dosen',
            'password'          => Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        // Pengguna pending (untuk test verifikasi)
        User::create([
            'name'              => 'Bambang Sudarsono (Mahasiswa Baru)',
            'email'             => 'mahasiswa4@kampus.ac.id',
            'nim_nip'           => '2024110004',
            'no_hp'             => '081234567894',
            'tipe_pengguna'     => 'mahasiswa',
            'password'          => Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'pending',
        ]);

        // Pengguna rejected (untuk test tolak)
        User::create([
            'name'              => 'User Ditolak (Test)',
            'email'             => 'rejected@kampus.ac.id',
            'nim_nip'           => '2024110005',
            'password'          => Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'rejected',
        ]);

        /* =========================================================
         |  2. FASILITAS
         ========================================================= */

        $f1 = Facility::create([
            'nama_fasilitas' => 'Ruang Kuliah Teori B101',
            'tipe'           => 'Ruangan',
            'lokasi'         => 'Gedung Rektorat Lantai 1',
            'kapasitas'      => 50,
            'deskripsi'      => 'Dilengkapi proyektor laser, sistem audio nirkabel, AC inverter, dan papan tulis ganda.',
            'foto'           => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
            'status'         => 'aktif',
        ]);

        $f2 = Facility::create([
            'nama_fasilitas' => 'Laboratorium Rekayasa Perangkat Lunak',
            'tipe'           => 'Laboratorium',
            'lokasi'         => 'Gedung Sains & Teknologi Lantai 3',
            'kapasitas'      => 35,
            'deskripsi'      => '35 PC Core i7, koneksi LAN Gigabit, pendingin udara ganda, dan smart TV presentasi.',
            'foto'           => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80',
            'status'         => 'aktif',
        ]);

        $f3 = Facility::create([
            'nama_fasilitas' => 'Gelanggang Olahraga & Lapangan Basket Indoor',
            'tipe'           => 'Olahraga',
            'lokasi'         => 'Kompleks Olahraga Barat Kampus',
            'kapasitas'      => 200,
            'deskripsi'      => 'Lantai vinyl standar FIBA, tribun penonton, ruang ganti bersih, dan pencahayaan LED.',
            'foto'           => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80',
            'status'         => 'aktif',
        ]);

        $f4 = Facility::create([
            'nama_fasilitas' => 'Auditorium Utama Graha Cendekia',
            'tipe'           => 'Fasilitas Umum',
            'lokasi'         => 'Pusat Kegiatan Mahasiswa Kampus',
            'kapasitas'      => 500,
            'deskripsi'      => 'Panggung akustik megah, lighting teater, proyektor raksasa, cocok untuk wisuda dan seminar.',
            'foto'           => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80',
            'status'         => 'aktif',
        ]);

        $f5 = Facility::create([
            'nama_fasilitas' => 'Laboratorium Jaringan & Keamanan Siber',
            'tipe'           => 'Laboratorium',
            'lokasi'         => 'Gedung Sains & Teknologi Lantai 2',
            'kapasitas'      => 30,
            'deskripsi'      => 'Rak server rackmount, switch manageable Cisco, dan kabel patch fiber optic.',
            'foto'           => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
            'status'         => 'dalam_perbaikan',
        ]);

        /* =========================================================
         |  3. RESERVASI
         ========================================================= */

        $besok = now()->addDay()->toDateString();
        $lusa  = now()->addDays(2)->toDateString();

        // Approved
        $r1 = Reservation::create([
            'facility_id' => $f1->id,
            'user_id'     => $mhs1->id,
            'petugas_id'  => $petugas->id,
            'tanggal'     => $besok,
            'start_time'  => '08:00:00',
            'end_time'    => '10:00:00',
            'tujuan'      => 'Kuliah Praktikum Pemrograman Web Lanjutan Semester 4',
            'status'      => 'approved',
        ]);

        // Pending
        $r2 = Reservation::create([
            'facility_id' => $f2->id,
            'user_id'     => $mhs2->id,
            'petugas_id'  => null,
            'tanggal'     => $besok,
            'start_time'  => '13:00:00',
            'end_time'    => '15:00:00',
            'tujuan'      => 'Rapat Kerja Himpunan Mahasiswa Informatika',
            'status'      => 'pending',
        ]);

        // Rejected
        Reservation::create([
            'facility_id' => $f3->id,
            'user_id'     => $dosen->id,
            'petugas_id'  => $petugas->id,
            'tanggal'     => $lusa,
            'start_time'  => '16:00:00',
            'end_time'    => '18:00:00',
            'tujuan'      => 'Latihan Futsal Dosen',
            'status'      => 'rejected',
            'alasan_tolak' => 'Fasilitas sedang digunakan untuk kegiatan kampus.',
        ]);

        // Cancelled
        Reservation::create([
            'facility_id' => $f1->id,
            'user_id'     => $mhs2->id,
            'petugas_id'  => null,
            'tanggal'     => $lusa,
            'start_time'  => '10:00:00',
            'end_time'    => '12:00:00',
            'tujuan'      => 'Belajar Kelompok',
            'status'      => 'cancelled',
            'alasan_batal' => 'Dibatalkan oleh pengguna.',
        ]);

        /* =========================================================
         |  4. LAPORAN
         ========================================================= */

        $l1 = Report::create([
            'facility_id'      => $f1->id,
            'user_id'          => $mhs1->id,
            'petugas_id'       => $petugas->id,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi'        => 'Proyektor ruang berkedip dan warna tampilan cenderung menguning saat digunakan.',
            'status_laporan'   => 'selesai',
            'catatan_resolusi' => 'Kabel HDMI dan bohlam lampu proyektor telah diganti oleh teknisi.',
        ]);

        $l2 = Report::create([
            'facility_id'      => $f5->id,
            'user_id'          => $mhs2->id,
            'petugas_id'       => $petugas->id,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi'        => 'Pendingin ruangan AC mati total sehingga lab panas dan tidak nyaman untuk praktikum.',
            'status_laporan'   => 'diproses',
            'catatan_resolusi' => 'Menunggu penggantian kompresor AC oleh pihak rekanan teknis.',
        ]);

        $l3 = Report::create([
            'facility_id'      => $f2->id,
            'user_id'          => $dosen->id,
            'petugas_id'       => null,
            'kategori_laporan' => 'Kebersihan',
            'deskripsi'        => 'Meja dan kursi di lab banyak debu, perlu dibersihkan sebelum praktikum.',
            'status_laporan'   => 'baru',
        ]);

        /* =========================================================
         |  5. NOTIFIKASI CONTOH
         ========================================================= */

        Notification::create([
            'user_id'        => $mhs1->id,
            'reservation_id' => $r1->id,
            'judul'          => 'Reservasi Disetujui',
            'pesan'          => 'Reservasi Anda di Ruang Kuliah Teori B101 telah disetujui.',
            'tipe'           => 'success',
            'link'           => '/pengguna/reservasi',
            'is_read'        => false,
        ]);

        Notification::create([
            'user_id'        => $petugas->id,
            'reservation_id' => $r2->id,
            'judul'          => 'Reservasi Baru',
            'pesan'          => 'Ada reservasi baru dari Rina Sasmita di Lab RPL.',
            'tipe'           => 'info',
            'link'           => '/petugas/reservasi',
            'is_read'        => false,
        ]);

        Notification::create([
            'user_id' => $admin->id,
            'judul'   => 'User Menunggu Verifikasi',
            'pesan'   => 'Ada user baru yang menunggu verifikasi: Bambang Sudarsono.',
            'tipe'    => 'warning',
            'link'    => '/admin/dashboard',
            'is_read' => false,
        ]);

        /* =========================================================
         |  6. OUTPUT INFORMASI
         ========================================================= */

        $this->command->info('Seeder berhasil!');
        $this->command->info('');
        $this->command->info('Akun Uji Coba:');
        $this->command->info('   Admin    : admin@kampus.ac.id / password');
        $this->command->info('   Petugas  : petugas@kampus.ac.id / password');
        $this->command->info('   Mahasiswa: mahasiswa1@kampus.ac.id / password');
        $this->command->info('   Dosen    : dosen1@kampus.ac.id / password');
        $this->command->info('   Pending  : mahasiswa4@kampus.ac.id / password');
        $this->command->info('');
    }
}