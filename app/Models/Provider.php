<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;
use Exception;

class Provider extends Model
{
    use LogsActivity;

    protected $table = 'providers';

    protected $fillable = [
        'name',
        'alias',
        'punter_percentage',
        'is_enabled',
        'currency_id'
    ];

    protected $hidden = [
      'priority'
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

    /**
     * Get Provider `id` from parameter `alias`
     * 
     * @param  string $alias
     * 
     * @return int
     */
    public static function getIdFromAlias(string $alias)
    {
        $query = self::where('alias', strtoupper($alias));

        if ($query->count()) {
            return $query->first()->id;
        } else {
            throw new Exception('Provider Alias does not exist.');
        }
    }
}
