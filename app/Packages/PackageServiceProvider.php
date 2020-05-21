<?php
namespace App\Packages;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Packages\Utils\ValidatorExtend;
use Illuminate\Support\ServiceProvider;
use App\Packages\Utils\Postman\PostmanRoute;
use Laravel\Telescope\TelescopeServiceProvider;
use App\Packages\Utils\AliOssProvider\AliOssServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require_once __DIR__ . '/Utils/Helpers.php';

        //解决安装数据库部分语句
        Schema::defaultStringLength(191);

        //注册 validator扩展
        ValidatorExtend::boot();
    }

    public function register()
    {
        $this->registerCommand();
        $this->routeMacro();
        $this->registerTelescope();
        $this->registerAliOssProvider();
        PostmanRoute::register();
    }

    private function registerAliOssProvider()
    {
        $this->app->register(AliOssServiceProvider::class);
    }

    private function registerCommand()
    {
        $this->commands([
            \App\Packages\Console\ControllerCommand::class,
            \App\Packages\Console\ExceptionCommand::class,
        ]);
    }

    private function registerTelescope()
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function routeMacro()
    {
        Route::macro('full', function ($prefix, $controller) {
            Route::get($prefix . '/index', $controller . '@index');
            Route::get($prefix . '/get', $controller . '@get');
            Route::post($prefix . '/create', $controller . '@create');
            Route::post($prefix . '/update', $controller . '@update');
            Route::post($prefix . '/delete', $controller . '@delete');
        });

        Route::macro('auto', function ($prefix, $controller) {
            Route::any($prefix . '/{action}', function ($action) use ($controller) {
                $namespace = 'App\Http\Controllers\\';
                $class     = $namespace . ucfirst($controller);
                $ctrl      = \App::make($class);
                return \App::call([$ctrl, $action]);
            });
        });
    }
}
