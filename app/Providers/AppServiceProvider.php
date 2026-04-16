<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Setting;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        \Illuminate\Pagination\Paginator::useBootstrap();        

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        if (!defined('APP_NAME')) {

            $appConfig = Cache::remember('app_config', 60 * 60, function () {
                try {
                    return Setting::first();
                } catch (\Exception $e) {
                    return null;
                }
            });

            if (!$appConfig) {
                $appConfig = new Setting();

                $appConfig->app_name = 'Laravel';
            }

            View::share('appConfig', $appConfig);

            define('APP_NAME', $appConfig->app_name ?? 'Laravel');
        }
    }
}
