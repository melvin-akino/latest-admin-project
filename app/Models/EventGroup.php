<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventGroup extends Model
{
    protected $table = "event_groups";

    protected $primaryKey = null;

    protected $fillable = [
        'master_event_id',
        'event_id',
    ];

    public $timestamps = false;
    public $incrementing = false;

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
