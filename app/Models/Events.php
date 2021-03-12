<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use App\Models\{League, Team};

class Events extends Model
{
    use SoftDeletes;

    protected $table = "events";

    protected $fillable = [
        'master_event_id',
        'sport_id',
        'provider_id',
        'event_identifier',
        'league_id',
        'team_home_id',
        'team_away_id',
        'ref_schedule',
        'game_schedule',
        'deleted_at',
        'missing_count'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get `events` data by Provider, also allowing to choose from `raw` or `existing match`
     * 
     * @param  int          $providerId
     * @param  bool|boolean $grouped
     * 
     * @return object
     */
    public static function getEventsByProvider(int $providerId, bool $grouped = true)
    {
        $where = $grouped ? "whereIn" : "whereNotIn";

        return self::with('league', 'teamHome', 'teamAway')->{$where}('id', function ($query) {
                $query->select('event_id')
                    ->from('event_groups');
            })
            ->where('provider_id', $providerId)
            ->get();
    }

    public function league() {
        return $this->belongsTo(League::class);
    }

    public function teamHome() {
        return $this->belongsTo(Team::class, 'team_home_id', 'id');
    }

    public function teamAway() {
        return $this->belongsTo(Team::class, 'team_away_id', 'id');
    }
}
