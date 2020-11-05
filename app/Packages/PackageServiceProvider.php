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
        require_once __DIR__ . '/Methods/autoload.php';

        //解决安装数据库部分语句
        Schema::defaultStringLength(191);

        //注册 validator扩展
        ValidatorExtend::boot();
    }

    public function register()
    {
        $this->routeMacro();
        $this->registerTelescope();
        $this->registerAliOssProvider();
        PostmanRoute::register();
    }

    private function registerAliOssProvider()
    {
        $this->app->register(AliOssServiceProvider::class);
    }

    private function registerTelescope()
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function routeMacro()
    {
        Route::macro('auto', function ($prefix, $controller) {
            Route::any($prefix . '/{action}', function ($action) use ($controller) {
                $separator = '-';
                if (strpos($action, $separator)) {
                    $action = $separator . str_replace($separator, ' ', strtolower($action));
                    $action = ltrim(str_replace(' ', '', ucwords($action)), $separator);
                }
                $namespace = 'App\Http\Controllers\\';
                $class     = $namespace . ucfirst($controller);
                $ctrl      = \App::make($class);
                return \App::call([$ctrl, $action]);
            });
        });
    }
}
