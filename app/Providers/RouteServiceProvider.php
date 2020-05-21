<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
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

        $this->mapClientApiRoutes();

        $this->mapAdminApiRoutes();

        $this->mapOpenRoutes();
    }

    protected function mapAdminApiRoutes()
    {
        Route::prefix('api/admin')
            ->middleware('api')
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.api.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapClientApiRoutes()
    {
        Route::prefix('api/client')
            ->middleware('api')
            ->namespace($this->namespace . '\Client')
            ->group(base_path('routes/client.api.php'));
    }

    protected function mapOpenRoutes()
    {
        Route::prefix('open-api')
            ->middleware('api')
            ->namespace($this->namespace . '\OpenApi')
            ->group(base_path('routes/open.api.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
}
