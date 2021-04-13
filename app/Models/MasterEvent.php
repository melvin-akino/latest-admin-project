<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class MasterEvent extends Model
{
    use SoftDeletes;

    protected $table = "master_events";

    protected $fillable = [
        'master_event_unique_id',
        'sport_id',
        'master_league_id',
        'master_team_home_id',
        'master_team_away_id'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function eventGroups()
    {
        return $this->hasMany(EventGroup::class, 'master_event_id', 'id');
    }
}
