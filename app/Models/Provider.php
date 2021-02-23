<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class Provider extends Model
{
    protected $table = 'providers';
    use LogsActivity;

    protected $fillable = [
        'name',
        'alias',
        'punter_percentage',
        'is_enabled',
        'currency_id'
    ];

    protected static $logAttributes = ['name', 'alias', 'punter_percentage', 'is_enabled', 'currency_id'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Providers';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." providers: ".request()->name;
    }
}
