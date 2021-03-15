<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class MasterLeague extends Model
{
    use SoftDeletes;

    protected $table = 'master_leagues';

    protected $fillable = [
        'sport_id',
        'name',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}