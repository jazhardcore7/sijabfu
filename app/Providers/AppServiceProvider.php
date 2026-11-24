<?php

namespace App\Providers;

use App\Models\FormUsulan;
use App\Policies\FormUsulanPolicy;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(FormUsulan::class, FormUsulanPolicy::class);

        // FR-AUTH-4: maksimal 5 percobaan login per menit per NIP dan IP.
        RateLimiter::for('login', function (Request $request) {
            $nip = Str::lower(trim((string) $request->input('nip')));

            return Limit::perMinute(5)->by($nip . '|' . $request->ip());
        });
    }
}
