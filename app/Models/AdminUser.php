<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasApiTokens, Notifiable;
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
    ];

    public static function getAll()
    {
        return self::select([
            'id',
            'name',
            'email',
            'status'
        ])
        ->get()
        ->toArray();
    }
}
