<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProviderErrorMessage extends Model
{
    //
     protected $table = "provider_error_messages";
     protected $fillable = [
     	'message',
     	'error_message_id'
     ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function getAll()
    {
        return self::join('error_messages as em', 'em.id', 'error_message_id')
            ->select(
                'provider_error_messages.id',
                'message',
                'em.error',
            )
            ->orderBy('provider_error_messages.created_at', 'desc')
            ->get()
            ->toArray();
    }
    

}

