<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class ProviderAccount extends Model
{
    protected $table = "provider_accounts";

    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'username',
        'password',
        'type',
        'punter_percentage',
        'provider_id',
        'is_enabled',
        'is_idle',
        'deleted_at',
        'uuid',
        'line',
        'usage',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = ['username', 'type', 'punter_percentage', 'provider_id', 'is_enabled', 'is_idle', 'line', 'usage'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Provider Accounts';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." provider account: ".request()->username;
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }
}
