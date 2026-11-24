<?php

namespace Tests\Feature\Phase5;

use App\Models\Regulasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regulasi (PRD Revisi 5.7): FR-REG-1, FR-REG-2, FR-REG-3.
 */
class RegulasiTest extends TestCase
{
    use RefreshDatabase;

    private User $pegawai;

    private User $timJf;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->pegawai = User::factory()->pegawai()->create();
        $this->timJf = User::factory()->timJf()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    private function pdf(string $name = 'peraturan.pdf', int $kb = 200): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, 'application/pdf');
    }

    private function withFile(array $attrs = []): Regulasi
    {
        Storage::disk('local')->put('regulasi/uji.pdf', '%PDF regulasi');

        return Regulasi::create($attrs + [
            'judul' => 'Pedoman Uji Regulasi',
            'kategori' => Regulasi::KATEGORI_BKN,
            'nomor' => 'Perka BKN No. 99 Tahun 2026',
            'tahun' => 2026,
            'file_path' => 'regulasi/uji.pdf',
        ]);
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'judul' => 'Peraturan Baru JF',
            'kategori' => Regulasi::KATEGORI_PERKA_BPS,
            'nomor' => 'Perka BPS No. 10 Tahun 2026',
            'tahun' => 2026,
            'file' => $this->pdf(),
        ];
    }

    public static function roles(): array
    {
        return ['pegawai' => ['pegawai'], 'tim_jf' => ['timJf'], 'super_admin' => ['superAdmin']];
    }

    // FR-REG-1
    #[DataProvider('roles')]
    public function test_every_role_sees_list_grouped_by_kategori(string $who): void
    {
        $response = $this->actingAs($this->{$who})->get('/regulasi')->assertOk();

        $response->assertSeeInOrder(['Peraturan Kepala BPS', 'Peraturan MenPAN-RB', 'Peraturan BKN']);
        $this->assertSame(3, substr_count($response->getContent(), 'class="regulasi-group'));
        $response->assertSee('Perka BPS No. 4 Tahun 2020');
        $response->assertSee('PermenPAN-RB No. 13 Tahun 2019');
        $response->assertSee('Perka BKN No. 6 Tahun 2023');
    }

    // FR-REG-1
    public function test_search_by_judul_or_nomor(): void
    {
        $this->actingAs($this->pegawai)->get('/regulasi?q=Pranata%20Komputer')
            ->assertSee('PermenPAN-RB No. 1 Tahun 2023')
            ->assertDontSee('Perka BPS No. 4 Tahun 2020');

        $this->actingAs($this->pegawai)->get('/regulasi?q=BKN%20No.%2011')
            ->assertSee('Perka BKN No. 11 Tahun 2022')
            ->assertDontSee('Perka BKN No. 6 Tahun 2023');
    }

    // FR-REG-1
    #[DataProvider('roles')]
    public function test_preview_and_download_work_for_record_with_file(string $who): void
    {
        $reg = $this->withFile();

        $this->actingAs($this->{$who})->get('/regulasi')
            ->assertSee('href="'.route('regulasi.preview', $reg).'"', false)
            ->assertSee('href="'.route('regulasi.download', $reg).'"', false);

        $preview = $this->actingAs($this->{$who})->get("/regulasi/{$reg->id}/preview")->assertOk();
        $this->assertStringStartsWith('inline', $preview->headers->get('Content-Disposition'));

        $this->actingAs($this->{$who})->get("/regulasi/{$reg->id}/download")
            ->assertOk()
            ->assertDownload('Perka-BKN-No-99-Tahun-2026.pdf');
    }

    public function test_guest_is_redirected_from_regulasi(): void
    {
        $reg = $this->withFile();

        $this->get('/regulasi')->assertRedirect(route('login'));
        $this->get("/regulasi/{$reg->id}/download")->assertRedirect(route('login'));
    }

    // FR-REG-2
    #[DataProvider('staff')]
    public function test_staff_can_upload_edit_and_soft_delete(string $who): void
    {
        $staff = $this->{$who};

        // upload
        $this->actingAs($staff)->post('/admin/regulasi', $this->payload())->assertRedirect(route('admin.regulasi.index'));
        $reg = Regulasi::where('nomor', 'Perka BPS No. 10 Tahun 2026')->sole();
        $this->assertSame($staff->id, (int) $reg->uploaded_by);
        $this->assertStringStartsWith('regulasi/', $reg->file_path);
        Storage::disk('local')->assertExists($reg->file_path);

        // edit tanpa ganti file
        $this->actingAs($staff)->put("/admin/regulasi/{$reg->id}", $this->payload(['judul' => 'Judul Diubah', 'file' => null]))
            ->assertRedirect(route('admin.regulasi.index'));
        $this->assertSame('Judul Diubah', $reg->fresh()->judul);
        $oldPath = $reg->fresh()->file_path;
        Storage::disk('local')->assertExists($oldPath);

        // edit dengan ganti file: file lama dihapus
        $this->actingAs($staff)->put("/admin/regulasi/{$reg->id}", $this->payload(['file' => $this->pdf('baru.pdf')]));
        $newPath = $reg->fresh()->file_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);

        // hapus (soft delete)
        $this->actingAs($staff)->delete("/admin/regulasi/{$reg->id}")->assertRedirect(route('admin.regulasi.index'));
        $this->assertSoftDeleted('regulasi', ['id' => $reg->id]);
        $this->actingAs($this->pegawai)->get('/regulasi')->assertDontSee('Perka BPS No. 10 Tahun 2026');
        $this->actingAs($staff)->get('/admin/regulasi')->assertDontSee('Perka BPS No. 10 Tahun 2026');
        $this->actingAs($this->pegawai)->get("/regulasi/{$reg->id}/download")->assertNotFound();
    }

    public static function staff(): array
    {
        return ['tim_jf' => ['timJf'], 'super_admin' => ['superAdmin']];
    }

    // FR-REG-2
    public function test_invalid_upload_is_rejected(): void
    {
        // [field yang error, override payload]
        $cases = [
            'bukan pdf' => ['file', ['file' => UploadedFile::fake()->create('peraturan.docx', 100, 'application/msword')]],
            'lebih 10 MB' => ['file', ['file' => $this->pdf('besar.pdf', 10241)]],
            'tanpa file' => ['file', ['file' => null]],
            'kategori asing' => ['kategori', ['kategori' => 'Peraturan Lain']],
            'tahun salah' => ['tahun', ['tahun' => 1800]],
            'judul kosong' => ['judul', ['judul' => '']],
        ];

        foreach ($cases as $name => [$field, $override]) {
            $before = Regulasi::count();
            $this->actingAs($this->timJf)->from('/admin/regulasi')
                ->post('/admin/regulasi', $this->payload($override))
                ->assertSessionHasErrors($field);
            $this->assertSame($before, Regulasi::count(), "kasus {$name}");
        }
    }

    // FR-REG-2
    public function test_pegawai_gets_403_on_admin_actions(): void
    {
        $reg = $this->withFile();

        $this->actingAs($this->pegawai)->get('/admin/regulasi')->assertForbidden();
        $this->actingAs($this->pegawai)->post('/admin/regulasi', $this->payload())->assertForbidden();
        $this->actingAs($this->pegawai)->put("/admin/regulasi/{$reg->id}", $this->payload(['file' => null]))->assertForbidden();
        $this->actingAs($this->pegawai)->delete("/admin/regulasi/{$reg->id}")->assertForbidden();

        $this->assertNull($reg->fresh()->deleted_at);
        $this->assertSame('Pedoman Uji Regulasi', $reg->fresh()->judul);
        $this->assertSame(0, Regulasi::where('nomor', 'Perka BPS No. 10 Tahun 2026')->count());
    }

    // FR-REG-3
    public function test_seeded_rows_exist_without_file_and_are_disabled(): void
    {
        $seeded = Regulasi::whereNull('file_path')->get();
        $this->assertCount(8, $seeded);
        $this->assertEqualsCanonicalizing(
            [3, 3, 2],
            [$seeded->where('kategori', 'Peraturan Kepala BPS')->count(), $seeded->where('kategori', 'Peraturan MenPAN-RB')->count(), $seeded->where('kategori', 'Peraturan BKN')->count()]
        );

        $html = $this->actingAs($this->pegawai)->get('/regulasi')->getContent();
        $this->assertSame(8, substr_count($html, 'data-section="file-belum-tersedia"'));
        $this->assertStringNotContainsString('data-action="download-regulasi"', $html);
        $this->assertStringNotContainsString('data-action="preview-regulasi"', $html);

        $first = $seeded->first();
        $this->actingAs($this->pegawai)->get("/regulasi/{$first->id}/preview")->assertNotFound();
        $this->actingAs($this->pegawai)->get("/regulasi/{$first->id}/download")->assertNotFound();
    }

    // FR-REG-3 (data migration)
    public function test_seed_migration_is_idempotent_and_down_only_removes_seeded_rows(): void
    {
        $migration = require database_path('migrations/2026_11_26_000001_seed_regulasi_awal.php');

        $migration->up();
        $this->assertSame(8, Regulasi::whereNull('file_path')->count(), 'tidak ada duplikat saat dijalankan ulang');

        // baris awal yang sudah diberi file dan baris baru tidak ikut terhapus
        Regulasi::where('nomor', 'Perka BPS No. 4 Tahun 2020')->update(['file_path' => 'regulasi/x.pdf', 'uploaded_by' => $this->timJf->id]);
        $own = $this->withFile();

        $migration->down();

        $this->assertSame(0, Regulasi::whereNull('file_path')->count());
        $this->assertTrue(Regulasi::where('nomor', 'Perka BPS No. 4 Tahun 2020')->exists());
        $this->assertTrue(Regulasi::whereKey($own->id)->exists());

        // up() pada database yang sudah punya sebagian nomor hanya menambah yang belum ada
        DB::table('regulasi')->where('nomor', 'Perka BKN No. 6 Tahun 2023')->delete();
        $migration->up();
        $this->assertSame(1, Regulasi::where('nomor', 'Perka BPS No. 4 Tahun 2020')->count());
        $this->assertSame(1, Regulasi::where('nomor', 'Perka BKN No. 6 Tahun 2023')->count());
    }

    public function test_staff_sees_kelola_link_and_pegawai_does_not(): void
    {
        $this->actingAs($this->timJf)->get('/regulasi')->assertSee('data-action="kelola-regulasi"', false);
        $this->actingAs($this->pegawai)->get('/regulasi')->assertDontSee('data-action="kelola-regulasi"', false);
    }
}
