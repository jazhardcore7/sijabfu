<?php

namespace Tests\Feature\Phase3;

use App\Models\User;
use App\Models\UsulanBerkas;
use App\Services\UsulanStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pelacakan Status (PRD Revisi 5.4): FR-PS-1 s.d. FR-PS-5, dan FR-PU-7.
 */
class PelacakanStatusTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->pegawai = User::factory()->pegawai()->create();
    }

    public static function statuses(): array
    {
        // [status_key, label, stage yang aktif/perbaikan, surat tampil, sertifikat tampil]
        return [
            'verifikasi' => ['verifikasi', 'Verifikasi', 'verifikasi', false, false],
            'perbaikan' => ['perbaikan', 'Perbaikan', 'verifikasi', false, false],
            'disetujui' => ['disetujui', 'Disetujui (MS)', 'disetujui', false, false],
            'ditetapkan' => ['ditetapkan', 'Ditetapkan', 'ditetapkan', true, false],
            'selesai' => ['selesai', 'Selesai', null, true, true],
        ];
    }

    // FR-PS-1, FR-PS-2, FR-PS-4, FR-PS-5
    #[DataProvider('statuses')]
    public function test_page_renders_correctly_at_each_status(string $status, string $label, ?string $activeStage, bool $surat, bool $sertifikat): void
    {
        $this->usulanAt($this->pegawai, $status, [
            'catatan' => 'Catatan terbaru tim',
            'surat_usulan_path' => $surat ? 'usulan/surat.pdf' : null,
            'sertifikat_path' => $sertifikat ? 'usulan/sertifikat.pdf' : null,
        ]);

        $response = $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertOk();
        $html = $response->getContent();

        $response->assertSee('data-status-key="'.$status.'"', false);
        $response->assertSee('● '.$label);
        foreach (['Verifikasi (Perbaikan)', 'Disetujui (MS/TMS)', 'Ditetapkan', 'Selesai'] as $stage) {
            $response->assertSee($stage);
        }
        $response->assertSee('Catatan terbaru tim');

        if ($activeStage) {
            $state = $status === 'perbaikan' ? 'perbaikan' : 'active';
            $this->assertStringContainsString('data-stage="'.$activeStage.'" data-state="'.$state.'"', $html);
        } else {
            $this->assertSame(4, substr_count($html, 'data-state="done"'));
        }

        // FR-PS-2: banner perbaikan hanya pada status perbaikan
        $this->assertSame($status === 'perbaikan', str_contains($html, 'data-section="perbaikan-banner"'));
        // FR-PS-4: tombol surat hanya pada ditetapkan / selesai
        $this->assertSame($surat, str_contains($html, 'data-action="download-surat"'));
        $this->assertSame($surat, str_contains($html, 'data-action="preview-surat"'));
        // FR-PS-5: tombol sertifikat hanya pada selesai
        $this->assertSame($sertifikat, str_contains($html, 'data-action="download-sertifikat"'));
        $this->assertSame($sertifikat, str_contains($html, 'data-action="preview-sertifikat"'));
    }

    // FR-PS-1: timeline dari log status dengan tanggal dan role aktor
    public function test_timeline_lists_each_transition_with_date_and_actor_role(): void
    {
        $usulan = $this->usulanAt($this->pegawai, null);
        $timJf = User::factory()->timJf()->create();
        $sm = new UsulanStateMachine;
        $sm->submit($usulan, $this->pegawai);
        $sm->decide($usulan, $timJf, 'tms', 'Perbaiki SKP');
        $sm->resubmit($usulan, $this->pegawai);

        $response = $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertOk();
        $html = $response->getContent();

        $this->assertSame(3, substr_count($html, 'data-log-to='));
        $response->assertSeeInOrder(['Dikirim →', 'Verifikasi', 'oleh Pegawai', 'Verifikasi → ', 'Perbaikan', 'oleh Tim Pembinaan JF', 'Perbaiki SKP', 'Perbaikan → ', 'Verifikasi', 'oleh Pegawai']);
        $response->assertSee(now()->format('d M Y'));
    }

    // FR-PS-2: banner berisi catatan dan berkas yang ditandai
    public function test_perbaikan_banner_lists_flagged_files(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'perbaikan', ['catatan' => 'Berkas belum lengkap']);
        $usulan->berkas()->where('jenis_persyaratan', 'dupak')->update([
            'pemeriksaan' => UsulanBerkas::PEMERIKSAAN_PERLU_PERBAIKAN,
            'catatan' => 'DUPAK kedaluwarsa',
        ]);

        $this->actingAs($this->pegawai)->get('/pelacakan-status')
            ->assertSee('Berkas belum lengkap')
            ->assertSee('DUPAK / Bukti Capaian Angka Kredit Terakhir: DUPAK kedaluwarsa');
    }

    // FR-PS-3
    public function test_ms_or_tms_label_matches_stored_value(): void
    {
        $usulan = $this->usulanAt($this->pegawai, 'disetujui', ['hasil_verifikasi' => 'ms']);
        $this->actingAs($this->pegawai)->get('/pelacakan-status')
            ->assertSee('data-hasil="ms"', false)->assertSee('Hasil Verifikasi: MS');

        $usulan->update(['status_key' => 'perbaikan', 'hasil_verifikasi' => 'tms']);
        $this->actingAs($this->pegawai)->get('/pelacakan-status')
            ->assertSee('data-hasil="tms"', false)->assertSee('Hasil Verifikasi: TMS');
    }

    // FR-PS-5
    public function test_selesai_shows_hasil_ukom_when_present(): void
    {
        $this->usulanAt($this->pegawai, 'selesai', [
            'surat_usulan_path' => 'usulan/s.pdf',
            'sertifikat_path' => 'usulan/c.pdf',
            'hasil_ukom' => 'Kompeten',
        ]);

        $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertSee('Hasil UKOM: Kompeten');
    }

    // PRD bagian 4: baris lama tanpa file
    public function test_legacy_row_without_file_shows_dokumen_belum_diunggah(): void
    {
        $this->usulanAt($this->pegawai, 'selesai');

        $response = $this->actingAs($this->pegawai)->get('/pelacakan-status');
        $this->assertSame(2, substr_count($response->getContent(), 'Dokumen belum diunggah'));
        $response->assertDontSee('data-action="download-surat"', false);
    }

    public function test_empty_state_and_draft_state(): void
    {
        $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertSee('Belum Ada Usulan');

        $this->usulanAt($this->pegawai, null);
        $this->actingAs($this->pegawai)->get('/pelacakan-status')->assertSee('Usulan Belum Dikirim');
    }

    // FR-PU-7
    public function test_legacy_links_render_only_for_http_and_https(): void
    {
        $this->usulanAt($this->pegawai, 'verifikasi', [
            'link_berkas' => 'javascript:alert(1)',
            'link_usulan' => 'https://drive.example.test/usulan',
        ]);
        $timJf = User::factory()->timJf()->create();

        $pages = [
            [$this->pegawai, '/pelacakan-status'],
            [$this->pegawai, '/pengajuan'],
            [$timJf, '/dashboard'],
            [$timJf, '/monitoring'],
        ];

        foreach ($pages as [$user, $uri]) {
            $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();
            $this->assertStringNotContainsString('href="javascript:', $html, $uri);
            $this->assertStringNotContainsString('javascript:alert', $html, $uri);
            if ($uri !== '/monitoring') { // monitoring hanya menampilkan link_berkas
                $this->assertStringContainsString('href="https://drive.example.test/usulan"', $html, $uri);
            }
        }
    }
}
