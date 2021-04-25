<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class LeagueGroup extends Model
{
    use LogsActivity;

    protected $table = "league_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_league_id',
        'league_id',
    ];

    public $timestamps = false;

    public $incrementing = false;

    protected static $logName = 'Leagues Matching';

    protected static $logAttributes = [
        'master_league_id',
        'league_id',
    ];

    protected static $logOnlyDirty = false;

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('attributes', [
            'master_league_id' => $this->master_league_id,
            'league_id'        => $this->league_id,
        ]);
        $activity->properties = $activity->properties->put('action', ucfirst($eventName));
        $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Matched Raw League ID " . $this->league_id . " to " . $this->master_league_id;
    }

    public static function getByLeagueId($leagueId)
    {
        return self::where('league_id', $leagueId)->get();
    }

    public static function getNonPrimaryLeagueIds($masterLeagueId, int $primaryProviderId) {
        return self::join('leagues as l', 'l.id', 'league_groups.league_id')
            ->when($masterLeagueId, function ($query) use ($masterLeagueId) {
              $query->where('league_groups.master_league_id', $masterLeagueId);
            })
            ->where('l.provider_id', '!=', $primaryProviderId)
            ->pluck('l.id');
    }

    public function leagues()
    {
        return $this->belongsTo(League::class, 'id', 'league_id');
    }

    public function masterLeagues()
    {
        return $this->belongsTo(MasterLeague::class, 'id', 'master_league_id');
    }

    public static function checkLeagueIfmatched($leagueId)
    {
        return self::where('league_id', $leagueId)->count();
    }
}
