<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMarketGroup extends Model
{
    protected $table = "event_market_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_event_market_id',
        'event_market_id',
    ];

    public $timestamps = false;
    public $incrementing = false;
}
