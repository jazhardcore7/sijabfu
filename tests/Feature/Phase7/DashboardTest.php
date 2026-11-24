<?php

namespace Tests\Feature\Phase7;

use App\Models\FormUsulan;
use App\Models\SsoSyncLog;
use App\Models\User;
use App\Services\UsulanStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * Dashboard per role: FR-DASH-3, FR-DASH-4, FR-DASH-5.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function chartJson(string $html, string $id): array
    {
        $this->assertMatchesRegularExpression('/data-chart-json="' . preg_quote($id, '/') . '">(.*?)<\/script>/s', $html);
        preg_match('/data-chart-json="' . preg_quote($id, '/') . '">(.*?)<\/script>/s', $html, $m);

        return json_decode($m[1], true);
    }

    public static function pegawaiStatuses(): array
    {
        // [status_key, label, persen, stage aktif, state]
        return [
            'verifikasi' => ['verifikasi', 'Verifikasi', 0, 'verifikasi', 'active'],
            'perbaikan'  => ['perbaikan', 'Perbaikan', 0, 'verifikasi', 'perbaikan'],
            'disetujui'  => ['disetujui', 'Disetujui (MS)', 25, 'disetujui', 'active'],
            'ditetapkan' => ['ditetapkan', 'Ditetapkan', 50, 'ditetapkan', 'active'],
            'selesai'    => ['selesai', 'Selesai', 100, 'selesai', 'done'],
        ];
    }

    // FR-DASH-3
    #[DataProvider('pegawaiStatuses')]
    public function test_pegawai_dashboard_shows_correct_values_at_each_status(string $status, string $label, int $persen, string $stage, string $state): void
    {
        $pegawai = User::factory()->pegawai()->create();
        $this->usulanAt($pegawai, $status, ['catatan' => 'Catatan terbaru']);

        $html = $this->actingAs($pegawai)->get('/dashboard')->assertOk()
            ->assertSee('data-section="dashboard-pegawai"', false)
            ->assertSee('data-status-key="' . $status . '">' . $label . '<', false)
            ->assertSee('Catatan terbaru')
            ->getContent();

        $progres = $this->chartJson($html, 'progres-pegawai');
        $this->assertSame($status, $progres['status_key']);
        $this->assertSame($persen, $progres['persen']);
        $this->assertSame(4, count($progres['tahapan']));
        $this->assertStringContainsString('data-stage="' . $stage . '" data-state="' . $state . '"', $html);

        // konten landing tetap ada (kartu modul)
        $this->assertStringContainsString('cards-section', $html);
    }

    // FR-DASH-3
    public function test_pegawai_dashboard_lists_latest_notifications_and_empty_state(): void
    {
        $pegawai = User::factory()->pegawai()->create();
        $this->actingAs($pegawai)->get('/dashboard')->assertSee('data-section="belum-ada-usulan"', false)->assertSee('Belum ada notifikasi.');

        $usulan = $this->usulanAt($pegawai, 'verifikasi');
        app(UsulanStateMachine::class)->decide($usulan, User::factory()->timJf()->create(), 'ms');

        $this->actingAs($pegawai)->get('/dashboard')
            ->assertSee('data-section="notifikasi-terbaru"', false)
            ->assertSee('Usulan disetujui (MS)');
    }

    // FR-DASH-4
    public function test_staff_chart_data_equals_database_counts(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 15));

        $make = function (string $status, string $jenis, string $submitted) {
            $this->usulanAt(User::factory()->pegawai()->create(), $status, ['jenis_usulan' => $jenis, 'submitted_at' => $submitted]);
        };
        $make('verifikasi', 'Inpassing', '2026-11-02');
        $make('verifikasi', 'Konsultasi JF', '2026-11-20');
        $make('perbaikan', 'Inpassing', '2026-10-05');
        $make('disetujui', 'Uji Kompetensi Kenaikan Jenjang', '2026-06-01');
        $make('selesai', 'Inpassing', '2025-12-31');
        $make('selesai', 'Inpassing', '2025-11-30'); // di luar 12 bulan terakhir
        $this->usulanAt(User::factory()->pegawai()->create(), null, ['jenis_usulan' => 'Inpassing']); // draf tidak dihitung

        $html = $this->actingAs(User::factory()->timJf()->create())->get('/dashboard')->assertOk()->getContent();

        $perStatus = $this->chartJson($html, 'per-status');
        $expectedStatus = [];
        foreach (FormUsulan::STATUS_LABELS as $key => $label) {
            $expectedStatus[$label] = FormUsulan::submitted()->where('status_key', $key)->count();
        }
        $this->assertSame($expectedStatus, $perStatus);
        $this->assertSame(['Verifikasi' => 2, 'Perbaikan' => 1, 'Disetujui (MS)' => 1, 'Ditetapkan' => 0, 'Selesai' => 2], $perStatus);

        $perJenis = $this->chartJson($html, 'per-jenis');
        foreach ($perJenis as $jenis => $n) {
            $this->assertSame(FormUsulan::submitted()->where('jenis_usulan', $jenis)->count(), $n, $jenis);
        }
        $this->assertSame(4, $perJenis['Inpassing']);

        $perBulan = $this->chartJson($html, 'per-bulan');
        $this->assertCount(12, $perBulan);
        $this->assertSame('Des 2025', array_key_first($perBulan));
        $this->assertSame('Nov 2026', array_key_last($perBulan));
        $this->assertSame(2, $perBulan['Nov 2026']);
        $this->assertSame(1, $perBulan['Okt 2026']);
        $this->assertSame(1, $perBulan['Jun 2026']);
        $this->assertSame(1, $perBulan['Des 2025']);
        $this->assertSame(5, array_sum($perBulan));

        // konten dashboard lama tetap ada; Tim JF tidak melihat kartu SSO
        $this->assertStringContainsString('Manajemen Status Usulan Jabatan Fungsional', $html);
        $this->assertStringContainsString('Link Internal', $html);
        $this->assertStringNotContainsString('data-section="sso-sync-card"', $html);
        $this->assertStringNotContainsString('data-chart-json="user-per-role"', $html);
    }

    // FR-DASH-5
    public function test_super_admin_sees_sso_card_with_latest_log_and_users_per_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->timJf()->count(2)->create();
        User::factory()->pegawai()->count(3)->create();

        $html = $this->actingAs($superAdmin)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('data-section="sso-sync-card"', $html);
        $this->assertStringContainsString('data-sso="empty"', $html);
        $this->assertStringContainsString('data-chart-json="per-status"', $html);

        $this->assertSame(
            ['Pegawai' => User::where('role', 'pegawai')->count(), 'Tim Pembinaan JF' => 2, 'Super Admin' => 1],
            $this->chartJson($html, 'user-per-role')
        );

        SsoSyncLog::create(['started_at' => '2026-11-01 08:00:00', 'finished_at' => '2026-11-01 08:05:00', 'created_count' => 1, 'updated_count' => 1, 'failed_count' => 1]);
        SsoSyncLog::create(['started_at' => '2026-11-20 09:00:00', 'finished_at' => '2026-11-20 09:02:00', 'created_count' => 4, 'updated_count' => 7, 'failed_count' => 2, 'error' => 'Timeout NIP 123']);

        $this->actingAs($superAdmin)->get('/dashboard')
            ->assertSee('data-sso="started">20 Nov 2026 09:00<', false)
            ->assertSee('data-sso="created">4<', false)
            ->assertSee('data-sso="updated">7<', false)
            ->assertSee('data-sso="failed">2<', false)
            ->assertSee('Timeout NIP 123')
            ->assertSee('data-action="sinkronkan"', false);
    }

    public function test_login_sends_every_role_to_dashboard(): void
    {
        foreach (['pegawai', 'timJf', 'superAdmin'] as $i => $state) {
            User::factory()->{$state}()->create(['nip' => '90000000000000000' . $i]);
            $this->post('/login', ['nip' => '90000000000000000' . $i, 'password' => 'password'])->assertRedirect(route('dashboard'));
            $this->post('/logout');
        }
    }
}
