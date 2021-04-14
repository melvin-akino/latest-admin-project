<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamGroup extends Model
{
    protected $table = "team_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_team_id',
        'team_id',
    ];

    public $timestamps = false;
    public $incrementing = false;

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
