<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnmatchedData extends Model
{
    protected $table = "unmatched_data";

    protected $primaryKey = null;

    protected $fillable = [
        'data_type',
        'data_id',
        'provider_id'
    ];

    public $timestamps = false;
    public $incrementing = false;
}
