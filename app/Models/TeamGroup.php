<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class TeamGroup extends Model
{
    use LogsActivity;

    protected $table = "team_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_team_id',
        'team_id',
    ];

    public $timestamps = false;

    public $incrementing = false;

    protected static $logName = 'Teams Matching';

    protected static $logAttributes = [
        'master_team_id',
        'team_id',
    ];

    protected static $logOnlyDirty = false;

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('attributes', [
            'master_team_id' => $this->master_team_id,
            'team_id'        => $this->team_id,
        ]);
        $activity->properties = $activity->properties->put('action', ucfirst($eventName));
        $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Matched Raw Team ID " . $this->team_id . " to " . $this->master_team_id;
    }

    public static function getByTeamId($teamId)
    {
        return self::where('team_id', $teamId)->get();
    }

    public function teams()
    {
        return $this->belongsTo(Team::class, 'id', 'team_id');
    }

    public function masterTeams()
    {
        return $this->belongsTo(MasterTeam::class, 'id', 'master_team_id');
    }

}
