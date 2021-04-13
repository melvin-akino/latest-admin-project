<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterEventMarket extends Model
{
    protected $table = "master_event_markets";

    protected $fillable = [
        'master_event_market_unique_id',
        'master_event_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function eventMarketGroups()
    {
        return $this->hasMany(EventMarketGroup::class, 'master_event_market_id', 'id');
    }
}
