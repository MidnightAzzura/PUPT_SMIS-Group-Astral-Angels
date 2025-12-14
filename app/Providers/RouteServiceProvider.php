<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Http\Middleware\CheckPermission;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * This namespace is applied to your controller routes.
     * Route::group([...])->namespace($this->namespace);
     *
     * @var string
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless and are used to serve API requests.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'prefix'     => 'api',
            'namespace'  => $this->namespace.'\\Api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes are typically stateful and are used to serve web pages.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace'  => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    protected $routeMiddleware = [
        'can' => \App\Http\Middleware\CheckPermission::class,
    ];
}