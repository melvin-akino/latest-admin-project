<?php

namespace App\Providers;

use App\Services\RawListingService;
use Illuminate\Support\ServiceProvider;

class RawListingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('App\Services\RawListingService', function() {
            return new RawListingService;
        });
    }
}
