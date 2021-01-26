<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class GeneralErrorMessage extends Model
{
    use LogsActivity;

    protected $table = "error_messages";

    protected $fillable = [
        'error'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected static $logAttributes = ['error'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'General Errors';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." error: ".request()->error;
    }

    public static function getAll() 
    {
        $errorMessages = self::all()->toArray();
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $error) {
                $data['data'][] = [
                    'id'                => $error['id'],
                    'error'             => $error['error']
                ];
            }
        }

        return !empty($data) ? $data : [];
    }
}
