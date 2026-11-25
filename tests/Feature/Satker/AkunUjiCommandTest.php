<?php

namespace Tests\Feature\Satker;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Akun uji lokal: password acak baru untuk akun demo, ditulis ke file (bukan ke terminal atau kode).
 */
class AkunUjiCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = storage_path('framework/testing/akun-uji-'.uniqid().'.md');
    }

    protected function tearDown(): void
    {
        File::delete($this->path);
        parent::tearDown();
    }

    public function test_writes_one_row_per_demo_account_with_working_password(): void
    {
        $this->seed();
        $lain = User::factory()->pegawai()->create(['nip' => '777777777777777777']);
        $hashLain = $lain->password;

        $this->artisan('sijabfu:akun-uji', ['--path' => $this->path])
            ->expectsOutputToContain('8 akun diperbarui')
            ->assertSuccessful();

        $md = File::get($this->path);
        foreach (['Pegawai', 'Tim Pembinaan JF', 'Super Admin', 'Admin Satuan Kerja Provinsi'] as $label) {
            $this->assertStringContainsString('| '.$label.' |', $md);
        }

        preg_match_all('/\| `(\d+)` \| `([A-Za-z0-9]{16})` \|/', $md, $m, PREG_SET_ORDER);
        $this->assertCount(8, $m);
        foreach ($m as [, $nip, $password]) {
            $this->assertTrue(Hash::check($password, User::where('nip', $nip)->value('password')), $nip);
        }

        // Akun di luar direktori fiktif tidak disentuh
        $this->assertSame($hashLain, $lain->fresh()->password);
        $this->assertStringNotContainsString('777777777777777777', $md);
    }

    public function test_refused_outside_local_and_testing(): void
    {
        $this->seed();
        $this->app['env'] = 'production';
        try {
            $this->artisan('sijabfu:akun-uji', ['--path' => $this->path])->assertFailed();
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertFileDoesNotExist($this->path);
    }
}
