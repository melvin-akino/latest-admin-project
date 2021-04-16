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

    public static function getByLeagueId($leagueId)
    {
        return self::where('league_id', $leagueId)->get();
    }

    public function leagues()
    {
        return $this->belongsTo(League::class, 'id', 'league_id');
    }

    public function masterLeagues()
    {
        return $this->belongsTo(MasterLeague::class, 'id', 'master_league_id');
    }
}
