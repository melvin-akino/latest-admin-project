<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";

    public function OrderLog() {
        return $this->hasOne(App/Models/OrderLog::class, 'order_id', 'id');
    }

    public static function getAllOrders($providerAccountId) {
        return self::where('provider_account_id', $providerAccountId)
            ->whereNotNull('orders.bet_id')
            ->join('order_logs', 'orders.id', 'order_logs.order_id')
            ->join('provider_account_orders', 'order_logs.id', 'provider_account_orders.order_log_id')
            ->select(
                'provider_account_id',
                'actual_stake',
                'actual_profit_loss',
                'orders.created_at',
                'orders.settled_date'
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
