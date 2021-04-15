<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    League,
    LeagueGroup,
    MasterLeague,
    Team,
    TeamGroup,
    MasterTeam,
    Event,
    EventGroup,
    MasterEvent,
    EventMarket,
    EventMarketGroup,
    MasterEventMarket
};

class Matching
{
    protected $models;

    public function __construct()
    {
        $models = [
            'League',
            'LeagueGroup',
            'MasterLeague',
            'Team',
            'TeamGroup',
            'MasterTeam',
            'Event',
            'EventGroup',
            'MasterEvent',
            'EventMarket',
            'EventMarketGroup',
            'MasterEventMarket'
        ];

        foreach ($models as $model) {
            $this->models[$model] = "App\Models\\" . $model;
        }
    }

    public function firstOrCreate($modelName, $searchData, $createData)
    {
        //@TODO add the audit trail here

        return $this->models[$modelName]::firstOrCreate($searchData, $createData);
    }

    public function create($modelName, $createData)
    {
        //@TODO add the audit trail here

        return $this->models[$modelName]::create($createData);
    }

    public function updateOrCreate($modelName, $searchData, $createData)
    {
        //@TODO add the audit trail here
        
        return $this->models[$modelName]::updateOrCreate($searchData, $createData);
    }
}
