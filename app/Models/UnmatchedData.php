<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class UnmatchedData extends Model
{
    protected $table = "unmatched_data";

    protected $primaryKey = null;

    protected $fillable = [
        'data_type',
        'data_id',
        'provider_id',
        'is_failed'
    ];

    public $timestamps = false;
    public $incrementing = false;

    public static function getUnmatchedLeagueData($type) 
    {
        return self::where('data_type', $type)
            ->where('is_failed', false)
            ->join('leagues', 'leagues.id', 'data_id')
            ->select('data_id', 'leagues.provider_id', 'sport_id', 'name')
            ->get()
            ->toArray();
    }

    public static function getUnmatchedTeamData($type) 
    {
        return self::where('data_type', $type)
            ->where('is_failed', false)
            ->join('teams', 'teams.id', 'data_id')
            ->select('data_id', 'teams.provider_id', 'sport_id', 'name')
            ->get()
            ->toArray();
    }

    public static function getUnmatchedEventData($type) 
    {
        return self::where('data_type', $type)
            ->where('is_failed', false)
            ->join('events as e', 'e.id', 'data_id')
            ->join('team_groups as htg', 'htg.team_id', 'e.team_home_id')
            ->join('team_groups as atg', 'atg.team_id', 'e.team_away_id')
            ->join('league_groups as lg', 'lg.league_id', 'e.league_id')
            ->select('data_id', 
                'e.provider_id', 
                'htg.master_team_id as master_home_team',
                'atg.master_team_id as master_away_team',
                'lg.master_league_id',
                'e.sport_id',
                'e.team_home_id',
                'e.team_away_id',
                'e.league_id',
                'e.ref_schedule',
                'e.event_identifier'
            )
            ->get()
            ->toArray();
    }

    public static function getAllFailedData()
    {
        return self::where('is_failed', true)
            ->select('data_type', DB::raw('count(data_type) as type_count'))
            ->groupBy('data_type')
            ->get()
            ->toArray();
    }

    public static function findUnmatchedData($dataType, $dataId, $providerId) {
        return self::where('data_type', $dataType)
            ->where('data_id', $dataId)
            ->where('provider_id', $providerId)
            ->first();
    }
}
