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

    public static function getMatchingLogs($searchKey, $page, $limit)
    {
        $query = self::where('log_name', 'ILIKE', '%Matching')
            ->whereIn('log_name', ['Leagues Matching', 'Events Matching']);

        if ($searchKey) {
            $query = $query->where('log_name', 'ILIKE', ucfirst($searchKey) . " Matching");
        }

        $query = $query->orderBy('id', 'DESC');

        return $query;
    }
}
