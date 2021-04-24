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

    public static function saklapNaman(int $masterId, int $rawId)
    {
        $primaryProviderId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        $query = self::find($masterId)
            ->join('event_groups AS egp', 'egp.master_event_id', 'master_events.id')
            ->join('events AS ep', function ($join) use ($primaryProviderId) {
                $join->on('ep.id', 'egp.event_id');
                $join->where('ep.provider_id', $primaryProviderId);
            })
            ->join('leagues AS lp', 'lp.id', 'ep.league_id')
            ->join('teams AS thp', 'thp.id', 'ep.team_home_id')
            ->join('teams AS tap', 'tap.id', 'ep.team_away_id')
            ->join('providers AS pp', 'ep.provider_id', 'pp.id')
            ->join('event_groups AS egs', 'egs.master_event_id', 'master_events.id')
            ->join('events AS es', function ($join) use ($rawId) {
                $join->on('es.id', 'egs.event_id');
                $join->where('es.id', $rawId);
            })
            ->join('leagues AS ls', 'ls.id', 'es.league_id')
            ->join('teams AS ths', 'ths.id', 'es.team_home_id')
            ->join('teams AS tas', 'tas.id', 'es.team_away_id')
            ->join('providers AS ps', 'es.provider_id', 'ps.id')
            ->select([
                'pp.alias AS primary_alias',
                'ps.alias AS secondary_alias',
                'ep.event_identifier AS primary_event_id',
                'es.event_identifier AS secondary_event_id',
                DB::raw("COALESCE(lp.name, lp.name) AS primary_league_name"),
                DB::raw("ls.name AS secondary_league_name"),
                DB::raw("COALESCE(thp.name, thp.name) AS primary_team_home_name"),
                DB::raw("ths.name AS secondary_team_home_name"),
                DB::raw("COALESCE(tap.name, tap.name) AS primary_team_away_name"),
                DB::raw("tas.name AS secondary_team_away_name"),
                'ep.ref_schedule AS primary_ref_schedule',
                'es.ref_schedule AS secondary_ref_schedule',
            ])
            ->first();

        return $query;
    }

    public function eventGroups()
    {
        return $this->hasMany(EventGroup::class, 'master_event_id', 'id');
    }
}
