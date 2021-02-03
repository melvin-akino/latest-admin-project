<?php

namespace App\Providers;

use App\Services\ProviderTransactionService;
use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Services\ProviderTransactionService', function() {
            return new ProviderTransactionService;
        });
    }
}
