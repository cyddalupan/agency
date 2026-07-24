<?php

namespace App\Providers;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsEmployer;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport;

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
        // Register Brevo API mail transport (port 587 blocked on this server)
        $this->app->make('mail.manager')->extend('brevo', function () {
            $apiKey = config('mail.mailers.brevo.api_key', env('BREVO_API_KEY'));
            $dsn = 'brevo+api://' . urlencode($apiKey) . '@default';
            return Transport::fromDsn($dsn);
        });

        // Share authenticated user with all employer-app views (both FRA and non-FRA)
        View::composer('layouts.employer-app', function ($view) {
            $view->with('user', Auth::user());
        });

        View::composer('layouts.employer-app-fra', function ($view) {
            $view->with('user', Auth::user());
        });

        // Register User policy for agency-scoped user management.
        Gate::policy(User::class, UserPolicy::class);

        // 🔒 Block destructive artisan commands in non-production when .env is 'local' but
        // this is a production server. Only allow them when APP_ENV is explicitly 'testing'.
        $this->blockDestructiveCommands();

        // Ensure middleware aliases are registered in both HTTP and Console/testing contexts.
        // bootstrap/app.php aliases are synced to the HTTP kernel, but the test framework
        // boots via the Console kernel which doesn't sync HTTP middleware aliases.
        $router = $this->app['router'];
        $router->aliasMiddleware('employer', EnsureUserIsEmployer::class);
        $router->aliasMiddleware('role', CheckRole::class);
    }

    /**
     * Prevent destructive artisan commands from running on this server.
     * Overrides them with custom command classes that block execution.
     * Skips in testing so CI/tests can run normally.
     */
    private function blockDestructiveCommands(): void
    {
        if ($this->app->environment('testing')) {
            return;
        }

        $this->commands([
            \App\Console\SafeCommands\_SafeMigrateFresh::class,
            \App\Console\SafeCommands\_SafeMigrateRefresh::class,
            \App\Console\SafeCommands\_SafeMigrateReset::class,
            \App\Console\SafeCommands\_SafeDbWipe::class,
        ]);
    }
}
