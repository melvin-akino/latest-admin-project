<?php

namespace App\Providers;

use App\Services\AdminSettlementService;
use Illuminate\Support\ServiceProvider;

class AdminSettlementServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Services\AdminSettlementService', function() {
            return new AdminSettlementService;
        });
    }
}