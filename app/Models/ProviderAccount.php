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
        'uuid'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = ['username', 'type', 'punter_percentage', 'provider_id', 'is_enabled', 'is_idle'];

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

    public static function getProviderAccounts($providerId) 
    {
      $where = null;        
      if (!empty($providerId)) {
        $where = ['provider_id' => $providerId];
      }
        $accounts = self::where($where)
          ->join('providers as p', 'p.id', 'provider_id')
          ->join('currency as c', 'c.id', 'p.currency_id')
          ->select([
            'provider_accounts.id',
            'username',
            'password',
            'type',
            'provider_accounts.punter_percentage',
            'provider_accounts.is_enabled',
            'is_idle',
            'provider_id',
            'p.currency_id',
            'c.code as currency',
            'uuid'
          ])                                    
          ->orderBy('provider_accounts.created_at', 'DESC')
          ->get()
          ->toArray();

      $data = [];
        if (!empty($accounts)) {
            foreach($accounts as $account) {
                $data['data'][] = [
                    'id'                => $account['id'],
                    'username'          => $account['username'],
                    'password'          => $account['password'],
                    'type'              => $account['type'],
                    'punter_percentage' => $account['punter_percentage'],
                    'is_enabled'        => $account['is_enabled'],
                    'is_idle'           => $account['is_idle'],
                    'provider_id'       => $account['provider_id'],
                    'currency_id'       => $account['currency_id'],
                    'currency'          => $account['currency'],
                    'uuid'              => $account['uuid']
                ];
            }
        }
      return $data;
    }

    public static function getProviderAccountByUuid($uuid)
    {
        return self::where('uuid', $uuid)
          ->join('providers as p', 'p.id', 'provider_id')
          ->join('currency as c', 'c.id', 'p.currency_id')
          ->select(['username', 'c.code as currency', 'uuid'])
          ->first();
    }
}
