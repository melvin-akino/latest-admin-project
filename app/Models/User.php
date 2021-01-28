<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{UserWallet, Currency, OAuthToken};
use Illuminate\Support\Arr;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class User extends Model
{
    use LogsActivity;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'firstname',
        'lastname',
        'currency_id',
        'status',
        'uuid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static $logAttributes = ['name', 'email', 'firstname', 'lastname', 'currency_id', 'status'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Accounts';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." user: ".request()->email;
    }

    public static function getAll()
    {
      $users = self::join('currency as c', 'c.id', 'users.currency_id')
          ->select([
            'users.id',
            'email',
            'firstname',
            'lastname',
            'status',
            'currency_id',
            'users.created_at',
            'users.updated_at',
            'uuid',
            'c.code as currency_code'
        ])
        ->orderBy('users.created_at', 'DESC')
        ->get()
        ->toArray();

        if ($users) 
        {   
            foreach($users as $key => $value)
            {
                $oauth = OAuthToken::getLastLogin($value['id']);
                $users[$key]['last_login'] = $oauth['last_login_date'];
            }
        }

        return !empty($users) ? $users : [];
    }

    public static function getUser($userId)
    {
        return self::where('id', $userId)->first();
    }

    public static function getUserByUuid($uuid)
    {
        return self::where('uuid', $uuid)
          ->join('currency as c', 'c.id', 'users.currency_id')
          ->select(['email', 'firstname', 'lastname', 'c.code as currency', 'uuid'])
          ->first();
    }
}
