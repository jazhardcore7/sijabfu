<?php

namespace Tests\Feature\Phase9;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase3\UsulanFixtures;
use Tests\TestCase;

/**
 * Regresi temuan pemeriksaan browser (setelah Fase 9):
 * halaman melebar ke samping, kontras tema gelap, kotak tautan lama tanpa gaya,
 * dan panel dashboard Pegawai yang tertutup hero.
 */
class LayoutRegressionTest extends TestCase
{
    use RefreshDatabase;
    use UsulanFixtures;

    public function test_main_content_can_shrink_and_navbar_adapts_on_small_screens(): void
    {
        $layout = File::get(resource_path('views/layouts/app.blade.php'));

        $this->assertMatchesRegularExpression('/\.main-content\s*\{[^}]*min-width:\s*0;/s', $layout);
        $this->assertMatchesRegularExpression('/@media \(max-width: 640px\)\s*\{[^@]*\.profile-name\s*\{\s*display:\s*none;/s', $layout);

        $html = $this->actingAs(User::factory()->pegawai()->create(['name' => 'Nama Panjang Sekali']))->get('/regulasi')->getContent();
        $this->assertStringContainsString('<span class="profile-name">Nama Panjang Sekali</span>', $html);
    }

    public function test_no_view_uses_fixed_navy_for_text(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![\w-])color\s*:\s*(var\(--bps-dark\)|var\(--bps-navy\)|#003b73)/i',
                $file->getContents(),
                'Teks biru tua tetap di '.$file->getRelativePathname()
            );
        }
    }

    public function test_theme_defines_heading_and_hero_tokens_for_both_modes(): void
    {
        $theme = File::get(resource_path('views/partials/theme.blade.php'));
        [$light, $dark] = explode('[data-theme="dark"] {', $theme, 2);

        foreach (['--c-heading', '--c-hero-bg', '--c-card-muted-bg'] as $token) {
            $this->assertStringContainsString($token.':', $light, "{$token} (terang)");
            $this->assertStringContainsString($token.':', $dark, "{$token} (gelap)");
        }

        foreach (['auth/login.blade.php', 'admin/dashboard.blade.php'] as $view) {
            $content = File::get(resource_path('views/'.$view));
            $this->assertStringContainsString('background: var(--c-hero-bg);', $content, $view);
            $this->assertStringNotContainsString('#E0F2FE 0%, #EFF6FF', $content, $view);
        }
        $this->assertStringContainsString('background: var(--c-card-muted-bg);', File::get(resource_path('views/auth/login.blade.php')));
    }

    public function test_legacy_links_box_is_styled_on_the_verifikasi_page(): void
    {
        Storage::fake('local');
        $usulan = $this->usulanAt(User::factory()->pegawai()->create(), 'verifikasi', [
            'link_berkas' => 'https://laci.example.test/berkas',
        ]);

        $html = $this->actingAs(User::factory()->timJf()->create())->get("/verifikasi/{$usulan->id}")->assertOk()->getContent();

        $this->assertStringContainsString('data-section="legacy-links"', $html);
        $this->assertStringContainsString('.legacy-card {', $html, 'gaya kotak tautan lama ikut dimuat');
        $this->assertSame(1, substr_count($html, '.legacy-card {'), 'gaya hanya dimuat sekali');
    }

    public function test_pegawai_dashboard_panel_is_shown_before_the_hero(): void
    {
        $html = $this->actingAs(User::factory()->pegawai()->create())->get('/dashboard')->assertOk()->getContent();

        $panel = strpos($html, 'data-section="dashboard-pegawai"');
        $hero = strpos($html, '<section class="hero-section">');
        $this->assertNotFalse($panel);
        $this->assertNotFalse($hero);
        $this->assertLessThan($hero, $panel);
    }
}
