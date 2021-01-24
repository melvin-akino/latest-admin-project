<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

class AdminUser extends Authenticatable
{
    use HasApiTokens, Notifiable, LogsActivity;
    protected $table = 'admin_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status'
    ];

    protected $hidden = [
      'password',
      'remember_token'
    ];

    protected static $logAttributes = ['name', 'email', 'status'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Admin Users';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return ucfirst($eventName)." admin user: ".request()->email;
    }

    public static function getAll()
    {
        return self::select([
            'id',
            'name',
            'email',
            'status',
            'created_at'
        ])
        ->orderBy('created_at', 'DESC')
        ->get()
        ->toArray();
    }
}
