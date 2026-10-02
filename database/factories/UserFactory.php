<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Default state.
     */
    public function definition(): array
    {
        return [
            'name'              => fake('id_ID')->name(),
            'email'             => fake()->unique()->safeEmail(),
            'nim_nip'           => fake()->unique()->numerify('##########'),
            'no_hp'             => fake('id_ID')->phoneNumber(),
            'ktm_path'          => null,
            'unit'              => null,
            'tipe_pengguna'     => null,
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
            'remember_token'    => Str::random(10),
        ];
    }

    /* =========================================================
     |  STATE: ROLE
     ========================================================= */

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'              => 'admin',
            'status_verifikasi' => 'verified',
            'tipe_pengguna'     => null,
        ]);
    }

    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'              => 'petugas',
            'status_verifikasi' => 'verified',
            'tipe_pengguna'     => 'petugas',
            'unit'              => fake()->randomElement(['Sarpras', 'IT', 'Umum']),
        ]);
    }

    public function pengguna(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
            'tipe_pengguna'     => fake()->randomElement(['mahasiswa', 'dosen', 'staf']),
        ]);
    }

    /* =========================================================
     |  STATE: STATUS VERIFIKASI
     ========================================================= */

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_verifikasi' => 'pending',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_verifikasi' => 'rejected',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /* =========================================================
     |  STATE: KTM
     ========================================================= */

    public function withKtm(): static
    {
        return $this->state(fn (array $attributes) => [
            'ktm_path' => 'ktm/dummy-ktm.pdf',
        ]);
    }
}