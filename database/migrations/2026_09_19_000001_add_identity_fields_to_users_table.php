<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim_nip', 30)->nullable()->unique()->after('email');
            $table->string('no_hp', 20)->nullable()->after('nim_nip');
            $table->string('ktm_path')->nullable()->after('no_hp'); // berkas KTM/KTP (disk private)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim_nip']);
            $table->dropColumn(['nim_nip', 'no_hp', 'ktm_path']);
        });
    }
};
