<?php

namespace App\Providers;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsEmployer;
use App\Models\User;
use App\Policies\UserPolicy;
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

        // Ensure middleware aliases are registered in both HTTP and Console/testing contexts.
        // bootstrap/app.php aliases are synced to the HTTP kernel, but the test framework
        // boots via the Console kernel which doesn't sync HTTP middleware aliases.
        $router = $this->app['router'];
        $router->aliasMiddleware('employer', EnsureUserIsEmployer::class);
        $router->aliasMiddleware('role', CheckRole::class);
    }
}
