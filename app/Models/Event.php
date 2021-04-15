<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use App\Models\{League, Team};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * @param  string       $searchKey
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getByProvider(int $providerId, string $searchKey = '', string $sortOrder = 'asc', bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return DB::table('events as e')
            ->join('leagues as l', 'l.id', 'e.league_id')
            ->join('teams as th', 'th.id', 'e.team_home_id')
            ->join('teams as ta', 'ta.id', 'e.team_away_id')
            ->join('league_groups as lg', 'lg.league_id', 'e.league_id')
            ->select(['e.id', 'e.sport_id', 'e.provider_id', 'e.ref_schedule', 'l.name as league_name', 'th.name as team_home_name', 'ta.name as team_away_name', 'lg.master_league_id as master_league_id'])
            ->{$where}('e.id', function ($q) {
                $q->select('event_id')
                    ->from('event_groups');
            })
            ->when(!$grouped, function ($q) use ($providerId) {
                $q->whereIn('e.id', function ($where) use ($providerId) {
                    $where->select('ud.data_id')
                        ->from('unmatched_data AS ud')
                        ->where('ud.data_type', 'event')
                        ->where('ud.provider_id', $providerId);
                })
                ->whereIn('e.team_home_id', function($q) {
                    $q->select('team_id')->from('team_groups');
                })
                ->whereIn('e.team_away_id', function($q) {
                    $q->select('team_id')->from('team_groups');
                })
                ->whereIn('e.league_id', function($q) {
                    $q->select('league_id')->from('league_groups');
                });
            })
            ->where('e.provider_id', $providerId)
            ->where(DB::raw('CONCAT(l.name, \' \', th.name, \' \', ta.name, \' \', e.ref_schedule)'), 'ILIKE', '%'.$searchKey.'%')
            ->whereNull('e.deleted_at')
            ->orderBy('e.ref_schedule', $sortOrder)
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

    public static function getAllActiveNotExistInPivotByProviderId($providerId)
    {
        return self::where('provider_id', $providerId)->doesntHave('eventGroup')->get();
    }

    public function eventGroup()
    {
        return $this->hasOne(EventGroup::class, 'event_id', 'id');
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

    public static function getAllOtherProviderUnmatchedEvents(int $primaryProviderId)
    {
        return self::where('provider_id', '!=',$primaryProviderId)
            ->whereNotIn('id', function($notInUnmatched) {
                $notInUnmatched->select('data_id')
                    ->from('unmatched_data')
                    ->where('data_type', 'event');
            })
            ->whereNotIn('id', function($notInEventGroups) use ($primaryProviderId) {
                $notInEventGroups->select('event_id')
                    ->from('event_groups')
                    ->join('events', 'events.id', 'event_groups.event_id')
                    ->where('provider_id', '!=', $primaryProviderId);
            })
            ->select('id', 'provider_id')
            ->get();
    }

    public static function getMasterEventId($eventId, int $primaryProviderId)
    {
        $masterEventId = null;
        $unmatchedEventInfo = DB::table('unmatched_data as ue')
                ->join('events as e', 'e.id', 'ue.data_id')
                ->join('team_groups as htg', 'htg.team_id', 'e.team_home_id')
                ->join('teams as ht', 'ht.id', 'e.team_home_id')
                ->join('team_groups as atg', 'atg.team_id', 'e.team_away_id')
                ->join('teams as at', 'at.id', 'e.team_away_id')
                ->join('league_groups as lg', 'lg.league_id', 'e.league_id')
                ->join('leagues as l', 'l.id', 'e.league_id')
                ->where('ue.data_type', 'event')
                ->where('ue.data_id', $eventId)
                ->select(
                    'htg.master_team_id as master_home_team', 
                    'atg.master_team_id as master_away_team', 
                    'lg.master_league_id', 
                    'e.ref_schedule', 
                    'l.name as league_name', 
                    'ht.name as home_team',
                    'at.name as away_team',
                    'e.sport_id'
                )
                ->first();
        if ($unmatchedEventInfo) {
            $masterEventInfo = DB::table('master_events as me')
                ->join('event_groups as eg', 'eg.master_event_id', 'me.id')
                ->join('events as e', 'e.id', 'eg.event_id')
                ->join('teams as ht', 'ht.id', 'e.team_home_id')
                ->join('teams as at', 'at.id', 'e.team_away_id')
                ->join('leagues as l', 'l.id', 'e.league_id')
                ->where('master_team_home_id', $unmatchedEventInfo->master_home_team)
                ->where('master_team_away_id', $unmatchedEventInfo->master_away_team)
                ->where('master_league_id', $unmatchedEventInfo->master_league_id)
                ->where('e.provider_id', $primaryProviderId)
                ->whereNull('me.deleted_at')
                ->select(
                    'me.id as master_event_id', 
                    'e.sport_id', 
                    'l.name as league_name', 
                    'ht.name as home_team', 
                    'at.name as away_team', 
                    'e.ref_schedule'
                )
                ->first();
            if ($masterEventInfo) {
                if ($masterEventInfo->sport_id == $unmatchedEventInfo->sport_id
                    && $masterEventInfo->league_name == $unmatchedEventInfo->league_name
                    && $masterEventInfo->home_team == $unmatchedEventInfo->home_team
                    && $masterEventInfo->away_team == $unmatchedEventInfo->away_team
                    && abs(strtotime($masterEventInfo->ref_schedule) - strtotime($unmatchedEventInfo->ref_schedule)) < 60) 
                {
                    $masterEventId = $masterEventInfo->master_event_id;
                }
            }
        }
        return $masterEventId;
    }

    public static function getSoftDeletedEvent($event)
    {
        //check if event has similar league, home team, away team and ref sched filtered by hour and currently soft deleted and hasEventGroups
        $softDeletedEvent = self::select('master_event_id','id')
            ->join('event_groups', 'event_groups.event_id', 'id')
            ->where('team_home_id', $event['team_home_id'])
            ->where('team_away_id', $event['team_away_id'])
            ->where('league_id', $event['league_id'])
            ->where('sport_id', $event['sport_id'])
            ->where('provider_id', $event['provider_id'])
            ->where(DB::raw('TO_TIMESTAMP(ref_schedule, YYYY-MM-DD HH24) = ' . Carbon::createFromFormat('YYYY-MM-DD HH', $event['ref_schedule'])))
            ->whereNotNull('events.deleted_at')
            ->orderBy('events.id', 'desc')
            ->first();
        //now check if this event has a matched data in the event_groups table
        $eventGroup = DB::table('event_groups')
            ->where('master_event_id', $softDeletedEvent->master_event_id)
            ->count();

        if ($eventGroup > 1) {
            return $softDeletedEvent;
        }

        return null;
    }
}
