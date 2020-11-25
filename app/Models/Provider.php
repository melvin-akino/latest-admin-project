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
        'priority',
        'is_enabled'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function getAllProviders()
    {
        return self::orderBy('priority', 'asc')->orderBy('id', 'asc')->get()->toArray();
    }

    public static function getIdFromAlias($alias)
    {
        $query = self::where('alias', strtoupper($alias));

        if ($query->exists()) {
            return $query->first()->id;
        }
    }
}
