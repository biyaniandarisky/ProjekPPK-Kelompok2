<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah status "selesai" (perbaikan sudah selesai, tapi fasilitas belum diaktifkan).
        Schema::table('facilities', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'dalam_perbaikan', 'selesai', 'nonaktif'])
                ->default('aktif')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('facilities')->where('status', 'selesai')->update(['status' => 'dalam_perbaikan']);

        Schema::table('facilities', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'dalam_perbaikan', 'nonaktif'])
                ->default('aktif')
                ->change();
        });
    }
};
