<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('nama_fasilitas', 150);
            $table->enum('tipe', ['Ruangan', 'Laboratorium', 'Olahraga', 'Fasilitas Umum']);
            $table->string('lokasi', 100);
            $table->integer('kapasitas')->unsigned();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'dalam_perbaikan', 'nonaktif'])->default('aktif');
            $table->timestamps();

            // Index untuk performa filter
            $table->index('tipe');
            $table->index('lokasi');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};