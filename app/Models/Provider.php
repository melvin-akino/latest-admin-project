<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'providers';

    protected $fillable = [
        'name',
        'alias',
        'punter_percentage',
        'is_enabled',
        'currency_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
