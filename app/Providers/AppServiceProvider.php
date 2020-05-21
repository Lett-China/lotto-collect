<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //解决audit如果为空的情况下 也会插入数据
        \OwenIt\Auditing\Models\Audit::creating(function (\OwenIt\Auditing\Models\Audit $model) {
            if (empty($model->old_values) && empty($model->new_values)) {
                return false;
            }
        });

        \Studio\Totem\Totem::auth(function ($request) {
            $access = env('TOTEM_KEY', 'lettbee');
            $cookie = $request->cookie('__totem_key__');

            if ($cookie && $cookie === $access) {
                return true;
            }

            if ($request->code && $request->code === $access) {
                $cookie = $request->code;
                \Cookie::queue('__totem_key__', $cookie, 60 * 24 * 7, '/');
                return true;
            }

            return false;
        });
    }

    public function register()
    {
        //
    }
}
