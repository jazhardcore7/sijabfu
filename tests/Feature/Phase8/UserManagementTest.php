<?php

namespace Tests\Feature\Phase8;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-USR-1: Daftar User (Super Admin).
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Satu']);
    }

    private function update(User $actor, User $target, string $role, bool $active)
    {
        return $this->actingAs($actor)->from('/admin/users')
            ->post("/admin/users/{$target->id}", ['role' => $role, 'is_active' => $active ? '1' : '0']);
    }

    public function test_search_role_filter_and_pagination(): void
    {
        User::factory()->pegawai()->create(['name' => 'Andi Cari', 'nip' => '111222333444555666']);
        User::factory()->timJf()->create(['name' => 'Andi Tim']);
        // Nama dan email tetap agar pencarian "Andi" tidak pernah cocok dengan data acak faker
        User::factory()->pegawai()->count(20)->sequence(fn ($seq) => [
            'name' => 'Pegawai Pengisi '.($seq->index + 1),
            'email' => 'pengisi'.($seq->index + 1).'@example.test',
        ])->create();

        $this->actingAs($this->superAdmin)->get('/admin/users?q=Andi')
            ->assertSee('Andi Cari')->assertSee('Andi Tim')->assertSee('data-total="2"', false);
        $this->actingAs($this->superAdmin)->get('/admin/users?q=111222333')->assertSee('Andi Cari')->assertDontSee('Andi Tim');
        $this->actingAs($this->superAdmin)->get('/admin/users?q=Andi&role=tim_jf')
            ->assertSee('Andi Tim')->assertDontSee('Andi Cari');

        $page1 = $this->actingAs($this->superAdmin)->get('/admin/users')->getContent();
        $this->assertSame(15, substr_count($page1, 'data-user="'));
        $this->assertSame(23, (int) preg_replace('/\D/', '', preg_match('/data-total="(\d+)"/', $page1, $m) ? $m[1] : '0'));
    }

    public function test_change_role_and_activate_deactivate(): void
    {
        $pegawai = User::factory()->pegawai()->create();

        $this->update($this->superAdmin, $pegawai, 'tim_jf', true)->assertRedirect(route('admin.users'));
        $this->assertSame('tim_jf', $pegawai->fresh()->role);

        $this->update($this->superAdmin, $pegawai, 'tim_jf', false);
        $this->assertFalse($pegawai->fresh()->is_active);

        $this->update($this->superAdmin, $pegawai, 'pegawai', true);
        $this->assertSame('pegawai', $pegawai->fresh()->role);
        $this->assertTrue($pegawai->fresh()->is_active);
    }

    public function test_super_admin_cannot_demote_or_deactivate_self(): void
    {
        User::factory()->superAdmin()->create(); // bukan Super Admin terakhir

        $this->update($this->superAdmin, $this->superAdmin, 'tim_jf', true)->assertSessionHas('error');
        $this->update($this->superAdmin, $this->superAdmin, 'super_admin', false)->assertSessionHas('error');

        $this->assertSame('super_admin', $this->superAdmin->fresh()->role);
        $this->assertTrue($this->superAdmin->fresh()->is_active);
    }

    public function test_last_active_super_admin_is_protected(): void
    {
        // Dua Super Admin aktif: yang lain boleh diturunkan
        $other = User::factory()->superAdmin()->create();
        $this->update($this->superAdmin, $other, 'tim_jf', true)->assertSessionMissing('error');
        $this->assertSame('tim_jf', $other->fresh()->role);
        $this->assertSame(1, User::where('role', 'super_admin')->where('is_active', true)->count());

        // Super Admin yang sudah nonaktif tidak dihitung, jadi boleh diturunkan
        $inactive = User::factory()->superAdmin()->create(['is_active' => false]);
        $this->update($this->superAdmin, $inactive, 'pegawai', false)->assertSessionMissing('error');
        $this->assertSame('pegawai', $inactive->fresh()->role);

        // Super Admin aktif terakhir tidak dapat diturunkan atau dinonaktifkan
        $this->update($this->superAdmin, $this->superAdmin, 'pegawai', true)->assertSessionHas('error');
        $this->update($this->superAdmin, $this->superAdmin, 'super_admin', false)->assertSessionHas('error');
        $this->assertSame('super_admin', $this->superAdmin->fresh()->role);
        $this->assertTrue($this->superAdmin->fresh()->is_active);
    }

    public function test_only_super_admin_can_manage_users(): void
    {
        $target = User::factory()->pegawai()->create();

        foreach ([User::factory()->timJf()->create(), User::factory()->pegawai()->create()] as $user) {
            $this->actingAs($user)->get('/admin/users')->assertForbidden();
            $this->actingAs($user)->post("/admin/users/{$target->id}", ['role' => 'super_admin', 'is_active' => '1'])->assertForbidden();
        }

        $this->assertSame('pegawai', $target->fresh()->role);
    }

    public function test_invalid_role_is_rejected(): void
    {
        $target = User::factory()->pegawai()->create();

        $this->update($this->superAdmin, $target, 'admin', true)->assertSessionHasErrors('role');
        $this->assertSame('pegawai', $target->fresh()->role);
    }

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $pegawai = User::factory()->pegawai()->create(['nip' => '199505152020121002']);

        $this->post('/login', ['nip' => '199505152020121002', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk();

        User::whereKey($pegawai->id)->update(['is_active' => false]);

        // request baru memuat ulang user dari sesi (di test, guard menyimpan user antar request)
        $this->app['auth']->forgetGuards();

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
