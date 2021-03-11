<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventGroup extends Model
{
    protected $table = "event_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_event_id',
        'event_id',
    ];

    public $timestamps = false;
    public $incrementing = false;
}
