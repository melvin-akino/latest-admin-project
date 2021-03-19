<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use App\Models\{League, Team};
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use SoftDeletes;

    protected $table = "events";

    protected $fillable = [
        'master_event_id',
        'sport_id',
        'provider_id',
        'event_identifier',
        'league_id',
        'team_home_id',
        'team_away_id',
        'ref_schedule',
        'game_schedule',
        'deleted_at',
        'missing_count'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get `events` data by Provider, also allowing to choose from `raw` or `existing match`
     * 
     * @param  int          $providerId
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getEventsByProvider(int $providerId, bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return DB::table('events as e')
            ->join('leagues as l', 'l.id', 'e.league_id')
            ->join('teams as th', 'th.id', 'e.team_home_id')
            ->join('teams as ta', 'ta.id', 'e.team_away_id')
            ->select(['e.id', 'e.sport_id', 'e.provider_id', 'e.ref_schedule', 'l.name as league_name', 'th.name as team_home_name', 'ta.name as team_away_name'])
            ->{$where}('e.id', function ($query) {
                $query->select('event_id')
                    ->from('event_groups');
            })
            ->when(!$grouped, function ($query) use ($providerId) {
                $query->whereIn('e.id', function ($where) use ($providerId) {
                    $where->select('ud.data_id')
                        ->from('unmatched_data AS ud')
                        ->where('ud.data_type', 'event')
                        ->where('ud.provider_id', $providerId);
                });
            })
            ->where('e.provider_id', $providerId)
            ->orderBy('e.ref_schedule', 'desc')
            ->get();
    }

    public static function getGroupVerifiedUnmatchedEvent($eventId)
    {
        return DB::table('events as e')
            ->join('unmatched_data as ue', 'ue.data_id', 'e.id')
            ->join('team_groups as ht', 'ht.team_id', 'e.team_home_id')
            ->join('team_groups as at', 'at.team_id', 'e.team_away_id')
            ->join('league_groups as lg', 'lg.league_id', 'e.league_id')
            ->where('ue.data_type', 'event')
            ->where('ue.data_id', $eventId)
            ->count();
    }

    public function league() 
    {
        return $this->belongsTo(League::class);
    }

    public function teamHome() 
    {
        return $this->belongsTo(Team::class, 'team_home_id', 'id');
    }

    public function teamAway() 
    {
        return $this->belongsTo(Team::class, 'team_away_id', 'id');
    }
}
