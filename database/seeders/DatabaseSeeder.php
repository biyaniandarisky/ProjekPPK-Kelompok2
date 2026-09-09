<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1. DATA USERS
        // ============================================
        
        // Admin
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Kampus',
            'email' => 'admin@kampus.ac.id',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status_verifikasi' => 'verified',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Petugas
        $petugasId = DB::table('users')->insertGetId([
            'name' => 'Petugas Fasilitas',
            'email' => 'petugas@kampus.ac.id',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'status_verifikasi' => 'verified',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Pengguna (Mahasiswa)
        $pengguna1Id = DB::table('users')->insertGetId([
            'name' => 'Mahasiswa Satu',
            'email' => 'mahasiswa1@kampus.ac.id',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'verified',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $pengguna2Id = DB::table('users')->insertGetId([
            'name' => 'Mahasiswa Dua',
            'email' => 'mahasiswa2@kampus.ac.id',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'verified',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $pengguna3Id = DB::table('users')->insertGetId([
            'name' => 'Mahasiswa Tiga',
            'email' => 'mahasiswa3@kampus.ac.id',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'pengguna',
            'status_verifikasi' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // ============================================
        // 2. DATA FASILITAS KAMPUS
        // ============================================
        
        $facility1Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Ruang Kelas A101',
            'tipe' => 'Ruangan',
            'lokasi' => 'Gedung A Lt. 1',
            'kapasitas' => 40,
            'deskripsi' => 'Ruang kelas dengan AC dan proyektor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility2Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Ruang Kelas A102',
            'tipe' => 'Ruangan',
            'lokasi' => 'Gedung A Lt. 1',
            'kapasitas' => 40,
            'deskripsi' => 'Ruang kelas dengan AC dan whiteboard',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility3Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Ruang Meeting B201',
            'tipe' => 'Ruangan',
            'lokasi' => 'Gedung B Lt. 2',
            'kapasitas' => 15,
            'deskripsi' => 'Ruang meeting dengan AC dan meja rapat',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility4Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Laboratorium Komputer C301',
            'tipe' => 'Laboratorium',
            'lokasi' => 'Gedung C Lt. 3',
            'kapasitas' => 30,
            'deskripsi' => 'Lab komputer dengan 30 PC dan internet',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility5Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Auditorium',
            'tipe' => 'Ruangan',
            'lokasi' => 'Gedung Serbaguna',
            'kapasitas' => 200,
            'deskripsi' => 'Auditorium dengan sound system dan layar LED',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility6Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Lapangan Basket',
            'tipe' => 'Olahraga',
            'lokasi' => 'Area Outdoor',
            'kapasitas' => 20,
            'deskripsi' => 'Lapangan basket outdoor standar',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility7Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Lapangan Futsal',
            'tipe' => 'Olahraga',
            'lokasi' => 'Area Outdoor',
            'kapasitas' => 14,
            'deskripsi' => 'Lapangan futsal dengan rumput sintetis',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $facility8Id = DB::table('facilities')->insertGetId([
            'nama_fasilitas' => 'Perpustakaan Ruang Baca',
            'tipe' => 'Fasilitas Umum',
            'lokasi' => 'Gedung Perpustakaan Lt. 2',
            'kapasitas' => 50,
            'deskripsi' => 'Ruang baca dengan AC dan WiFi',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // ============================================
        // 3. DATA RESERVASI
        // ============================================
        
        // Reservasi aktif (confirmed) - hari ini dan besok
        DB::table('reservations')->insert([
            'user_id' => $pengguna1Id,
            'facility_id' => $facility1Id,
            'petugas_id' => $petugasId,
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'tujuan' => 'Kuliah Pengantar Pemrograman',
            'status' => 'confirmed',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('reservations')->insert([
            'user_id' => $pengguna2Id,
            'facility_id' => $facility4Id,
            'petugas_id' => $petugasId,
            'tanggal' => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'tujuan' => 'Praktikum Basis Data',
            'status' => 'confirmed',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Reservasi pending (menunggu konfirmasi)
        DB::table('reservations')->insert([
            'user_id' => $pengguna1Id,
            'facility_id' => $facility3Id,
            'petugas_id' => null,
            'tanggal' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'tujuan' => 'Rapat Organisasi Mahasiswa',
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('reservations')->insert([
            'user_id' => $pengguna2Id,
            'facility_id' => $facility6Id,
            'petugas_id' => null,
            'tanggal' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'tujuan' => 'Latihan Basket UKM',
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Reservasi batal/ditolak
        DB::table('reservations')->insert([
            'user_id' => $pengguna3Id,
            'facility_id' => $facility5Id,
            'petugas_id' => $petugasId,
            'tanggal' => Carbon::now()->subDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'tujuan' => 'Seminar Proposal',
            'status' => 'cancelled',
            'alasan_batal' => 'Dibatalkan oleh petugas karena bentrok jadwal',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // ============================================
        // 4. DATA LAPORAN
        // ============================================
        
        // Laporan baru
        DB::table('reports')->insert([
            'user_id' => $pengguna1Id,
            'facility_id' => $facility1Id,
            'petugas_id' => null,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi' => 'AC di ruang kelas A101 tidak dingin',
            'foto' => null,
            'status_laporan' => 'baru',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Laporan diproses
        DB::table('reports')->insert([
            'user_id' => $pengguna2Id,
            'facility_id' => $facility4Id,
            'petugas_id' => $petugasId,
            'kategori_laporan' => 'Kerusakan',
            'deskripsi' => '3 unit PC di laboratorium C301 tidak bisa menyala',
            'foto' => null,
            'status_laporan' => 'diproses',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Laporan selesai
        DB::table('reports')->insert([
            'user_id' => $pengguna1Id,
            'facility_id' => $facility6Id,
            'petugas_id' => $petugasId,
            'kategori_laporan' => 'Fasilitas',
            'deskripsi' => 'Ring basket lapangan outdoor longgar',
            'foto' => null,
            'status_laporan' => 'selesai',
            'catatan_resolusi' => 'Ring basket sudah diperbaiki',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Laporan ditolak
        DB::table('reports')->insert([
            'user_id' => $pengguna3Id,
            'facility_id' => $facility7Id,
            'petugas_id' => $petugasId,
            'kategori_laporan' => 'Kebersihan',
            'deskripsi' => 'Rumput lapangan futsal tidak terawat',
            'foto' => null,
            'status_laporan' => 'ditolak',
            'catatan_resolusi' => 'Laporan tidak valid, rumput dalam kondisi baik',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // ============================================
        // 5. TAMPILKAN INFORMASI
        // ============================================
        $this->command->info('Database seeding berhasil!');
        $this->command->info('');
        $this->command->info('Data yang dimasukkan:');
        $this->command->info('   Users: 6 (1 admin, 1 petugas, 4 pengguna)');
        $this->command->info('   Facilities: 8 fasilitas kampus');
        $this->command->info('   Reservations: 5 reservasi');
        $this->command->info('   Reports: 4 laporan');
        $this->command->info('');
        $this->command->info('Akun Login:');
        $this->command->info('   Admin    : admin@kampus.ac.id / password');
        $this->command->info('   Petugas  : petugas@kampus.ac.id / password');
        $this->command->info('   Pengguna : mahasiswa1@kampus.ac.id / password');
        $this->command->info('   Pengguna : mahasiswa2@kampus.ac.id / password');
        $this->command->info('   Pengguna : mahasiswa3@kampus.ac.id / password');
        $this->command->info('');
        $this->command->info('Catatan:');
        $this->command->info('   - Mahasiswa3 status verifikasi masih pending');
        $this->command->info('   - Gunakan tanggal hari ini untuk reservasi aktif');
    }
}