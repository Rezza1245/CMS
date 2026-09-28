<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // Rate limiter anti brute-force login (maksimal 5 percobaan per menit per kombinasi IP + Email)
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            return Limit::perMinute(5)->by($request->ip() . '|' . $email)->response(function (Request $request, array $headers) {
                return response()->view('errors.429', [
                    'message' => 'Terlalu banyak percobaan masuk. Demi keamanan sistem, silakan tunggu 1 menit sebelum mencoba kembali.',
                ], 429, $headers);
            });
        });

        // Rate limiter pengunjung publik website dinas (120 permintaan per menit per IP)
        RateLimiter::for('public-site', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Rate limiter navigasi dashboard CMS (120 permintaan per menit per pengguna)
        RateLimiter::for('cms-read', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiter operasi penulisan/mutasi konten & unggahan berkas CMS (40 permintaan per menit per pengguna)
        RateLimiter::for('cms-write', function (Request $request) {
            return Limit::perMinute(40)->by($request->user()?->id ?: $request->ip());
        });
    }
}
