<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMarket extends Model
{
    protected $table = "event_markets";

    public static function getAllActiveNotExistInPivotByProviderId($providerId)
    {
        return self::where('provider_id', $providerId)->doesntHave('eventMarketGroup')->get();
    }

    public function eventMarketGroup()
    {
        return $this->hasOne(EventMarketGroup::class, 'event_market_id', 'id');
    }
}
