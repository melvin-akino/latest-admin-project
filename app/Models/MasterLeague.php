<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use App\Models\{LeagueGroup, SystemConfiguration AS SC, Provider};

class MasterLeague extends Model
{
    use SoftDeletes;

    protected $table = 'master_leagues';

    protected $fillable = [
        'sport_id',
        'name',
        'is_priority'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function leagueGroups()
    {
        return $this->hasMany(LeagueGroup::class, 'master_league_id', 'id');
    }

    public static function getLeagueBaseName(int $masterId)
    {
        $primaryProviderId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        $query = self::join('league_groups AS lg', 'lg.master_league_id', 'master_leagues.id')
            ->join('leagues AS l', function ($join) use ($primaryProviderId) {
                $join->on('l.id', 'lg.league_id');
                $join->where('l.provider_id', $primaryProviderId);
            })
            ->where('master_leagues.id', $masterId)
            ->select([
                DB::raw("COALESCE(master_leagues.name, l.name) AS leaguename"),
                DB::raw("'" . SC::getValueByType('PRIMARY_PROVIDER') . "' AS provider")
            ])
            ->first();

        return $query;
    }

    public static function getSideBarLeaguesBySportAndGameSchedule(int $sportId, int $primaryProviderId, int $maxMissingCount, string $gameSchedule)
    {
        $sql = "SELECT name, COUNT(name) AS match_count, master_league_id
            FROM (
                SELECT COALESCE(ml.name, l.name) as name, ml.id AS master_league_id
                FROM master_leagues as ml
                JOIN league_groups as lg
                    ON lg.master_league_id = ml.id
                JOIN leagues as l
                    ON lg.league_id = l.id
                JOIN master_events as me
                    ON me.master_league_id = ml.id
                JOIN event_groups as eg
                    ON eg.master_event_id = me.id
                WHERE EXISTS (
                    SELECT 1
                    FROM events as e
                    WHERE e.id = eg.event_id
                        AND e.deleted_at is null
                        AND game_schedule = '{$gameSchedule}'
                        AND provider_id = {$primaryProviderId}
                        AND missing_count <= {$maxMissingCount}
                )
                AND provider_id = {$primaryProviderId}
                AND l.sport_id = {$sportId}
            ) as sidebar_leagues
            GROUP BY name, master_league_id
            ORDER BY name";

        return DB::select($sql);
    }

    public static function getMasterLeaguesForUnmatching($providerId, $searchKey = '', string $sortOrder = 'asc') 
    {
        return self::join('league_groups as lg', 'master_leagues.id', 'lg.master_league_id')
                  ->join('leagues as l', 'l.id', 'lg.league_id')
                  ->where('l.provider_id', $providerId)
                  ->where(DB::raw('COALESCE(master_leagues.name, l.name)'), 'ILIKE', '%'.$searchKey.'%')
                  ->select('master_leagues.id', DB::raw('COALESCE(master_leagues.name, l.name) as master_league_name'), 'master_leagues.name as alias', 'is_priority')
                  ->orderBy('is_priority', 'desc')
                  ->orderBy('l.name', $sortOrder);
    }
}