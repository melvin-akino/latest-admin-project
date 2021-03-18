<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

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
     * @param  int          $page
     * @param  int          $limit
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getLeaguesByProvider(int $providerId, string $searchKey = '', int $page = 0, int $limit = 0, bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        $query = self::{$where}('id', function ($query) {
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
            ->orderBy('name');
        
        if ($page == 0 || $limit == 0) {
            return $query->get();
        } else {
            return $query->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();
        }
    }
}
