<?php

namespace Tests\Feature\Phase3;

use App\Models\FormUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * FR-PS-6 (dan FR-RBAC-2 bagian file): file dilayani route yang diotorisasi.
 */
class FileAccessTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $owner;

    private FormUsulan $usulan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('usulan/surat.pdf', '%PDF surat');
        Storage::disk('local')->put('usulan/sertifikat.pdf', '%PDF sertifikat');

        $this->owner = User::factory()->pegawai()->create(['nip' => '199001012020011001']);
        $this->usulan = $this->usulanAt($this->owner, 'selesai', [
            'surat_usulan_path' => 'usulan/surat.pdf',
            'sertifikat_path' => 'usulan/sertifikat.pdf',
        ]);
    }

    private function urls(): array
    {
        $berkasId = $this->usulan->berkas()->first()->id;

        return [
            'berkas' => "/files/berkas/{$berkasId}",
            'surat' => "/files/surat/{$this->usulan->id}",
            'sertifikat' => "/files/sertifikat/{$this->usulan->id}",
        ];
    }

    public function test_owner_gets_200_with_inline_preview_and_attachment_download(): void
    {
        foreach ($this->urls() as $type => $url) {
            $preview = $this->actingAs($this->owner)->get($url.'?mode=preview')->assertOk();
            $this->assertStringStartsWith('inline', $preview->headers->get('Content-Disposition'), $type);

            $download = $this->actingAs($this->owner)->get($url)->assertOk();
            $this->assertStringStartsWith('attachment', $download->headers->get('Content-Disposition'), $type);
        }

        $this->actingAs($this->owner)->get($this->urls()['surat'])
            ->assertDownload('surat-usulan-199001012020011001.pdf');
    }

    public function test_other_pegawai_gets_403(): void
    {
        $other = User::factory()->pegawai()->create();

        foreach ($this->urls() as $url) {
            $this->actingAs($other)->get($url)->assertForbidden();
            $this->actingAs($other)->get($url.'?mode=preview')->assertForbidden();
        }
    }

    public function test_tim_jf_and_super_admin_get_200(): void
    {
        foreach ([User::factory()->timJf()->create(), User::factory()->superAdmin()->create()] as $staff) {
            foreach ($this->urls() as $url) {
                $this->actingAs($staff)->get($url)->assertOk();
            }
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach ($this->urls() as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_owner_cannot_open_surat_or_sertifikat_before_their_status(): void
    {
        $this->usulan->update(['status_key' => 'disetujui']);
        $this->actingAs($this->owner)->get($this->urls()['surat'])->assertForbidden();
        $this->actingAs($this->owner)->get($this->urls()['sertifikat'])->assertForbidden();

        $this->usulan->update(['status_key' => 'ditetapkan']);
        $this->actingAs($this->owner)->get($this->urls()['surat'])->assertOk();
        $this->actingAs($this->owner)->get($this->urls()['sertifikat'])->assertForbidden();
    }

    public function test_missing_file_returns_404(): void
    {
        $this->usulan->update(['sertifikat_path' => null]);

        $this->actingAs($this->owner)->get($this->urls()['sertifikat'])->assertNotFound();
        $this->actingAs($this->owner)->get('/files/berkas/99999')->assertNotFound();
        $this->actingAs($this->owner)->get('/files/lainnya/1')->assertNotFound();
    }
}
