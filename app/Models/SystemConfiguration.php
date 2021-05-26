<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;
use Exception;

class SystemConfiguration extends Model
{
    use LogsActivity;

    protected $table = 'system_configurations';

    protected $fillable = [
        'type',
        'value',
        'module'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = ['type', 'value', 'module'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'System Configurations';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." system configuration: ".request()->type;
    }

    public static function getAll() 
    {
        $configs = self::orderBy('id','asc')->get();
        foreach ($configs as $config) {
            $data['data'][] = [
                'id'                => $config['id'],
                'type'              => $config['type'],
                'value'             => $config['value'],
                'module'            => $config['module']
            ];
        }

        return !empty($data) ? $data : [];
    }

    /**
     * Get column `value` from parameter `type`
     * 
     * @param  string $type
     * 
     * @return string|int|float
     */
    public static function getValueByType(string $type)
    {
        $query = self::where('type', $type);

        if ($query->count()) {
            return $query->first()->value;
        } else {
            throw new Exception('Type does not exist.');
        }
    }

    public static function getSystemConfigurationValue(string $type, string $module = "")
    {
        $data = self::where('type', $type);

        if ($module != "") {
            $data = $data->where('module', $module);
        }

        return $data->first();
    }
}