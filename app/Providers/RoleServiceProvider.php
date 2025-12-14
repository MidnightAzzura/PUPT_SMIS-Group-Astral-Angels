<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Role;

class RoleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind 'role' to our Role model class (not instance) to prevent callable errors
        $this->app->bind('role', Role::class);
        
        // Also bind the class directly for explicit resolution
        $this->app->singleton(Role::class, function ($app) {
            return new Role();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
