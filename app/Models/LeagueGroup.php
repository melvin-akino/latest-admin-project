<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class LeagueGroup extends Model
{
    use SoftDeletes;

    protected $table = 'league_groups';

    protected $fillable = [
        'master_league_id',
        'league_id'
    ];
}
