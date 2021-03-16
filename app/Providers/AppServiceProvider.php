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
        Validator::extend('checkIfMatchedLeagueAndTeam', function($attribute, $value, $parameters, $validator) {
            $sql = "SELECT e.id as event_id, e.ref_schedule, lg.master_league_id, ht.master_team_id as master_home_team_id, at.master_team_id as master_away_team_id FROM " . static::$table . " as e"
        . " JOIN unmatched_data as ue on ue.data_id = e.id"
        . " JOIN team_groups as ht on ht.team_id = e.team_home_id"
        . " JOIN team_groups as at on at.away_team_id = e.team_away_id"
        . " JOIN league_groups as lg on lg.league_id = e.league_id"          
        . " WHERE ue.data_type = 'event'";
            $team = Event::where(['team_id' => $value])->count();
            if (!empty($team))
            {
                return true;
            }
            return false;
        });
    }
}
