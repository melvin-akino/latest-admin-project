<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    public static function getMatchingLogs($searchKey = null)
    {
        $query = self::whereIn('log_name', ['Leagues Matching', 'Events Matching']);

        if ($searchKey) {
            $query = $query->where('log_name', 'ILIKE', ucfirst($searchKey) . " Matching");
        }

        foreach ($query->get() AS $row) {
            $props = json_decode($row->properties);

            if (stripos($row->log_name, 'leagues') !== false) {
                //
            } else if (stripos($row->log_name, 'events') !== false) {
                if (($row->action != "Deleted") && !EventGroup::where('master_event_id', $props->attributes->master_event_id)->where('event_id', $props->attributes->event_id)->count()) {
                    unset($row);
                }
            }
        }

        return $query;
    }

    /**
     * $matchingType - ['league', 'team', 'event']
     */
    public static function deleteMatchingLogsOfMasterData($matchingType, $masterId) {
        $logName = [
            'league' => 'Leagues Matching',
            'team'   => 'Teams Matching',
            'event'  => 'Events Matching'
        ];

        self::whereRaw('log_name LIKE \''.$logName[$matchingType].'\'')
            ->whereRaw('properties::text ILIKE \'%master_'.$matchingType.'_id":'.$masterId.',%\'')
            ->delete();
    }
}
