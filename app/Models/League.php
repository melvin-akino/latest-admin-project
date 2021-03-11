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
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getLeaguesByProvider(int $providerId, bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return self::{$where}('id', function ($query) {
                $query->select('league_id')
                    ->from('league_groups');
            })
            ->where('provider_id', $providerId)
            ->get();
    }
}
