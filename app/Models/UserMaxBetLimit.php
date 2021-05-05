<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class UserMaxBetLimit extends Model
{
    use LogsActivity;

    protected $table = 'user_max_bet_limit';

    protected $fillable = [
        'user_id',
        'max_bet_limit'
    ];

    protected static $logAttributes = ['user_id', 'max_bet_limit'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Accounts';

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('action', ucfirst($eventName));
        $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName) . " user_id: " . request()->user_id . " MAX_BET_LIMIT: " . request()->max_bet_limit;
    }
}
