<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

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
        // Ép buộc dùng HTTPS trên môi trường production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Cấu hình Rate Limiter cho các API chung (60 request/phút)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Cấu hình Rate Limiter cho các API nhạy cảm (5 request/phút)
        RateLimiter::for('auth_limiter', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
