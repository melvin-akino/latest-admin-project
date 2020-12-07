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
}
