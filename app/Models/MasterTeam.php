<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;

class MasterTeam extends Model
{
    use SoftDeletes;

    protected $table = 'master_teams';

    protected $fillable = [
        'sport_id',
        'name',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function teamGroups()
    {
        return $this->hasMany(TeamGroup::class, 'master_team_id', 'id');
    }

    public static function getMasterTeams($providerId, $searchKey = '', string $sortOrder = 'asc') 
    {
        return self::join('team_groups as tg', 'master_teams.id', 'tg.master_team_id')
                  ->join('teams as t', 't.id', 'tg.team_id')
                  ->where('t.provider_id', $providerId)
                  ->where(DB::raw('COALESCE(master_teams.name, t.name)'), 'ILIKE', '%'.$searchKey.'%')
                  ->select('master_teams.id', DB::raw('COALESCE(master_teams.name, t.name) as master_team_name'), 'master_teams.name as alias')
                  ->orderBy('t.name', $sortOrder);
    }
}
