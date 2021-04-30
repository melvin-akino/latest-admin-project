<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use App\Models\{League, Team, Matching, SystemConfiguration AS SC, Provider};
use Illuminate\Support\Facades\{DB, Log};
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

    public static function checkRawEventProvider(int $rawId)
    {
        $primaryProviderId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $query             = self::withTrashed()->find($rawId)->provider_id;

        return $primaryProviderId == $query ? true : false;
    }

    public static function getEventData(int $rawId)
    {
        return self::withTrashed()->join('leagues AS l', 'l.id', 'events.league_id')
            ->join('teams AS th', 'th.id', 'events.team_home_id')
            ->join('teams AS ta', 'ta.id', 'events.team_away_id')
            ->join('providers AS p', 'p.id', 'events.provider_id')
            ->where('events.id', $rawId)
            ->select([
                'p.alias AS alias',
                'events.event_identifier AS event_id',
                'l.name AS league_name',
                'th.name AS team_home_name',
                'ta.name AS team_away_name',
                'events.ref_schedule AS ref_schedule'
            ])
            ->first();
    }

    /**
     * Get `events` data by League, also allowing to choose from `raw` or `existing match`
     * 
     * @param  int          $leagueId
     * @param  int          $providerId
     * @param  bool|boolean $grouped
     * @param  string       $searchKey
     * @param  string       $sortOrder
     * 
     * @return object
     */
    public static function getEvents($leagueIds, $providerId, bool $grouped = true, $searchKey = '', string $sortOrder = 'asc', $gameSchedule = null)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return DB::table('events as e')
            ->join('leagues as l', 'l.id', 'e.league_id')
            ->join('teams as th', 'th.id', 'e.team_home_id')
            ->join('teams as ta', 'ta.id', 'e.team_away_id')
            ->join('providers as p', 'p.id', 'e.provider_id')
            ->when($grouped, function ($query) {
                $query->join('league_groups as lg', 'lg.league_id', 'l.id')
                    ->join('master_leagues as ml', 'ml.id', 'lg.master_league_id');
            })
            ->select(['e.id', 'e.provider_id', 'p.alias as provider', 'e.sport_id', 'e.event_identifier', 'l.name as league_name', 'th.name as team_home_name', 'ta.name as team_away_name', 'e.ref_schedule', 'e.game_schedule'])
            ->{$where}('e.id', function ($q) {
                $q->select('event_id')
                    ->from('event_groups');
            })
            ->when($leagueIds, function ($query) use ($leagueIds) {
                $query->whereIn('e.league_id', $leagueIds);
            })
            ->when($providerId, function ($query) use ($providerId) {
                $query->where('e.provider_id', $providerId);
            })
            ->when($gameSchedule, function ($query) use ($gameSchedule) {
                $query->whereIn('e.game_schedule', $gameSchedule);
            })
            ->where('l.name', 'ILIKE', '%'.$searchKey.'%')
            ->whereNull('e.deleted_at')
            ->when($grouped, function ($query) {
                $query->orderBy('ml.is_priority', 'desc');
            })
            ->orderBy('e.ref_schedule', $sortOrder);
    }

    public static function getMatchedEventsByMasterLeagueId($masterLeagueId)
    {
        return DB::table('events as e')
            ->join('event_groups as eg', 'e.id', 'eg.event_id')
            ->join('providers as p', 'p.id', 'e.provider_id')
            ->join('leagues as l', 'l.id', 'e.league_id')
            ->join('league_groups as lg', 'l.id', 'lg.league_id')
            ->join('teams as th', 'th.id', 'e.team_home_id')
            ->join('teams as ta', 'ta.id', 'e.team_away_id')
            ->where('lg.master_league_id', $masterLeagueId)
            ->whereNull('e.deleted_at')
            ->select('e.id', 'e.event_identifier', 'eg.master_event_id', 'e.provider_id', 'p.alias as provider', 'e.sport_id', 'l.name as league_name', 'th.name as team_home_name', 'ta.name as team_away_name', 'e.ref_schedule')
            ->orderBy('eg.master_event_id', 'asc')
            ->orderBy('e.provider_id', 'asc')
            ->distinct()
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
        return self::where('provider_id', '!=', $primaryProviderId)
            ->whereNotIn('id', function($query) {
                return $query->select('event_id')
                    ->from('event_groups');
            })
            ->whereNull('deleted_at')
            ->select('id', 'provider_id')
            ->get();
    }

    public static function getMasterEventId($unmatchedEventInfo, int $primaryProviderId) {
		$masterEventId=null;
		$matching=new Matching();
	  //check first if this event is already existing in the db but soft deleted, and if so, re-use the existing master_event_id
		$event=self::getSoftDeletedEvent((array)$unmatchedEventInfo);
		if(!empty($event)) {
			//delete the soft deleted event_groups entry for this soft deleted but return the existing masterEventId
			$matching->delete('EventGroup', [
				'master_event_id' => $event->master_event_id,
				'event_id'    => $event->id
			]);
			$masterEventId=$event->master_event_id;
		}
		else {
			$masterEventInfo=DB::table('master_events as me')
				->join('event_groups as eg', 'eg.master_event_id', 'me.id')
				->join('events as e', 'e.id', 'eg.event_id')
				->where('master_team_home_id', $unmatchedEventInfo['master_home_team'])
				->where('master_team_away_id', $unmatchedEventInfo['master_away_team'])
				->where('master_league_id', $unmatchedEventInfo['master_league_id'])
				->where('e.provider_id', $primaryProviderId)
				->where('e.sport_id', $unmatchedEventInfo['sport_id'])
				->whereNull('me.deleted_at')
				->select(
                    'me.id as master_event_id',
                    'e.ref_schedule'
                )
			    ->first();
			if($masterEventInfo) {
				if(Carbon::parse($masterEventInfo->ref_schedule)->format('YmdH')==Carbon::parse($masterEventInfo->ref_schedule)->format('YmdH')) {
					$masterEventId=$masterEventInfo->master_event_id;
				}
			}
		}
		return $masterEventId;
	}

    public static function getSoftDeletedEvent($event)
    {
        $eventGroup = null;
        $softDeletedRefSchedule = null;
        $newEventRefSchedule = Carbon::parse($event['ref_schedule'])->format('YmdH');
        //check if event has similar league, home team, away team and ref sched filtered by hour and currently soft deleted and hasEventGroups
        $softDeletedEvent = self::select('event_groups.master_event_id','id','ref_schedule')
            ->join('event_groups', 'event_groups.event_id', 'id')
            ->where('team_home_id', $event['team_home_id'])
            ->where('team_away_id', $event['team_away_id'])
            ->where('league_id', $event['league_id'])
            ->where('sport_id', $event['sport_id'])
            ->where('provider_id', $event['provider_id'])
            ->whereNotNull('events.deleted_at')
            ->orderBy('events.id', 'desc')
            ->first();
        if (!empty($softDeletedEvent)) {
            $softDeletedRefSchedule = Carbon::parse($softDeletedEvent->ref_schedule)->format('YmdH');
            
            //now check if this event has a matched data in the event_groups table
            $eventGroup = DB::table('event_groups')
                ->where('master_event_id', $softDeletedEvent->master_event_id)
                ->count();
        }

        if ($softDeletedRefSchedule == $newEventRefSchedule &&  $eventGroup > 1) {
            return $softDeletedEvent;
        }

        return null;
    }

    public static function getLeagueEvents($leagueId, $providerId, $sportId)
    {
        return DB::table('event_groups as eg')
            ->join('events as e', 'e.id', 'eg.event_id')
            ->join('team_groups as ht', 'ht.team_id', 'e.team_home_id')
            ->join('team_groups as at', 'at.team_id', 'e.team_away_id')
            ->where('e.league_id', $leagueId)
            ->where('e.provider_id', $providerId)
            ->where('e.sport_id', $sportId)
            ->select(
                'ht.master_team_id as team_master_home_id',
                'team_home_id', 
                'at.master_team_id as team_master_away_id', 
                'team_away_id',
                'eg.master_event_id', 
                'e.id'
            )
            ->get()
            ->toArray();
    }

    public static function getTeamEvents($teamId, $providerId, $sportId)
    {
        return DB::table('event_groups as eg')
            ->join('events as e', 'e.id', 'eg.event_id')
            ->join('team_groups as ht', 'ht.team_id', 'e.team_home_id')
            ->join('team_groups as at', 'at.team_id', 'e.team_away_id')
            ->where('e.provider_id', $providerId)
            ->where('e.sport_id', $sportId)
            ->where(function($query) use ($teamId) {
                $query->where('e.team_home_id', $teamId)
                    ->orWhere('e.team_away_id', $teamId);
            })
            ->select(
                'ht.master_team_id as team_master_home_id',
                'team_home_id', 
                'at.master_team_id as team_master_away_id', 
                'team_away_id',
                'eg.master_event_id', 
                'e.id'
            )
            ->get()
            ->toArray();
    }

    public static function getEventInfo($eventId, $providerId, $sportId)
    {
        return DB::table('event_groups as eg')
            ->join('events as e', 'e.id', 'eg.event_id')
            ->join('team_groups as ht', 'ht.team_id', 'e.team_home_id')
            ->join('team_groups as at', 'at.team_id', 'e.team_away_id')
            ->where('e.id', $eventId)
            ->where('e.provider_id', $providerId)
            ->where('e.sport_id', $sportId)
            ->select(
                'ht.master_team_id as team_master_home_id',
                'team_home_id', 
                'at.master_team_id as team_master_away_id', 
                'team_away_id',
                'eg.master_event_id', 
                'e.id'
            )
            ->first();
    }
}
