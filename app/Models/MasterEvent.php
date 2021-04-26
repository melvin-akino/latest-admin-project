<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use App\Models\{LeagueGroup, SystemConfiguration AS SC, Provider};

class MasterEvent extends Model
{
    use SoftDeletes;

    protected $table = "master_events";

    protected $fillable = [
        'master_event_unique_id',
        'sport_id',
        'master_league_id',
        'master_team_home_id',
        'master_team_away_id'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public static function getEventInfo(int $masterId)
    {
        $primaryProviderId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        return self::join('event_groups AS egp', 'egp.master_event_id', 'master_events.id')
            ->join('events AS ep', function ($join) use ($primaryProviderId) {
                $join->on('ep.id', 'egp.event_id');
                $join->where('ep.provider_id', $primaryProviderId);
            })
            ->join('leagues AS lp', 'lp.id', 'ep.league_id')
            ->join('teams AS thp', 'thp.id', 'ep.team_home_id')
            ->join('teams AS tap', 'tap.id', 'ep.team_away_id')
            ->join('providers AS pp', 'ep.provider_id', 'pp.id')
            ->where('master_events.id', $masterId)
            ->select([
                'pp.alias AS primary_alias',
                'ep.event_identifier AS primary_event_id',
                DB::raw("COALESCE(lp.name, lp.name) AS primary_league_name"),
                DB::raw("COALESCE(thp.name, thp.name) AS primary_team_home_name"),
                DB::raw("COALESCE(tap.name, tap.name) AS primary_team_away_name"),
                'ep.ref_schedule AS primary_ref_schedule',
            ])
            ->first();
    }

    public function eventGroups()
    {
        return $this->hasMany(EventGroup::class, 'master_event_id', 'id');
    }
}
