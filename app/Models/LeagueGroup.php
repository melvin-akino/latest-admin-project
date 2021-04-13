<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueGroup extends Model
{
    protected $table = "league_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_league_id',
        'league_id',
    ];

    public $timestamps = false;
    public $incrementing = false;
}
