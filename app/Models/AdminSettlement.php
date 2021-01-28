<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class AdminSettlement extends Model
{
    use LogsActivity;
    protected $table = "admin_settlements";

    protected $fillable = [
        'reason',
        'payload',
        'bet_id',
        'processed',
    ];

    protected static $logAttributes = ['bet_id', 'reason', 'payload'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Settlement Request';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." settlement request for BET ID: ".request()->bet_id;
    }
}
