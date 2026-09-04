<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    /**
     * Register the error monitoring / reporting client.
     *
     * When a DSN is configured (config/reporting.php), this provider
     * registers the reporting client so unhandled exceptions are sent
     * to the monitoring service (e.g. Sentry-compatible endpoint).
     */
    public function register(): void
    {
        $this->app->singleton('reporting', function ($app) {
            return new \App\Services\ReportingClient(
                $app['config']->get('reporting.dsn')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
