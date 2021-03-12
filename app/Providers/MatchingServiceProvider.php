<?php

namespace App\Providers;

use App\Services\MatchingService;
use Illuminate\Support\ServiceProvider;

class MatchingServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Services\MatchingService', function() {
            return new MatchingService;
        });
    }
}
