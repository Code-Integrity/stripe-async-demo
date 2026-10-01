<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
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


        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('stripe-checkout', function (Request $request) {

            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'error' => 'Too many checkout requests. Anti-DoS protection activated.'
                ], 429);
            });
        });
    }
}
