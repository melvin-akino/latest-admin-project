<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $table    = "sports";

    public static function getAllActive()
    {
        return self::where('is_enabled', true)->get();
    }
}
