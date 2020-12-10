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
        $providers = self::orderBy('priority', 'asc')->orderBy('id', 'asc')->get()->toArray();

        if (!empty($providers)) {
            foreach ($providers as $provider) {
                $data['data'][] = [
                    'id'            => $provider['id'],
                    'alias'         => $provider['alias'],
                    'currency_id'   => $provider['currency_id']
                ];
            }
        }

        return !empty($data) ? $data : [];
    }

    public static function getIdFromAlias($alias)
    {
        $query = self::where('alias', strtoupper($alias));

        if ($query->exists()) {
            return $query->first()->id;
        }
    }
}
