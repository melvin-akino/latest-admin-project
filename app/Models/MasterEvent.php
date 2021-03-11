<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class MasterEvent extends Model
{
    use SoftDeletes;

    protected $table = 'master_events';

    protected $fillable = [
        'sport_id',
        'master_league_id',
        'master_team_home_id',
        'master_team_away_id',
        'master_event_unique_id',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
