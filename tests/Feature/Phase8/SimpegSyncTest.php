<?php

namespace Tests\Feature\Phase8;

use App\Contracts\SimpegDirectory;
use App\Models\FormUsulan;
use App\Models\User;
use App\Sso\SsoIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * FR-PU-1: Sinkron data dengan SIMPEG/SIASN pada Pengajuan Usulan.
 */
class SimpegSyncTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_sync_success_fills_nama_nip_jabatan_and_unit_kerja(): void
    {
        // Pegawai Pusat di direktori fiktif, agar tetap boleh membuka Pengajuan setelah sinkron (FR-SATKER-6)
        $pegawai = User::factory()->pegawai()->create([
            'nip' => '199505152020121002',
            'name' => 'Nama Lama',
            'jabatan' => null,
            'unit_kerja' => 'Unit Lama',
        ]);

        $this->actingAs($pegawai)->post('/pengajuan/sinkron')
            ->assertRedirect(route('pengajuan'))
            ->assertSessionHas('success');

        $fresh = $pegawai->fresh();
        $this->assertSame('Budi Pratama, S.Tr.Stat.', $fresh->name);
        $this->assertSame('199505152020121002', $fresh->nip, 'NIP tidak berubah');
        $this->assertSame('Statistisi Ahli Pertama', $fresh->jabatan);
        $this->assertSame('BPS RI Pusat', $fresh->unit_kerja);

        $this->actingAs($fresh)->get('/pengajuan')
            ->assertSee('value="Budi Pratama, S.Tr.Stat."', false)
            ->assertSee('value="Statistisi Ahli Pertama"', false)
            ->assertSee('value="BPS RI Pusat"', false)
            ->assertSee('data-action="sinkron-simpeg"', false);
    }

    public function test_unknown_nip_shows_warning_and_form_still_works(): void
    {
        Storage::fake('local');
        $pegawai = User::factory()->pegawai()->create(['nip' => '199912312024011999', 'name' => 'Tetap Sama']);

        $this->actingAs($pegawai)->post('/pengajuan/sinkron')
            ->assertRedirect(route('pengajuan'))
            ->assertSessionHas('warning');

        $this->assertSame('Tetap Sama', $pegawai->fresh()->name);
        $this->actingAs($pegawai)->get('/pengajuan')->assertSee('data-section="sinkron-warning"', false);

        // form tetap dapat dipakai dengan data dari users
        $this->actingAs($pegawai)->post('/pengajuan', $this->dataUsulan(['aksi' => 'kirim', 'berkas' => $this->allBerkas()]))
            ->assertRedirect(route('pelacakan-status'));
        $usulan = FormUsulan::sole();
        $this->assertSame('verifikasi', $usulan->status_key);
        $this->assertSame('Tetap Sama', $usulan->nama_lengkap);
    }

    public function test_service_failure_shows_warning(): void
    {
        $this->app->bind(SimpegDirectory::class, fn () => new class implements SimpegDirectory
        {
            public function findByNip(string $nip): ?SsoIdentity
            {
                throw new RuntimeException('timeout');
            }
        });
        $pegawai = User::factory()->pegawai()->create(['name' => 'Tetap']);

        $this->actingAs($pegawai)->post('/pengajuan/sinkron')
            ->assertSessionHas('warning', 'Sinkron SIMPEG/SIASN gagal. Data dari akun Anda tetap dapat dipakai.');
        $this->assertSame('Tetap', $pegawai->fresh()->name);
    }

    public function test_only_pegawai_can_sync(): void
    {
        $this->actingAs(User::factory()->timJf()->create())->post('/pengajuan/sinkron')->assertForbidden();
    }

    public function test_guest_is_redirected(): void
    {
        $this->post('/pengajuan/sinkron')->assertRedirect(route('login'));
    }
}
