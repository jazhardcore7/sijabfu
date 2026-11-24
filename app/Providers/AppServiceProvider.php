<?php

namespace App\Providers;

use App\Contracts\SimpegDirectory;
use App\Contracts\SsoProvider;
use App\Models\ChatThread;
use App\Models\FormUsulan;
use App\Models\Regulasi;
use App\Models\UsulanBerkas;
use App\Policies\ChatThreadPolicy;
use App\Policies\FormUsulanPolicy;
use App\Policies\RegulasiPolicy;
use App\Policies\UsulanBerkasPolicy;
use App\Simpeg\FakeSimpegDirectory;
use App\Sso\FakeSsoProvider;
use App\Sso\UnconfiguredSsoProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Adapter SSO dan SIMPEG/SIASN dipilih dari config (FR-USR-3)
        $this->app->bind(SsoProvider::class, function () {
            $driver = (string) config('sijabfu.sso.driver');

            return $driver === 'fake'
                ? new FakeSsoProvider
                : new UnconfiguredSsoProvider($driver);
        });

        $this->app->bind(SimpegDirectory::class, function () {
            $driver = (string) config('sijabfu.simpeg.driver');

            return $driver === 'fake'
                ? new FakeSimpegDirectory
                : new UnconfiguredSsoProvider($driver);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(FormUsulan::class, FormUsulanPolicy::class);
        Gate::policy(UsulanBerkas::class, UsulanBerkasPolicy::class);
        Gate::policy(Regulasi::class, RegulasiPolicy::class);
        Gate::policy(ChatThread::class, ChatThreadPolicy::class);

        // FR-CHAT-4: maksimal 30 pesan per menit per user.
        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(30)->by('chat|'.($request->user()?->id ?? $request->ip()));
        });

        // FR-AUTH-4: maksimal 5 percobaan login per menit per NIP dan IP.
        RateLimiter::for('login', function (Request $request) {
            $nip = Str::lower(trim((string) $request->input('nip')));

            return Limit::perMinute(5)->by($nip.'|'.$request->ip());
        });

        // PRD bagian 8: throttle unggahan berkas usulan per user.
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(20)->by('uploads|'.($request->user()?->id ?? $request->ip()));
        });
    }
}
