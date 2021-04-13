<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;

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

    public function leagueGroup() 
    {
        return $this->hasOne(LeagueGroup::class);
    }

    public function unmatchedData() 
    {
        return $this->hasOne(UnmatchedData::class);
    }

    /**
     * Get `leagues` data by Provider, also allowing to choose from `raw` or `existing match`
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

        return self::{$where}('id', function ($query) {
                $query->select('league_id')
                    ->from('league_groups');
            })
            ->when(!$grouped, function ($query) use ($providerId) {
                $query->whereIn('id', function ($where) use ($providerId) {
                    $where->select('data_id')
                        ->from('unmatched_data')
                        ->where('data_type', 'league')
                        ->where('provider_id', $providerId);
                });
            })
            ->where('provider_id', $providerId)
            ->where('name', 'ILIKE', '%'.$searchKey.'%')
            ->select('id', 'sport_id', 'provider_id', 'name')
            ->orderBy('name', $sortOrder)
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
            ->whereNotIn('id', function($notInLeagueGroups) use ($primaryProviderId) {
                $notInLeagueGroups->select('league_id')
                    ->from('league_groups')
                    ->join('leagues')
                    ->where('provider_id', '!=', $primaryProviderId);
            })
            ->select('id', 'provider_id')
            ->get();
    }
}
