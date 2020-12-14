<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{UserWallet, Currency};
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
        return self::join('wallet', 'wallet.user_id', 'users.id')
          ->join('currency', 'currency.id', 'users.currency_id')
          ->select([
            'users.id',
            'email',
            'firstname',
            'lastname',
            'wallet.balance as credits',
            'currency.code as currency',
            'status',
            'users.created_at',
            'users.updated_at'
        ])
        ->orderBy('created_at', 'DESC')
        ->get()
        ->toArray();
    }
}
