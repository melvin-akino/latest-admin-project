<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{UserWallet, Currency, OAuthToken};
use Illuminate\Support\Arr;

class User extends Model
{
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
        'status'
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

    public static function getAll()
    {
      $users = self::select([
            'id',
            'email',
            'firstname',
            'lastname',
            'status',
            'currency_id',
            'created_at',
            'updated_at'
        ])
        ->orderBy('created_at', 'DESC')
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
}
