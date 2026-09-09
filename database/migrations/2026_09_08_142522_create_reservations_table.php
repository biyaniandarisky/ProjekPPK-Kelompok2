<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users & facilities
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Pemesan
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade'); // Fasilitas yang dipesan
            $table->foreignId('petugas_id')->nullable()->constrained('users')->onDelete('set null'); // Petugas yang ngurus
            
            // Waktu penggunaan
            $table->date('tanggal');
            $table->time('start_time');
            $table->time('end_time');
            
            // Tujuan penggunaan (wajib diisi oleh pengguna)
            $table->string('tujuan', 200);
            
            // Status reservasi: pending, confirmed, cancelled, rejected
            $table->string('status', 20)->default('pending');
            
            // Alasan pembatalan/penolakan (diisi oleh petugas/admin)
            $table->string('alasan_batal', 200)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};