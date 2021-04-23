<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use App\Models\LeagueGroup;

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
}