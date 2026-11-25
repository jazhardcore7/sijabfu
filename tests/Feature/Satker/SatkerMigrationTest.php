<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-2 dan FR-SATKER-20: kolom satker dan submitted_by, backfill, data lama tetap ada.
 */
class SatkerMigrationTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private function runMigration(string $file): void
    {
        $migration = require database_path('migrations/'.$file);
        $migration->up();
    }

    public function test_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['satker_tipe', 'satker_kode', 'provinsi_kode']));
        $this->assertTrue(Schema::hasColumn('form_usulan', 'submitted_by'));
        $this->assertTrue(Schema::hasColumn('sso_sync_logs', 'satker_missing_count'));
    }

    public function test_backfill_sets_existing_users_to_pusat_and_keeps_roles(): void
    {
        foreach (['pegawai', 'tim_jf', 'super_admin'] as $role) {
            User::factory()->create(['role' => $role, 'satker_tipe' => null]);
        }
        $roles = User::orderBy('id')->pluck('role')->all();

        $this->runMigration('2026_12_01_000002_backfill_users_satker_tipe.php');

        $this->assertSame(0, User::whereNull('satker_tipe')->count());
        $this->assertSame(3, User::where('satker_tipe', 'pusat')->count());
        $this->assertSame($roles, User::orderBy('id')->pluck('role')->all());
    }

    public function test_user_created_after_migration_has_no_satker_by_default(): void
    {
        DB::table('users')->insert(['name' => 'Baru', 'email' => 'baru@example.test', 'password' => 'x', 'nip' => '123']);

        $this->assertNull(User::where('nip', '123')->value('satker_tipe'));
    }

    public function test_backfill_submitted_by_from_user_id_keeps_status(): void
    {
        Storage::fake('local');
        $a = User::factory()->pegawai()->create();
        $b = User::factory()->pegawai()->create();
        $u1 = $this->usulanAt($a, FormUsulan::STATUS_VERIFIKASI);
        $u2 = $this->usulanAt($b, FormUsulan::STATUS_SELESAI);
        FormUsulan::query()->update(['submitted_by' => null]);

        $this->runMigration('2026_12_01_000004_backfill_form_usulan_submitted_by.php');

        $this->assertSame($a->id, (int) $u1->fresh()->submitted_by);
        $this->assertSame($b->id, (int) $u2->fresh()->submitted_by);
        $this->assertSame(['verifikasi', 'selesai'], FormUsulan::orderBy('id')->pluck('status_key')->all());
    }

    public function test_new_pegawai_usulan_records_submitted_by(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->create();

        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan())->assertRedirect();

        $this->assertSame($pegawai->id, (int) FormUsulan::sole()->submitted_by);
    }

    public function test_seeder_creates_admin_satker_and_daerah_pegawai(): void
    {
        $this->seed();

        $admin = User::where('nip', '199001012015011001')->sole();
        $this->assertSame(User::ROLE_ADMIN_SATKER, $admin->role);
        $this->assertSame('32', $admin->provinsi_kode);
        $this->assertSame(2, User::inSatkerScopeOf($admin)->count());
        $this->assertSame('pusat', User::where('nip', '199808182022032005')->value('satker_tipe'));
        $this->assertSame(0, User::whereNull('satker_tipe')->count());
    }
}
