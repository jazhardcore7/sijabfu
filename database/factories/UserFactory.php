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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nip' => fake()->unique()->numerify('##################'),
            'role' => User::ROLE_PEGAWAI,
            'is_active' => true,
            // Default Pusat agar perilaku pengujian lama tetap sama (FR-SATKER-7)
            'satker_tipe' => User::SATKER_PUSAT,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function pegawai(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_PEGAWAI]);
    }

    public function timJf(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_TIM_JF]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function pusat(): static
    {
        return $this->state(fn (array $attributes) => ['satker_tipe' => User::SATKER_PUSAT, 'satker_kode' => '0000', 'provinsi_kode' => null]);
    }

    public function provinsi(string $provinsiKode = '32'): static
    {
        return $this->state(fn (array $attributes) => ['satker_tipe' => User::SATKER_PROVINSI, 'satker_kode' => $provinsiKode.'00', 'provinsi_kode' => $provinsiKode]);
    }

    public function kabkota(string $provinsiKode = '32', string $kabkota = '73'): static
    {
        return $this->state(fn (array $attributes) => ['satker_tipe' => User::SATKER_KABKOTA, 'satker_kode' => $provinsiKode.$kabkota, 'provinsi_kode' => $provinsiKode]);
    }

    public function tanpaSatker(): static
    {
        return $this->state(fn (array $attributes) => ['satker_tipe' => null, 'satker_kode' => null, 'provinsi_kode' => null]);
    }

    public function adminSatker(string $provinsiKode = '32'): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN_SATKER,
            'satker_tipe' => User::SATKER_PROVINSI,
            'satker_kode' => $provinsiKode.'00',
            'provinsi_kode' => $provinsiKode,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
