<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * PRD bagian 3: role 'admin' lama dimigrasikan menjadi 'tim_jf'.
 */
class RoleMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_11_24_000001_migrate_admin_role_to_tim_jf.php');
    }

    private function insertUser(string $nip, string $role): void
    {
        DB::table('users')->insert([
            'nip' => $nip,
            'name' => 'User ' . $nip,
            'email' => $nip . '@bps.go.id',
            'password' => 'x',
            'role' => $role,
        ]);
    }

    public function test_up_converts_admin_to_tim_jf_and_keeps_other_roles(): void
    {
        $this->insertUser('100', 'admin');
        $this->insertUser('200', 'pegawai');

        $this->migration()->up();

        $this->assertSame('tim_jf', DB::table('users')->where('nip', '100')->value('role'));
        $this->assertSame('pegawai', DB::table('users')->where('nip', '200')->value('role'));
        $this->assertSame(0, DB::table('users')->where('role', 'admin')->count());
    }

    public function test_down_restores_admin_role(): void
    {
        $this->insertUser('100', 'tim_jf');

        $this->migration()->down();

        $this->assertSame('admin', DB::table('users')->where('nip', '100')->value('role'));
    }
}
