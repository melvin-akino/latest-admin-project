<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table = "order_logs";

    public function ProviderAccountOrder() {
        return $this->hasOne(App\Models\ProviderAccountOrder::class, 'order_log_id', 'id');
    }

}
