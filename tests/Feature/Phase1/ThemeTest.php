<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * FR-DASH-6: tema dark/light lewat variabel CSS pada [data-theme],
 * default prefers-color-scheme, pilihan disimpan di localStorage.
 *
 * Catatan: persistensi setelah reload terjadi di browser (localStorage) dan
 * tidak dapat dijalankan di PHPUnit; test ini memeriksa kode yang membaca dan
 * menulis localStorage, serta pemindaian view untuk warna latar netral hardcoded.
 */
class ThemeTest extends TestCase
{
    use RefreshDatabase;

    private const APP_VIEWS = [
        'layouts/app.blade.php',
        'landing.blade.php',
        'auth/login.blade.php',
        'admin/dashboard.blade.php',
        'admin/monitoring.blade.php',
        'verifikasi/index.blade.php',
        'verifikasi/show.blade.php',
        'verifikasi/_styles.blade.php',
        'modules/alur-layanan.blade.php',
        'modules/form-usulan.blade.php',
        'modules/progress-berkas.blade.php',
        'modules/regulasi.blade.php',
        'modules/tim-kerja.blade.php',
    ];

    public function test_authenticated_page_has_theme_script_tokens_and_toggle(): void
    {
        $html = $this->actingAs(User::factory()->pegawai()->create())->get('/regulasi')->assertOk()->getContent();

        $this->assertStringContainsString("localStorage.getItem('sijabfu-theme')", $html);
        $this->assertStringContainsString("localStorage.setItem('sijabfu-theme'", $html);
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);
        $this->assertStringContainsString('[data-theme="dark"]', $html);
        $this->assertStringContainsString('onclick="toggleTheme()"', $html);
    }

    public function test_login_page_applies_saved_theme(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString("localStorage.getItem('sijabfu-theme')", $html);
        $this->assertStringContainsString('[data-theme="dark"]', $html);
    }

    public function test_no_app_view_uses_hardcoded_neutral_background_or_text_color(): void
    {
        $background = '/background(?:-color)?\s*:\s*(?:white|#fff\b|#ffffff|#f8fafc|#f1f5f9|#f3f4f6|#e2e8f0|#f0f4f8)/i';
        $text = '/(?<![\w-])color\s*:\s*#(?:1e293b|0f172a|111827|334155|374151|475569|64748b|6b7280|94a3b8|9ca3af)\b/i';

        foreach (self::APP_VIEWS as $view) {
            $content = File::get(resource_path('views/' . $view));

            $this->assertDoesNotMatchRegularExpression($background, $content, "Latar netral hardcoded di {$view}");
            $this->assertDoesNotMatchRegularExpression($text, $content, "Warna teks netral hardcoded di {$view}");
        }
    }
}
