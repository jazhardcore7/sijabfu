<?php

namespace Tests\Feature\Satker;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-SATKER-3: Daftar User mengisi role dan satuan kerja, dengan validasi dan filter.
 */
class DaftarUserSatkerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Satu']);
    }

    private function save(User $target, array $data)
    {
        return $this->actingAs($this->superAdmin)->from('/admin/users')
            ->post("/admin/users/{$target->id}", $data + ['is_active' => '1']);
    }

    public function test_assign_admin_satker_with_provinsi(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->save($user, ['role' => 'admin_satker', 'satker_tipe' => 'provinsi', 'satker_kode' => '3200', 'provinsi_kode' => '32'])
            ->assertRedirect()->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(['admin_satker', 'provinsi', '3200', '32'], [$user->role, $user->satker_tipe, $user->satker_kode, $user->provinsi_kode]);
    }

    public function test_admin_satker_without_provinsi_is_rejected(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->save($user, ['role' => 'admin_satker', 'satker_tipe' => 'provinsi', 'satker_kode' => '', 'provinsi_kode' => ''])
            ->assertSessionHas('error', 'Admin Satuan Kerja Provinsi wajib memiliki provinsi.');
        $this->save($user, ['role' => 'admin_satker', 'satker_tipe' => 'pusat', 'provinsi_kode' => '32'])
            ->assertSessionHas('error');

        $this->assertSame('pegawai', $user->fresh()->role);
    }

    public function test_daerah_requires_provinsi_and_pusat_clears_it(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'kabkota', 'satker_kode' => '', 'provinsi_kode' => ''])
            ->assertSessionHas('error', 'Satuan kerja Provinsi atau Kabupaten/Kota wajib memiliki provinsi.');
        $this->assertSame('pusat', $user->fresh()->satker_tipe);

        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'kabkota', 'satker_kode' => '3273', 'provinsi_kode' => '32'])->assertSessionHas('success');
        $this->assertSame('kabkota', $user->fresh()->satker_tipe);

        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'pusat', 'satker_kode' => '0000', 'provinsi_kode' => '32'])->assertSessionHas('success');
        $this->assertSame(['pusat', null], [$user->fresh()->satker_tipe, $user->fresh()->provinsi_kode]);
    }

    public function test_kode_must_match_provinsi_and_format(): void
    {
        $user = User::factory()->pegawai()->create();

        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'kabkota', 'satker_kode' => '3373', 'provinsi_kode' => '32'])
            ->assertSessionHas('error', 'Kode satker 3373 tidak berada di provinsi yang dipilih.');
        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'kabkota', 'satker_kode' => '32A3', 'provinsi_kode' => '32'])
            ->assertSessionHasErrors('satker_kode');
        $this->save($user, ['role' => 'pegawai', 'satker_tipe' => 'kabkota', 'provinsi_kode' => '99'])
            ->assertSessionHasErrors('provinsi_kode');
    }

    public function test_post_without_satker_fields_keeps_stored_values(): void
    {
        $user = User::factory()->pegawai()->kabkota('32', '73')->create();

        $this->save($user, ['role' => 'pegawai', 'is_active' => '0'])->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->is_active);
        $this->assertSame(['kabkota', '3273', '32'], [$user->satker_tipe, $user->satker_kode, $user->provinsi_kode]);
    }

    public function test_filters_and_columns(): void
    {
        User::factory()->pegawai()->tanpaSatker()->create(['name' => 'Tanpa Satker Uji']);
        User::factory()->pegawai()->provinsi('32')->create(['name' => 'Jabar Uji']);
        User::factory()->pegawai()->provinsi('33')->create(['name' => 'Jateng Uji']);

        $this->actingAs($this->superAdmin)->get('/admin/users?satker=kosong')->assertOk()
            ->assertSee('Tanpa Satker Uji')->assertDontSee('Jabar Uji')->assertSee('data-total="1"', false);
        $this->actingAs($this->superAdmin)->get('/admin/users?satker=provinsi&provinsi=32')
            ->assertSee('Jabar Uji')->assertDontSee('Jateng Uji')->assertDontSee('Tanpa Satker Uji');

        $this->actingAs($this->superAdmin)->get('/admin/users')
            ->assertSee('data-section="satker-missing"', false)
            ->assertSee('<option value="admin_satker"', false)
            ->assertSee('Admin Satuan Kerja Provinsi')
            ->assertSee('Jawa Barat');
    }
}
