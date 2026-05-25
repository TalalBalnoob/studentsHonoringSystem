<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        //
    }

    public function boot(): void {
        // Rate limiter for public student form submissions
        RateLimiter::for('student-form', function (Request $request) {
            return Limit::perHour(10)->by($request->ip());
        });

        // Rate limiter for login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->input('username') ?: $request->ip());
        });

        // Rate limiter for authenticated API endpoints
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(20)->by($request->ip());
        });
    }
}
