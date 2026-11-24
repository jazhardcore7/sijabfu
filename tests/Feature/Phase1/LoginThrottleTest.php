<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FR-AUTH-4: login dibatasi 5 percobaan per menit per NIP dan IP.
 */
class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sixth_attempt_within_a_minute_returns_429(): void
    {
        User::factory()->pegawai()->create(['nip' => '199001012020011001']);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['nip' => '199001012020011001', 'password' => 'salah'])
                ->assertStatus(302);
        }

        $this->post('/login', ['nip' => '199001012020011001', 'password' => 'salah'])
            ->assertStatus(429);
    }

    public function test_limit_is_per_nip(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['nip' => '111', 'password' => 'salah']);
        }

        $this->post('/login', ['nip' => '222', 'password' => 'salah'])->assertStatus(302);
    }

    public function test_valid_login_still_works_and_redirects_by_role(): void
    {
        User::factory()->timJf()->create(['nip' => '198501012010011001']);

        $this->post('/login', ['nip' => '198501012010011001', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }
}
