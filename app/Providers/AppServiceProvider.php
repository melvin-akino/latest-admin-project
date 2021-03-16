<?php

namespace App\Providers;
use App\Models\Event;
use Validator;
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
        Validator::extend('check_if_league_and_team_is_matched', function($attribute, $value, $parameters, $validator) {
            $event = Event::getGroupVerifiedUnmatchedEvent($value);
            if (!empty($event))
            {
                return true;
            }
            return false;
        });
    }
}
