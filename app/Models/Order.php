<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    protected $table = "orders";

    public function OrderLog() {
        return $this->hasOne(App/Models/OrderLog::class, 'order_id', 'id');
    }

    public static function getAllOrders($providerAccountId) {
        $orders = self::where('provider_account_id', $providerAccountId)
            ->whereNotNull('orders.bet_id')
            ->join('order_logs', 'orders.id', 'order_logs.order_id')
            ->join('provider_accounts', 'orders.provider_account_id', 'provider_accounts.id')
            ->join('provider_account_orders', 'order_logs.id', 'provider_account_orders.order_log_id')
            ->select(
                'provider_account_id',
                'actual_stake',
                'actual_profit_loss',
                'orders.created_at',
                'orders.settled_date',
                'provider_accounts.updated_at'
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        $data = [];
        if (!empty($orders)) {
            $pl = 0;
            $openOrders = 0;
            $lastBetDate = '';
            $providerAccountLastUpdate = '';
            foreach($orders as $key => $order) {
                if ($key == 0) {
                    $lastBetDate = $order['created_at'];
                    $providerAccountLastUpdate = $order['updated_at'];
                    $lastAction = 'Check Balance';
                }

                if ($order['settled_date'] != '') {
                    $pl += $order['actual_profit_loss'];

                    if (Carbon::create($providerAccountLastUpdate)->lte(Carbon::create($order['settled_date']))) {
                        $providerAccountLastUpdate = $order['settled_date'];
                        $lastAction = 'Settlement';
                    }
                }
                else {
                    $openOrders += $order['actual_stake'];
                    
                }
                
            }

            $data = [
                'provider_account_id' => $providerAccountId,
                'pl' => $pl,
                'open_orders' => $openOrders,
                'last_bet' => $lastBetDate,
                'last_scrape' => $providerAccountLastUpdate,
                'last_sync' => $lastAction
            ];
        }

        return $data;
    }

    public static function getOpenOrders($userId)
    {
        $lastBetDate = '-';
        $openOrdersSum = 0;

        $openOrders = self::where('user_id', $userId)
            ->whereNotNull('bet_id')
            ->select('stake', 'settled_date', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        if ($openOrders)
        {
            foreach($openOrders as $key=>$order) 
            {
                if ($key == 0) {
                    $lastBetDate = $order['created_at'];
                }
                if (empty($order['settled_date']))
                {
                    $openOrdersSum += $order['stake'];
                }
            }
        }

        return [
            'open_orders'   => $openOrdersSum,
            'last_bet'      => $lastBetDate
        ];
    }
}
