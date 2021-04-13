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
}
