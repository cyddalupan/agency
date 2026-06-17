<?php

namespace App\Providers;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsEmployer;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
