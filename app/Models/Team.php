<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use App\Models\TeamGroup;

class Team extends Model
{
    use SoftDeletes;

    protected $table = 'teams';

    protected $fillable = [
        'provider_id',
        'name',
        'sport_id',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get `teams` data by Provider, also allowing to choose from `raw` or `existing match`
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

        return DB::table('teams as t')
            ->join('events as e', function($join) {
                $join->on('e.team_home_id', '=', 't.id')
                    ->orOn('e.team_away_id', '=', 't.id');
            })
            ->join('league_groups as lg', 'lg.league_id', 'e.league_id')
            ->{$where}('t.id', function ($q) {
                $q->select('team_id')
                    ->from('team_groups');
            })
            ->when(!$grouped, function ($q) use ($providerId) {
                $q->whereIn('t.id', function ($where) use ($providerId) {
                    $where->select('data_id')
                        ->from('unmatched_data')
                        ->where('data_type', 'team')
                        ->where('provider_id', $providerId);
                });
            })
            ->where('t.provider_id', $providerId)
            ->where('t.name', 'ILIKE', '%'.$searchKey.'%')
            ->whereNull('t.deleted_at')
            ->select(['t.id', 't.sport_id', 't.provider_id', 't.name', DB::raw('array_to_string(array_agg(lg.master_league_id), \',\') as master_league_ids')])
            ->groupBy('t.id')
            ->orderBy('name', $sortOrder)
            ->get();
    }

    public static function getAllOtherProviderUnmatchedTeams(int $primaryProviderId)
    {
        return self::where('provider_id', '!=', $primaryProviderId)
            ->whereNotIn('id', function($query) {
                return $query->select('team_id')
                    ->from('team_groups');
            })
            ->whereNull('deleted_at')
            ->select('id', 'provider_id')
            ->get();
    }

    public static function getMasterTeamId($teamName, int $sportId, int $primaryProviderId)
    {
        return self::select('master_team_id')
            ->join('team_groups', 'team_groups.team_id', 'id')
            ->where('name', $teamName)
            ->where('sport_id', $sportId)
            ->where('provider_id', $primaryProviderId)
            ->first();
            
    }
    public static function getAllActiveNotExistInPivotByProviderId($providerId)
    {
        return self::where('provider_id', $providerId)->doesntHave('teamGroup')->get();
    }

    public static function getPrimaryTeams($primaryProviderId)
    {
        return self::where('teams.provider_id', $primaryProviderId)
            ->join('team_groups', 'team_groups.team_id', 'teams.id')
            ->get()
            ->toArray();
    }

    public function teamGroup()
    {
        return $this->hasOne(TeamGroup::class, 'team_id', 'id');
    }

    public static function getMatchedTeamsByMasterTeamId(int $masterTeamId)
    {
        return self::join('team_groups as tg', 'tg.team_id', 'teams.id')
                ->join('providers as p', 'p.id', 'teams.provider_id')
                ->where('tg.master_team_id', $masterTeamId)
                ->select('teams.id', 'teams.name', 'provider_id', 'p.alias as provider', 'sport_id')
                ->orderBy('p.id', 'asc')
                ->get();
    }
}
