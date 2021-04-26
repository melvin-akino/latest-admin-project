<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class EventGroup extends Model
{
    use LogsActivity;

    protected $table = "event_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_event_id',
        'event_id',
    ];

    public $timestamps = false;

    public $incrementing = false;

    protected static $logName = 'Events Matching';

    protected static $logAttributes = [
        'master_event_id',
        'event_id',
    ];

    protected static $logOnlyDirty = false;

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('attributes', [
            'master_event_id' => $this->master_event_id,
            'event_id'        => $this->event_id,
        ]);
        $activity->properties = $activity->properties->put('action', ucfirst($eventName));
        $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Matched Raw Event ID " . $this->event_id . " to " . $this->master_event_id;
    }

    public static function checkMatchExist(int $masterId, int $rawId): bool
    {
        return self::where('master_event_id', $masterId)    
            ->where('event_id', $rawId)
            ->count();
    }

    public static function getByEventId($eventId)
    {
        return self::where('event_id', $eventId)->get();
    }

    public function events()
    {
        return $this->belongsTo(Event::class, 'id', 'event_id');
    }

    public function masterEvents()
    {
        return $this->belongsTo(MasterEvent::class, 'id', 'master_event_id');
    }
}
