<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

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
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getTeamsByProvider(int $providerId, bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return self::{$where}('id', function ($query) {
                $query->select('team_id')
                    ->from('team_groups');
            })
            ->when(!$grouped, function ($query) use ($providerId) {
                $query->whereIn('id', function ($where) use ($providerId) {
                    $where->select('data_id')
                        ->from('unmatched_data')
                        ->where('data_type', 'team')
                        ->where('provider_id', $providerId);
                });
            })
            ->where('provider_id', $providerId)
            ->orderBy('name')
            ->get();
    }
}
