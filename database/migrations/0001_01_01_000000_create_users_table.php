<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();

            // Kolom tambahan (URUTAN mengikuti deklarasi, tanpa ->after())
            $table->string('nim_nip', 30)->nullable()->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('ktm_path')->nullable();
            $table->string('unit', 100)->nullable();
            $table->string('tipe_pengguna', 20)->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'petugas', 'pengguna'])->default('pengguna');
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected', 'suspended'])
                  ->default('pending');
            $table->rememberToken();
            $table->timestamps();

            // Index
            $table->index('role');
            $table->index('status_verifikasi');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};