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
}

class Matching extends Model
{
    protected $models;

    public function __construct()
    {
        parent::__construct();

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
            $this->models[$model] = $model::class;
        }
    }

    public function firstOrCreate($modelName, $searchData, $createData)
    {
        //@TODO add the audit trail here

        return $this->{$modelName}->firstOrCreate($searchData, $createData);
    }

    public function updateOrCreate($modelName, $searchData, $createData)
    {
        //@TODO add the audit trail here
        
        return $this->{$modelName}->firstOrCreate($searchData, $createData);
    }

    public function find($modelName, $primaryId)
    {
        //@TODO add the audit trail here
        
        return $this->{$modelName}->find($primaryId);
    }
}
