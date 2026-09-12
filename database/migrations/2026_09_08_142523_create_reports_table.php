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
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('kategori_laporan', ['Kerusakan', 'Kebersihan', 'Fasilitas', 'Lainnya']);
            $table->string('deskripsi', 150);
            $table->string('foto')->nullable();
            $table->enum('status_laporan', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('catatan_resolusi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};