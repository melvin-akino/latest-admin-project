<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSettlement extends Model
{
    protected $table = "admin_settlements";

    protected $fillable = [
        'reason',
        'payload',
        'bet_id',
        'processed',
    ];
}
