<?php

namespace App\Providers;

use App\Models\Param\RefAdvParamstring;
use App\Observers\ParamStringObserver;
use Illuminate\Support\ServiceProvider;

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
        RefAdvParamstring::observe(ParamStringObserver::class);
    }
}
