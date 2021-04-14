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
        return self::where('provider_id', '!=',$primaryProviderId)
            ->whereNotIn('id', function($notInUnmatched) {
                $notInUnmatched->select('data_id')
                    ->from('unmatched_data')
                    ->where('data_type', 'team');
            })
            ->whereNotIn('id', function($notInTeamGroups) use ($primaryProviderId) {
                $notInTeamGroups->select('team_id')
                    ->from('team_groups')
                    ->join('teams')
                    ->where('provider_id', '!=', $primaryProviderId);
            })
            ->select('id', 'provider_id')
            ->get();
    }
    public static function getAllActiveNotExistInPivotByProviderId($providerId)
    {
        return self::where('provider_id', $providerId)->doesntHave('teamGroup')->get();
    }

    public function teamGroup()
    {
        return $this->hasOne(TeamGroup::class, 'team_id', 'id');
    }
}
