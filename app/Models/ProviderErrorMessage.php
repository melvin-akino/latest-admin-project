<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class ProviderErrorMessage extends Model
{
    use LogsActivity;
    //
     protected $table = "provider_error_messages";
     protected $fillable = [
     	'message',
     	'error_message_id',
         'retry_type_id',
         'odds_have_changed'
     ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = ['message', 'error_message_id', 'retry_type_id', 'odds_have_changed'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Provider Errors';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." error: ".request()->message;
    }

    public static function getAll()
    {
        return self::join('error_messages as em', 'em.id', 'error_message_id')
            ->select(
                'provider_error_messages.id',
                'message',
                'error_message_id',
                'em.error',
                'odds_have_changed',
                'retry_type_id'
            )
            ->orderBy('provider_error_messages.created_at', 'desc')
            ->get()
            ->toArray();
    }
    

}

