<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnmatchedData extends Model
{
    protected $table = "unmatched_data";

    protected $primaryKey = null;

    protected $fillable = [
        'data_type',
        'data_id',
        'provider_id'
    ];

    public $timestamps = false;
    public $incrementing = false;

    public static function getUnmatchedLeagueData($type) 
    {
        return self::where('data_type', $type)
            ->join('leagues', 'leagues.id', 'data_id')
            ->select('data_id', 'leagues.provider_id', 'sport_id', 'name')
            ->get()
            ->toArray();
    }

    public static function getUnmatchedTeamData($type) 
    {
        return self::where('data_type', $type)
            ->join('teams', 'teams.id', 'data_id')
            ->select('data_id', 'teams.provider_id', 'sport_id', 'name')
            ->get()
            ->toArray();
    }

    public static function getUnmatchedEventData($type) 
    {
        return self::where('data_type', $type)
            ->join('events', 'events.id', 'data_id')
            ->select('data_id', 'events.provider_id')
            ->get()
            ->toArray();
    }
}
