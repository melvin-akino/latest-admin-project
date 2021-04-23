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

    public static function getNonPrimaryLeagueIds($masterLeagueId, int $primaryProviderId) {
        return self::join('leagues as l', 'l.id', 'league_groups.league_id')
            ->when($masterLeagueId, function ($query) use ($masterLeagueId) {
              $query->where('league_groups.master_league_id', $masterLeagueId);
            })
            ->where('l.provider_id', '!=', $primaryProviderId)
            ->pluck('l.id');
    }

    public function leagues()
    {
        return $this->belongsTo(League::class, 'id', 'league_id');
    }

    public function masterLeagues()
    {
        return $this->belongsTo(MasterLeague::class, 'id', 'master_league_id');
    }

    public static function checkLeagueIfmatched($leagueId)
    {
        return self::where('league_id', $leagueId)->count();
    }
}
