<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
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
}