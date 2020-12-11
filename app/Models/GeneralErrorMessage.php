<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralErrorMessage extends Model
{
    protected $table = "error_messages";

    protected $fillable = [
        'error'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getAll() 
    {
        $errorMessages = self::all()->toArray();
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $error) {
                $data['data'][] = [
                    'id'                => $error['id'],
                    'error'             => $error['error']
                ];
            }
        }

        return !empty($data) ? $data : [];
    }
}
