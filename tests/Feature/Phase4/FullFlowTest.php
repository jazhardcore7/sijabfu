<?php

namespace Tests\Feature\Phase4;

use App\Models\FormUsulan;
use App\Models\UsulanBerkas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * PRD Revisi bagian 10: alur lengkap lewat HTTP.
 * Pegawai kirim -> Tim JF TMS -> Pegawai perbaiki dan kirim ulang -> Tim JF MS ->
 * surat diunggah (ditetapkan, Pegawai unduh surat) -> sertifikat diunggah (selesai, Pegawai unduh sertifikat).
 */
class FullFlowTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_full_flow_end_to_end(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->create(['nip' => '199001012020011001']);
        $timJf = User::factory()->timJf()->create();

        // 1. Pegawai mengirim usulan lengkap
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan([
            'aksi' => 'kirim',
            'berkas' => $this->allBerkas(),
        ]))->assertRedirect(route('pelacakan-status'));
        $usulan = FormUsulan::sole();
        $this->assertSame('verifikasi', $usulan->status_key);

        // 2. Tim JF menandai berkas dan memutuskan TMS
        $skp = $usulan->berkas()->where('jenis_persyaratan', 'skp')->first();
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", [
            'aksi' => 'periksa',
            'pemeriksaan' => [$skp->id => ['hasil' => 'perlu_perbaikan', 'catatan' => 'SKP buram']],
        ])->assertRedirect();
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'tms'])->assertRedirect();
        $this->assertSame('perbaikan', $usulan->fresh()->status_key);
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertSee('SKP buram')->assertSee('data-hasil="tms"', false);

        // 3. Pegawai memperbaiki dan mengirim ulang
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan(['berkas' => ['skp' => $this->pdf('skp-baru.pdf')]]))->assertRedirect();
        $this->actingAs($pegawai)->post('/pengajuan/kirim-ulang')->assertRedirect(route('pelacakan-status'));
        $this->assertSame('verifikasi', $usulan->fresh()->status_key);
        $this->assertNull(UsulanBerkas::find($skp->id)->pemeriksaan);

        // 4. Tim JF memutuskan MS
        $this->actingAs($timJf)->post("/verifikasi/{$usulan->id}", ['aksi' => 'keputusan', 'hasil' => 'ms'])->assertRedirect();
        $this->assertSame('disetujui', $usulan->fresh()->status_key);
        $this->actingAs($pegawai)->get("/files/surat/{$usulan->id}")->assertForbidden();

        // 5. Surat usulan diunggah -> ditetapkan; Pegawai mengunduh surat
        $this->actingAs($timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'tetapkan', 'surat' => $this->pdf('surat.pdf')])->assertRedirect();
        $this->assertSame('ditetapkan', $usulan->fresh()->status_key);
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertSee('data-action="download-surat"', false);
        $this->actingAs($pegawai)->get("/files/surat/{$usulan->id}")->assertOk()->assertDownload('surat-usulan-199001012020011001.pdf');
        $this->actingAs($pegawai)->get("/files/sertifikat/{$usulan->id}")->assertForbidden();

        // 6. Sertifikat diunggah -> selesai; Pegawai mengunduh sertifikat
        $this->actingAs($timJf)->post("/monitoring/{$usulan->id}/status", ['aksi' => 'selesaikan', 'sertifikat' => $this->pdf('sertifikat.pdf')])->assertRedirect();
        $this->assertSame('selesai', $usulan->fresh()->status_key);
        $this->actingAs($pegawai)->get('/pelacakan-status')->assertSee('data-action="download-sertifikat"', false);
        $this->actingAs($pegawai)->get("/files/sertifikat/{$usulan->id}")->assertOk()->assertDownload('sertifikat-ukom-199001012020011001.pdf');

        // Riwayat: 6 perubahan status tercatat
        $this->assertSame(
            [[null, 'verifikasi'], ['verifikasi', 'perbaikan'], ['perbaikan', 'verifikasi'], ['verifikasi', 'disetujui'], ['disetujui', 'ditetapkan'], ['ditetapkan', 'selesai']],
            $usulan->statusLogs()->get()->map(fn ($l) => [$l->from_status, $l->to_status])->all()
        );
    }
}
