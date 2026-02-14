<?php

namespace App\Providers;

use App\Support\RateLimits\MailerRateLimit;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('mailer', fn (object $job): Limit => new MailerRateLimit()->handle($job));

        RateLimiter::for('marketing-api', fn (object $job): Limit => Limit::perSecond(maxAttempts: 3));
    }
}
