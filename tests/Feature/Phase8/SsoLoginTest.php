<?php

namespace Tests\Feature\Phase8;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * FR-AUTH-1, FR-AUTH-2, FR-AUTH-3 dan FR-USR-3 (alur SSO lokal dengan driver fake).
 */
class SsoLoginTest extends TestCase
{
    use RefreshDatabase;

    private function ssoLogin(string $nip, ?string $state = null)
    {
        $this->get('/auth/sso/redirect')->assertOk()->assertSee('data-section="sso-fake"', false);
        $state ??= session('sso_state');

        return $this->get('/auth/sso/callback?'.http_build_query(['state' => $state, 'nip' => $nip]));
    }

    // FR-AUTH-1
    public function test_known_nip_logs_in_and_is_redirected_by_role(): void
    {
        $user = User::factory()->timJf()->create(['nip' => '198501012010011001']);

        $this->ssoLogin('198501012010011001')->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('fake-sso-0001', $user->fresh()->sso_id, 'sso_id ditautkan dari direktori');
    }

    // FR-AUTH-1
    public function test_unknown_nip_is_refused_and_no_account_is_created(): void
    {
        $before = User::count();

        $this->ssoLogin('200001012024011001')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['nip' => 'NIP 200001012024011001 belum terdaftar di SIJABFU. Hubungi Super Admin.']);

        $this->assertGuest();
        $this->assertSame($before, User::count());
    }

    public function test_inactive_user_is_refused(): void
    {
        User::factory()->pegawai()->create(['nip' => '199505152020121002', 'is_active' => false]);

        $this->ssoLogin('199505152020121002')->assertRedirect(route('login'))->assertSessionHasErrors('nip');
        $this->assertGuest();
    }

    public function test_wrong_or_missing_state_is_refused(): void
    {
        User::factory()->pegawai()->create(['nip' => '199505152020121002']);

        $this->ssoLogin('199505152020121002', 'state-palsu')->assertRedirect(route('login'))->assertSessionHasErrors('nip');
        $this->assertGuest();

        $this->get('/auth/sso/callback?nip=199505152020121002')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_fake_driver_is_refused_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('/auth/sso/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['nip' => 'Driver SSO/SIMPEG "fake" tidak diizinkan di production.']);

        $this->app['env'] = 'testing';
    }

    public function test_unconfigured_driver_shows_clear_error(): void
    {
        config(['sijabfu.sso.driver' => 'oidc']);

        $this->get('/auth/sso/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['nip' => 'Driver "oidc" belum dikonfigurasi.']);
    }

    // FR-AUTH-2
    public function test_views_contain_no_hardcoded_credentials_and_sso_button_uses_adapter(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $content = $file->getContents();
            $this->assertDoesNotMatchRegularExpression('/\b\d{18}\b/', $content, 'NIP hardcoded di '.$file->getRelativePathname());
            $this->assertStringNotContainsString('fillCredentials', $content, $file->getRelativePathname());
            // nilai password demo seperti fillCredentials('...', 'password'); bukan pwd.type === 'password'
            $this->assertDoesNotMatchRegularExpression("/,\\s*'password'\\s*\\)/", $content, $file->getRelativePathname());
        }

        $this->get('/login')->assertOk()
            ->assertSee('href="'.route('sso.redirect').'"', false)
            ->assertDontSee('handleSSO', false)
            ->assertDontSee('Akun Demo');
    }

    // FR-AUTH-3
    public function test_local_login_disabled_by_flag(): void
    {
        User::factory()->pegawai()->create(['nip' => '199505152020121002']);
        config(['sijabfu.auth_local_enabled' => false]);

        $this->get('/login')->assertOk()->assertDontSee('id="loginForm"', false)->assertSee('LOGIN SSO');

        $this->post('/login', ['nip' => '199505152020121002', 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('nip');
        $this->assertGuest();

        // SSO tetap berfungsi
        $this->ssoLogin('199505152020121002')->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    // FR-AUTH-3
    public function test_local_login_enabled_by_flag(): void
    {
        User::factory()->pegawai()->create(['nip' => '199505152020121002']);
        config(['sijabfu.auth_local_enabled' => true]);

        $this->get('/login')->assertSee('id="loginForm"', false);
        $this->post('/login', ['nip' => '199505152020121002', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_local_login_refuses_inactive_user(): void
    {
        User::factory()->pegawai()->create(['nip' => '199505152020121002', 'is_active' => false]);

        $this->post('/login', ['nip' => '199505152020121002', 'password' => 'password'])
            ->assertSessionHasErrors(['nip' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']);
        $this->assertGuest();
    }

    // FR-USR-3: seluruh alur berjalan lokal dengan SSO_DRIVER=fake
    public function test_full_local_flow_with_fake_driver(): void
    {
        $this->assertSame('fake', config('sijabfu.sso.driver'));
        User::factory()->superAdmin()->create(['nip' => '198001012005011001']);

        // 1. Super Admin masuk lewat SSO
        $this->ssoLogin('198001012005011001')->assertRedirect(route('dashboard'));

        // 2. Sinkronisasi Data SSO membuat user dari direktori fiktif
        $this->post('/admin/sso-sync')->assertRedirect(route('admin.sso-sync'));
        $this->assertTrue(User::where('nip', '200001012024011001')->exists());
        $this->post('/logout');
        $this->assertGuest();

        // 3. User baru masuk lewat SSO dan menjadi Pegawai
        $this->ssoLogin('200001012024011001')->assertRedirect(route('dashboard'));
        $this->assertSame('pegawai', auth()->user()->role);
        $this->get('/dashboard')->assertOk()->assertSee('Contoh Pegawai Fiktif Satu');
    }
}
