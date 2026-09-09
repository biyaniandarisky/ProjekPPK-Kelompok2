<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users & facilities
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Detail laporan
            $table->string('kategori_laporan', 50);
            $table->string('deskripsi', 150);
            $table->string('foto', 255)->nullable();
            
            // Status laporan: baru, diproses, selesai, ditolak
            $table->string('status_laporan', 50)->default('baru');
            
            // Catatan resolusi - diisi petugas saat menutup laporan
            $table->text('catatan_resolusi')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};