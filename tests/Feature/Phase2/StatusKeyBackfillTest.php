<?php

namespace Tests\Feature\Phase2;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Backfill status_key dari status lama [ASSUMPTION PRD 11.4]:
 * 1, 2 -> verifikasi; 3 -> disetujui; 4 -> ditetapkan; 5 -> selesai.
 */
class StatusKeyBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_11_25_000003_backfill_form_usulan_status_key.php');
    }

    private function legacyRow(int $status): int
    {
        $user = User::factory()->pegawai()->create();

        return DB::table('form_usulan')->insertGetId([
            'user_id' => $user->id,
            'email' => $user->email,
            'jabatan_fungsional' => 'Statistisi',
            'jenis_usulan' => 'Inpassing',
            'nomor_surat' => 'X',
            'hal_surat' => 'X',
            'nama_lengkap' => $user->name,
            'nip' => $user->nip,
            'satuan_kerja' => 'BPS',
            'link_berkas' => 'https://example.test',
            'status' => $status,
            'status_key' => null,
        ]);
    }

    public function test_each_legacy_status_maps_to_its_key(): void
    {
        $expected = [1 => 'verifikasi', 2 => 'verifikasi', 3 => 'disetujui', 4 => 'ditetapkan', 5 => 'selesai'];
        $ids = [];
        foreach (array_keys($expected) as $status) {
            $ids[$status] = $this->legacyRow($status);
        }

        $this->migration()->up();

        foreach ($expected as $status => $key) {
            $row = DB::table('form_usulan')->find($ids[$status]);
            $this->assertSame($key, $row->status_key, "status lama {$status}");
            $this->assertSame($status, (int) $row->status, 'kolom status lama tidak berubah');
        }
    }

    public function test_existing_status_key_is_not_overwritten(): void
    {
        $id = $this->legacyRow(1);
        DB::table('form_usulan')->where('id', $id)->update(['status_key' => 'perbaikan']);

        $this->migration()->up();

        $this->assertSame('perbaikan', DB::table('form_usulan')->where('id', $id)->value('status_key'));
    }

    public function test_down_clears_status_key(): void
    {
        $id = $this->legacyRow(3);
        $this->migration()->up();

        $this->migration()->down();

        $this->assertNull(DB::table('form_usulan')->where('id', $id)->value('status_key'));
    }
}
