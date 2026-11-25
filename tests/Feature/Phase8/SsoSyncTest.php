<?php

namespace Tests\Feature\Phase8;

use App\Contracts\SsoProvider;
use App\Models\SsoSyncLog;
use App\Models\User;
use App\Sso\SsoIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * FR-USR-2: Sinkronisasi Data SSO (upsert berdasarkan NIP, role tidak diubah, log per proses).
 */
class SsoSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->superAdmin()->create(['nip' => '198001012005011001', 'email' => 'superadmin.jf@bps.go.id', 'name' => 'Super Admin SIGMA JF', 'jabatan' => 'Super Admin Pembinaan Jabatan Fungsional', 'unit_kerja' => 'Biro SDM - BPS RI']);
    }

    public function test_run_creates_and_updates_users_and_never_changes_role(): void
    {
        $existing = User::factory()->timJf()->create([
            'nip' => '199505152020121002',
            'name' => 'Nama Lama',
            'email' => 'lama@bps.go.id',
        ]);

        $this->actingAs($this->superAdmin)->post('/admin/sso-sync')
            ->assertRedirect(route('admin.sso-sync'))
            ->assertSessionHas('success');

        $log = SsoSyncLog::sole();
        $this->assertSame($this->superAdmin->id, (int) $log->triggered_by);
        $this->assertNotNull($log->started_at);
        $this->assertNotNull($log->finished_at);
        // direktori fiktif: 9 orang (S1 menambah 3 orang satker daerah); Budi dan Super Admin diperbarui
        // (Super Admin: sso_id ditautkan); 7 orang baru
        $this->assertSame(7, $log->created_count);
        $this->assertSame(2, $log->updated_count);
        $this->assertSame('fake-sso-0002', $this->superAdmin->fresh()->sso_id);
        $this->assertSame(0, $log->failed_count);
        $this->assertNull($log->error);

        $existing->refresh();
        $this->assertSame('Budi Pratama, S.Tr.Stat.', $existing->name);
        $this->assertSame('budi.pratama@bps.go.id', $existing->email);
        $this->assertSame('tim_jf', $existing->role, 'role tidak diubah oleh sinkronisasi');

        $new = User::where('nip', '200002022024012002')->sole();
        $this->assertSame('pegawai', $new->role);
        $this->assertTrue($new->is_active);
        $this->assertFalse(Hash::check('password', $new->password));
        $this->assertSame('super_admin', $this->superAdmin->fresh()->role);
    }

    public function test_second_run_counts_no_changes(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/sso-sync');
        $this->actingAs($this->superAdmin)->post('/admin/sso-sync');

        $last = SsoSyncLog::orderByDesc('id')->first();
        $this->assertSame([0, 0, 0], [$last->created_count, $last->updated_count, $last->failed_count]);
        $this->assertSame(2, SsoSyncLog::count());
    }

    public function test_invalid_rows_are_counted_as_failed(): void
    {
        User::factory()->pegawai()->create(['nip' => '300000000000000001', 'email' => 'dipakai@example.test']);

        config(['sijabfu_fake.people' => [
            ['nip' => '', 'name' => 'Tanpa NIP', 'email' => 'tanpa.nip@example.test'],
            ['nip' => '300000000000000002', 'name' => 'Email Salah', 'email' => 'bukan-email'],
            ['nip' => '300000000000000003', 'name' => 'Email Bentrok', 'email' => 'dipakai@example.test'],
            ['nip' => '300000000000000004', 'name' => 'Valid', 'email' => 'valid@example.test'],
        ]]);

        $this->actingAs($this->superAdmin)->post('/admin/sso-sync')->assertSessionHas('success');

        $log = SsoSyncLog::sole();
        $this->assertSame([1, 0, 3], [$log->created_count, $log->updated_count, $log->failed_count]);
        $this->assertStringContainsString('dipakai@example.test', $log->error);
        $this->assertFalse(User::where('nip', '300000000000000003')->exists());
    }

    public function test_unreadable_directory_writes_error_log(): void
    {
        $this->app->bind(SsoProvider::class, fn () => new class implements SsoProvider
        {
            public function redirect(Request $request, string $state): Response
            {
                throw new RuntimeException('x');
            }

            public function identityFromCallback(Request $request): ?SsoIdentity
            {
                return null;
            }

            public function directory(): iterable
            {
                throw new RuntimeException('koneksi ditolak');
            }
        });

        $this->actingAs($this->superAdmin)->post('/admin/sso-sync')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'koneksi ditolak'));

        $log = SsoSyncLog::sole();
        $this->assertNotNull($log->finished_at);
        $this->assertStringContainsString('koneksi ditolak', $log->error);
    }

    public function test_page_lists_history_and_dashboard_button_posts_to_sync(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/sso-sync');

        $this->actingAs($this->superAdmin)->get('/admin/sso-sync')->assertOk()
            ->assertSee('Riwayat Sinkronisasi')
            ->assertSee('data-log="', false);

        $this->actingAs($this->superAdmin)->get('/dashboard')
            ->assertSee('action="'.route('admin.sso-sync.run').'"', false)
            ->assertSee('data-sso="created">8<', false);
    }

    public function test_only_super_admin_can_sync(): void
    {
        foreach ([User::factory()->timJf()->create(), User::factory()->pegawai()->create()] as $user) {
            $this->actingAs($user)->get('/admin/sso-sync')->assertForbidden();
            $this->actingAs($user)->post('/admin/sso-sync')->assertForbidden();
        }
        $this->assertSame(0, SsoSyncLog::count());
    }
}
