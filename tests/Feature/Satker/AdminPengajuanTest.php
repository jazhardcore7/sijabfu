<?php

namespace Tests\Feature\Satker;

use App\Models\FormUsulan;
use App\Models\User;
use App\Notifications\UsulanDikirim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-SATKER-8 (pengajuan untuk pegawai dalam cakupan) dan FR-SATKER-10 (sinkron SIMPEG pegawai terpilih).
 */
class AdminPengajuanTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $admin;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->adminSatker('32')->create(['name' => 'Admin Jabar']);
        $this->pegawai = User::factory()->pegawai()->kabkota('32', '73')->create([
            'name' => 'Pegawai Kota Bandung', 'nip' => '199101012019011001', 'email' => 'p.bandung@example.test', 'jabatan' => 'Statistisi Ahli Muda',
        ]);
    }

    public function test_sidebar_and_list_pages(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertSee('data-menu-route="satker.pengajuan.index"', false);
        $this->actingAs($this->admin)->get('/satker/pengajuan')->assertOk()->assertSee('data-action="usulan-baru"', false);
        $this->actingAs($this->admin)->get('/satker/pengajuan/pilih-pegawai')->assertOk()
            ->assertSee('Pegawai Kota Bandung')->assertSee('data-action="ajukan"', false);
        $this->actingAs($this->admin)->get('/satker/pengajuan/pilih-pegawai?q=199101')->assertSee('data-total="1"', false);
        $this->actingAs($this->admin)->get('/satker/pengajuan/pilih-pegawai?q=tidakada')->assertSee('data-total="0"', false);
    }

    public function test_create_form_shows_pegawai_identity(): void
    {
        $this->actingAs($this->admin)->get("/satker/pengajuan/baru/{$this->pegawai->id}")->assertOk()
            ->assertSee('id="formPengajuan"', false)
            ->assertSee('value="Pegawai Kota Bandung"', false)
            ->assertSee('value="199101012019011001"', false)
            ->assertSee('name="pegawai_id" value="'.$this->pegawai->id.'"', false)
            ->assertDontSee('value="Admin Jabar"', false);
    }

    public function test_store_and_kirim_uses_pegawai_identity_and_records_submitter(): void
    {
        Notification::fake();
        $timJf = User::factory()->timJf()->create();

        $this->actingAs($this->admin)->post('/satker/pengajuan', $this->dataUsulan([
            'pegawai_id' => $this->pegawai->id,
            'aksi' => 'kirim',
            'berkas' => $this->allBerkas(),
            // nilai identitas kiriman diabaikan
            'nama_lengkap' => 'Palsu', 'nip' => '000', 'email' => 'palsu@example.test',
        ]))->assertRedirect(route('satker.pengajuan.index'));

        $usulan = FormUsulan::sole();
        $this->assertSame([$this->pegawai->id, $this->admin->id], [(int) $usulan->user_id, (int) $usulan->submitted_by]);
        $this->assertSame(['Pegawai Kota Bandung', '199101012019011001', 'p.bandung@example.test'], [$usulan->nama_lengkap, $usulan->nip, $usulan->email]);
        $this->assertSame(FormUsulan::STATUS_VERIFIKASI, $usulan->status_key);
        $this->assertSame($this->admin->id, (int) $usulan->statusLogs()->sole()->actor_id);
        $this->assertCount(8, $usulan->berkas);
        Notification::assertSentTo($timJf, UsulanDikirim::class);

        $this->actingAs($this->admin)->get('/satker/pengajuan')->assertSee('data-usulan="'.$usulan->id.'"', false);
        $this->actingAs($this->admin)->get("/satker/pengajuan/{$usulan->id}")->assertOk()->assertSee('data-section="usulan-diproses"', false);
        // Usulan tidak dapat diubah selama diproses
        $this->actingAs($this->admin)->post("/satker/pengajuan/{$usulan->id}", $this->dataUsulan(['hal_surat' => 'Diubah']))
            ->assertSessionHas('error');
        $this->assertSame('Permohonan UKOM', $usulan->fresh()->hal_surat);
    }

    public function test_simpan_draft_then_kirim_requires_all_berkas(): void
    {
        $this->actingAs($this->admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $this->pegawai->id, 'berkas' => $this->allBerkas(['skp'])]))
            ->assertRedirect();
        $usulan = FormUsulan::sole();
        $this->assertTrue($usulan->isDraft());

        $this->actingAs($this->admin)->post("/satker/pengajuan/{$usulan->id}", $this->dataUsulan(['aksi' => 'kirim']))
            ->assertSessionHas('error');
        $this->assertTrue($usulan->fresh()->isDraft());

        $this->actingAs($this->admin)->post("/satker/pengajuan/{$usulan->id}", $this->dataUsulan(['aksi' => 'kirim', 'berkas' => $this->allBerkas()]))
            ->assertRedirect(route('satker.pengajuan.index'));
        $this->assertSame(FormUsulan::STATUS_VERIFIKASI, $usulan->fresh()->status_key);
    }

    public function test_draft_by_admin_is_not_shown_to_pegawai(): void
    {
        $this->actingAs($this->admin)->post('/satker/pengajuan', $this->dataUsulan(['pegawai_id' => $this->pegawai->id]));

        $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertOk()
            ->assertDontSee('Usulan Belum Dikirim')->assertSee('Belum Ada Usulan');
    }

    public function test_sinkron_simpeg_for_chosen_pegawai(): void
    {
        // NIP fiktif 200002022024012002 di direktori fake: Kabupaten/Kota 3273 (Prov 32)
        $pegawai = User::factory()->pegawai()->kabkota('32', '73')->create(['nip' => '200002022024012002', 'name' => 'Nama Lama']);

        $this->actingAs($this->admin)->post("/satker/pengajuan/pegawai/{$pegawai->id}/sinkron")
            ->assertRedirect(route('satker.pengajuan.create', $pegawai))
            ->assertSessionHas('success');
        $this->assertSame('Contoh Pegawai Fiktif Dua', $pegawai->fresh()->name);
        $this->assertSame((string) $this->admin->fresh()->name, 'Admin Jabar', 'akun Admin tidak ikut berubah');
    }

    public function test_sinkron_failure_shows_warning(): void
    {
        $this->actingAs($this->admin)->post("/satker/pengajuan/pegawai/{$this->pegawai->id}/sinkron")
            ->assertRedirect(route('satker.pengajuan.create', $this->pegawai))
            ->assertSessionHas('warning');
        $this->assertSame('Pegawai Kota Bandung', $this->pegawai->fresh()->name);
    }

    public function test_sinkron_moving_pegawai_out_of_scope_returns_to_list(): void
    {
        // Direktori fake: NIP ini berada di Provinsi 33
        $pegawai = User::factory()->pegawai()->provinsi('32')->create(['nip' => '200003032024013003']);

        $this->actingAs($this->admin)->post("/satker/pengajuan/pegawai/{$pegawai->id}/sinkron")
            ->assertRedirect(route('satker.pengajuan.pilih-pegawai'))
            ->assertSessionHas('warning');
        $this->assertSame('33', $pegawai->fresh()->provinsi_kode);
        $this->actingAs($this->admin)->get("/satker/pengajuan/baru/{$pegawai->id}")->assertForbidden();
    }
}
