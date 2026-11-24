<?php

namespace Tests\Feature\Phase4;

use App\Models\User;
use App\Models\UsulanBerkas;
use App\Models\UsulanStatusLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * Verifikasi Berkas Usulan Pegawai (PRD Revisi 5.5): FR-VB-1 s.d. FR-VB-5.
 */
class VerifikasiTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $timJf;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->timJf = User::factory()->timJf()->create();
    }

    private function pegawai(string $name, string $nip): User
    {
        return User::factory()->pegawai()->create(['name' => $name, 'nip' => $nip]);
    }

    // FR-VB-1
    public function test_list_filters_by_status_and_search_combine_and_paginate_by_15(): void
    {
        $andi = $this->usulanAt($this->pegawai('Andi Wijaya', '111'), 'verifikasi');
        $this->usulanAt($this->pegawai('Andi Saputra', '222'), 'disetujui');
        $this->usulanAt($this->pegawai('Budi Santoso', '333'), 'verifikasi');
        $this->usulanAt($this->pegawai('Draf Saja', '444'), null);

        $all = $this->actingAs($this->timJf)->get('/verifikasi')->assertOk();
        $all->assertSee('data-total="3"', false)->assertDontSee('Draf Saja');

        $this->actingAs($this->timJf)->get('/verifikasi?status=verifikasi')
            ->assertSee('Andi Wijaya')->assertSee('Budi Santoso')->assertDontSee('Andi Saputra');

        $this->actingAs($this->timJf)->get('/verifikasi?q=Andi')
            ->assertSee('Andi Wijaya')->assertSee('Andi Saputra')->assertDontSee('Budi Santoso');

        $combined = $this->actingAs($this->timJf)->get('/verifikasi?q=Andi&status=verifikasi');
        $combined->assertSee('data-total="1"', false)->assertSee('data-usulan="'.$andi->id.'"', false);

        $this->actingAs($this->timJf)->get('/verifikasi?q=333')->assertSee('Budi Santoso')->assertDontSee('Andi Wijaya');

        for ($i = 0; $i < 15; $i++) {
            $this->usulanAt(User::factory()->pegawai()->create(), 'verifikasi');
        }
        $page1 = $this->actingAs($this->timJf)->get('/verifikasi')->getContent();
        $page2 = $this->actingAs($this->timJf)->get('/verifikasi?page=2')->getContent();
        $this->assertSame(15, substr_count($page1, 'data-usulan='));
        $this->assertSame(3, substr_count($page2, 'data-usulan='));
    }

    // FR-VB-2
    public function test_detail_lists_every_berkas_and_each_opens(): void
    {
        $usulan = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');

        $response = $this->actingAs($this->timJf)->get("/verifikasi/{$usulan->id}")->assertOk();
        $response->assertSee('Andi')->assertSee('001/TEST');

        $this->assertSame(8, substr_count($response->getContent(), 'data-action="preview-berkas"'));
        foreach ($usulan->berkas as $berkas) {
            $response->assertSee($berkas->original_name);
            $this->actingAs($this->timJf)->get("/files/berkas/{$berkas->id}?mode=preview")->assertOk();
            $this->actingAs($this->timJf)->get("/files/berkas/{$berkas->id}")->assertOk();
        }
    }

    public function test_draft_detail_is_not_found(): void
    {
        $draft = $this->usulanAt($this->pegawai('Draf', '1'), null);

        $this->actingAs($this->timJf)->get("/verifikasi/{$draft->id}")->assertNotFound();
    }

    // FR-VB-3
    public function test_pemeriksaan_marks_persist_per_berkas(): void
    {
        $usulan = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');
        [$skp, $dupak] = [$usulan->berkas()->where('jenis_persyaratan', 'skp')->first(), $usulan->berkas()->where('jenis_persyaratan', 'dupak')->first()];

        $this->actingAs($this->timJf)->post("/verifikasi/{$usulan->id}", [
            'aksi' => 'periksa',
            'pemeriksaan' => [
                $skp->id => ['hasil' => 'sesuai', 'catatan' => ''],
                $dupak->id => ['hasil' => 'perlu_perbaikan', 'catatan' => 'Tanda tangan belum ada'],
            ],
        ])->assertRedirect(route('verifikasi.show', $usulan));

        $this->assertSame('sesuai', $skp->fresh()->pemeriksaan);
        $this->assertNull($skp->fresh()->catatan);
        $this->assertSame('perlu_perbaikan', $dupak->fresh()->pemeriksaan);
        $this->assertSame('Tanda tangan belum ada', $dupak->fresh()->catatan);
        $this->assertSame('verifikasi', $usulan->fresh()->status_key, 'pemeriksaan tidak mengubah status');
    }

    public function test_pemeriksaan_ignores_berkas_of_another_usulan_and_is_refused_outside_verifikasi(): void
    {
        $usulan = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');
        $other = $this->usulanAt($this->pegawai('Budi', '222'), 'verifikasi');
        $otherBerkas = $other->berkas()->first();

        $this->actingAs($this->timJf)->post("/verifikasi/{$usulan->id}", [
            'aksi' => 'periksa',
            'pemeriksaan' => [$otherBerkas->id => ['hasil' => 'perlu_perbaikan']],
        ]);
        $this->assertNull($otherBerkas->fresh()->pemeriksaan);

        $disetujui = $this->usulanAt($this->pegawai('Citra', '333'), 'disetujui');
        $berkas = $disetujui->berkas()->first();
        $this->actingAs($this->timJf)->post("/verifikasi/{$disetujui->id}", [
            'aksi' => 'periksa',
            'pemeriksaan' => [$berkas->id => ['hasil' => 'perlu_perbaikan']],
        ])->assertStatus(422);
        $this->assertNull($berkas->fresh()->pemeriksaan);
    }

    // FR-VB-4
    public function test_ms_decision_sets_disetujui(): void
    {
        $usulan = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');

        $this->actingAs($this->timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])
            ->assertRedirect(route('verifikasi.show', $usulan));

        $this->assertSame('disetujui', $usulan->fresh()->status_key);
        $this->assertSame('ms', $usulan->fresh()->hasil_verifikasi);
        $this->assertSame(1, UsulanStatusLog::where('form_usulan_id', $usulan->id)->where('to_status', 'disetujui')->count());
    }

    // FR-VB-4
    public function test_tms_with_catatan_or_flagged_berkas_sets_perbaikan(): void
    {
        $a = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');
        $this->actingAs($this->timJf)->post("/verifikasi/{$a->id}", ['aksi' => 'keputusan', 'hasil' => 'tms', 'catatan' => 'Lengkapi SKP']);
        $this->assertSame('perbaikan', $a->fresh()->status_key);
        $this->assertSame('Lengkapi SKP', $a->fresh()->catatan);

        $b = $this->usulanAt($this->pegawai('Budi', '222'), 'verifikasi');
        $b->berkas()->first()->update(['pemeriksaan' => UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN]);
        $this->actingAs($this->timJf)->post("/verifikasi/{$b->id}", ['aksi' => 'keputusan', 'hasil' => 'tms']);
        $this->assertSame('perbaikan', $b->fresh()->status_key);
    }

    // FR-VB-4
    public function test_tms_without_reason_returns_422_and_changes_nothing(): void
    {
        $usulan = $this->usulanAt($this->pegawai('Andi', '111'), 'verifikasi');

        $this->actingAs($this->timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'tms', 'catatan' => ''])
            ->assertStatus(422)
            ->assertSee('data-section="transition-error"', false);

        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertNull($usulan->fresh()->hasil_verifikasi);
        $this->assertSame(0, UsulanStatusLog::count());
    }

    // FR-VB-5
    public function test_decision_outside_verifikasi_returns_422(): void
    {
        foreach (['perbaikan', 'disetujui', 'ditetapkan', 'selesai'] as $i => $status) {
            $usulan = $this->usulanAt($this->pegawai('P'.$i, (string) $i), $status);

            $this->actingAs($this->timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])
                ->assertStatus(422);

            $this->assertSame($status, $usulan->fresh()->status_key, $status);
        }
        $this->assertSame(0, UsulanStatusLog::count());
    }

    public function test_super_admin_can_verify_and_pegawai_cannot(): void
    {
        $pemilik = $this->pegawai('Andi', '111');
        $usulan = $this->usulanAt($pemilik, 'verifikasi');

        $this->actingAs($pemilik)->get('/verifikasi')->assertForbidden();
        $this->actingAs($pemilik)->get("/verifikasi/{$usulan->id}")->assertForbidden();
        $this->actingAs($pemilik)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])->assertForbidden();
        $this->assertSame('verifikasi', $usulan->fresh()->status_key);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])
            ->assertRedirect();
        $this->assertSame('disetujui', $usulan->fresh()->status_key);
    }
}
