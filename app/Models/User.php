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
        return self::select([
            'id',
            'email',
            'firstname',
            'lastname',
            'status',
            'created_at',
            'updated_at'
        ])
        ->get()
        ->toArray();
    }
}
