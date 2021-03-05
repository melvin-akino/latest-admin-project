<?php

namespace App\Providers;

use App\Services\ProviderAccountService;
use Illuminate\Support\ServiceProvider;

class ProviderAccountServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Services\ProviderAccountService', function() {
            return new ProviderAccountService;
        });
    }
}