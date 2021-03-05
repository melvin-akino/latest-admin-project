<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    protected $table = "orders";

    public function OrderLog() {
        return $this->hasOne(App\Models\OrderLog::class, 'order_id', 'id');
    }
}
