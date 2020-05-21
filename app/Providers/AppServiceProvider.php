<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // \Redis::enableEvents();

        \App\Models\SinglePage::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\Article::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\Option::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\OptionFocus::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoConfig::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoKenoCa::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoKenoDe::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoKenoHero::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3AnHui::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3BeiJing::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3HeBei::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3HuBei::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3JiangSu::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoK3ShangHai::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoSscHeiLongJiang::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoSscTianJin::observe(\App\Observers\FlushCacheObserver::class);
        \App\Models\LottoModule\Models\LottoSscXinJiang::observe(\App\Observers\FlushCacheObserver::class);

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
