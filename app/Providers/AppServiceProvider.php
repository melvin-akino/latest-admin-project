<?php

namespace App\Providers;
use App\Models\{Event, LeagueGroup, Provider, SystemConfiguration};
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
            // if (empty($event)) {
            //     return true;
            // }

            return empty($event) ? true : false;
        });

        Validator::extend('check_if_league_is_matched', function($attribute, $value, $parameters, $validator) {
            $league = LeagueGroup::checkLeagueIfmatched($value);
            if ($league > 0)
            {
                return true;
            }
            return false;
        });

        Validator::extend('check_if_provider_is_secondary', function($attribute, $value, $parameters, $validator) {
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
            return (boolean) ($value != $primaryProviderId);
        });
    }
}
