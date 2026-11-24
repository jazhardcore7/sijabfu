<?php

namespace Tests\Feature\Phase6;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * FR-TK-1: Tim Kerja dari user aktif ber-role tim_jf, dengan halaman profile.
 */
class TimKerjaTest extends TestCase
{
    use RefreshDatabase;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pegawai = User::factory()->pegawai()->create();
    }

    public function test_list_comes_from_active_tim_jf_users(): void
    {
        $a = User::factory()->timJf()->create(['name' => 'Anggota Satu', 'nip' => '111', 'bidang' => 'Bidang Uji']);
        User::factory()->timJf()->create(['name' => 'Anggota Nonaktif', 'is_active' => false]);
        User::factory()->superAdmin()->create(['name' => 'Super Admin Saja']);
        User::factory()->pegawai()->create(['name' => 'Pegawai Biasa']);

        $response = $this->actingAs($this->pegawai)->get('/tim-kerja')->assertOk();

        $response->assertSee('Anggota Satu')->assertSee('NIP. 111')->assertSee('Bidang Uji');
        $response->assertSee('href="' . route('tim-kerja.show', $a) . '"', false);
        $response->assertDontSee('Anggota Nonaktif')->assertDontSee('Super Admin Saja')->assertDontSee('Pegawai Biasa');
        $response->assertSee('data-jumlah-anggota="1"', false);
    }

    public function test_adding_a_tim_jf_user_makes_it_appear(): void
    {
        $this->actingAs($this->pegawai)->get('/tim-kerja')->assertDontSee('Anggota Baru');

        User::factory()->timJf()->create(['name' => 'Anggota Baru']);

        $this->actingAs($this->pegawai)->get('/tim-kerja')->assertSee('Anggota Baru');
    }

    public function test_hardcoded_team_is_gone(): void
    {
        $this->actingAs($this->pegawai)->get('/tim-kerja')
            ->assertDontSee('Eviphanias H. Sinaga')
            ->assertDontSee('Fitri Handayani');

        $this->assertStringNotContainsString('Eviphanias', File::get(app_path('Http/Controllers/PortalController.php')));
    }

    public function test_profile_shows_required_fields(): void
    {
        $anggota = User::factory()->timJf()->create([
            'name' => 'Rina Pembina, S.ST.',
            'nip' => '198001012005012001',
            'jabatan' => 'Ketua Tim Pembinaan JF',
            'bidang' => 'Biro SDM',
            'kontak' => '0812-0000-0000',
        ]);

        $response = $this->actingAs($this->pegawai)->get("/tim-kerja/{$anggota->id}")->assertOk();

        foreach (['Rina Pembina, S.ST.', '198001012005012001', 'Ketua Tim Pembinaan JF', 'Biro SDM', '0812-0000-0000'] as $value) {
            $response->assertSee($value);
        }
        $response->assertSee('data-section="foto"', false)->assertSee('>RP<', false);
        $response->assertSee('data-action="chat-tim"', false);

        // staf melihat profile tanpa tombol chat pegawai
        $this->actingAs(User::factory()->timJf()->create())->get("/tim-kerja/{$anggota->id}")
            ->assertOk()->assertDontSee('data-action="chat-tim"', false);
    }

    public function test_profile_of_non_tim_jf_or_inactive_is_404(): void
    {
        $nonaktif = User::factory()->timJf()->create(['is_active' => false]);
        $superAdmin = User::factory()->superAdmin()->create();
        $pegawaiLain = User::factory()->pegawai()->create();

        foreach ([$nonaktif, $superAdmin, $pegawaiLain] as $user) {
            $this->actingAs($this->pegawai)->get("/tim-kerja/{$user->id}")->assertNotFound();
        }
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/tim-kerja')->assertRedirect(route('login'));
    }
}
