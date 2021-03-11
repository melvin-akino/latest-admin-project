<?php

namespace App\Providers;

use App\Services\EventGroupService;
use Illuminate\Support\ServiceProvider;

class EventGroupServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Services\EventGroupService', function() {
            return new EventGroupService;
        });
    }
}