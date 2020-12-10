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

    public static function getAll() 
    {
        $configs = self::orderBy('id','asc')->get();
        foreach ($configs as $config) {
            $data['data'][] = [
                'id'                => $config['id'],
                'type'              => $config['type'],
                'value'             => $config['value'],
                'module'            => $config['module']
            ];
        }

        return !empty($data) ? $data : [];
    }
}