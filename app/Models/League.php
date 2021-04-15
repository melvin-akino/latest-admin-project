<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use App\Models\LeagueGroup;

class League extends Model
{
    use SoftDeletes;

    protected $table = 'leagues';

    protected $fillable = [
        'sport_id',
        'provider_id',
        'name',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get `leagues` data by Provider, also allowing to choose from `raw` or `existing match`
     * 
     * @param  int          $providerId
     * @param  string       $searchKey
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getLeagues($providerId, bool $grouped = true, string $searchKey = '', string $sortOrder = 'asc')
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return self::{$where}('leagues.id', function ($query) {
                $query->select('league_id')
                    ->from('league_groups');
            })
            ->join('providers as p', 'p.id', 'leagues.provider_id')
            ->when(!$grouped, function ($query) use ($providerId) {
                $query->whereIn('leagues.id', function ($where) use ($providerId) {
                    $where->select('data_id')
                    ->from('unmatched_data')
                    ->where('data_type', 'league')
                    ->when($providerId, function ($query) use ($providerId) {
                        $query->where('provider_id', $providerId);
                    });
                });
            })
            ->when($providerId, function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('leagues.name', 'ILIKE', '%'.$searchKey.'%')
            ->select('leagues.id', 'leagues.name', 'provider_id', 'p.alias as provider')
            ->orderBy('leagues.name', $sortOrder)
            ->get();
    }

    public static function getMatchedLeaguesById(int $id) {
        return self::join('league_groups as lg', 'leagues.id', 'lg.league_id')
              ->join('master_leagues as ml', 'ml.id', 'lg.master_league_id')
              ->join('providers as p', 'p.id', 'leagues.provider_id')
              ->where('ml.id', $id)
              ->select('leagues.id', 'leagues.name', 'provider_id', 'p.alias as provider')
              ->orderBy('leagues.id')
              ->get();
    }

    public static function getAllOtherProviderUnmatchedLeagues(int $primaryProviderId)
    {
        return self::where('provider_id', '!=',$primaryProviderId)
            ->whereNotIn('id', function($notInUnmatched) {
                $notInUnmatched->select('data_id')
                    ->from('unmatched_data')
                    ->where('data_type', 'league');
            })
            ->whereNotIn('id', function($notInLeagueGroup) use ($primaryProviderId) {
                $notInLeagueGroup->select('league_id')
                    ->from('league_groups')
                    ->join('leagues','leagues.id','league_groups.league_id')
                    ->where('provider_id', '!=', $primaryProviderId);
            })
            ->select('id', 'provider_id')
            ->get()
            ->toArray();
    }

    public static function getMasterLeagueId($leagueName, int $sportId, int $primaryProviderId)
    {
        return self::select('master_league_id')
            ->join('league_groups', 'league_groups.league_id', 'id')
            ->where('name', $leagueName)
            ->where('sport_id', $sportId)
            ->where('provider_id', $primaryProviderId)
            ->first();
            
    }

    public static function getAllActiveNotExistInPivotByProviderId($providerId)
    {
        return self::where('provider_id', $providerId)->doesntHave('leagueGroup')->get();
    }

    public function leagueGroup()
    {
        return $this->hasOne(LeagueGroup::class, 'league_id', 'id');
    }
}
