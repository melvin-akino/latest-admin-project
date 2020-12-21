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

    public static function getUserTransactions($request)
    {
        $dateFrom = null;
        $dateTo   = null;
        $where    = [];
        if ($request->date_from) 
        {
            $dateFrom = Carbon::createFromFormat("Y-m-d", $request->date_from, 'Etc/UTC')->setTimezone('Etc/UTC')->format("Y-m-d H:i:s");
        }
        if ($request->date_to) 
        {
            $dateTo = Carbon::createFromFormat("Y-m-d", $request->date_to, 'Etc/UTC')->setTimezone('Etc/UTC')->format("Y-m-d H:i:s");
        }
        if ($request->user_id) 
        {
            $where[] = ['user_id', $request->user_id];
        }
        if ($request->provider_id) 
        {
            $where[] = ['orders.provider_id', $request->provider_id];
        }
        if ($request->currency_id) 
        {
            $where[] = ['providers.currency_id', $request->currency_id];
        }
        return self::leftJoin('providers', 'providers.id', 'orders.provider_id')
                 ->leftJoin('odd_types AS ot', 'ot.id', 'orders.odd_type_id')
                 ->leftJoin('event_scores as es', 'es.master_event_unique_id', 'orders.master_event_unique_id')
                 ->leftJoin('provider_error_messages As pe','pe.id','orders.provider_error_message_id')
                 ->leftJoin('error_messages as em', 'em.id','pe.error_message_id')
                 ->leftJoin('users', 'users.id','orders.user_id')
                 ->select(
                     [
                         'orders.id',
                         'users.name as username',
                         'orders.bet_id',
                         'orders.bet_selection',
                         'orders.odds',
                         'orders.master_event_market_unique_id',
                         'orders.stake',
                         'orders.to_win',
                         'orders.created_at',
                         'orders.settled_date',
                         'orders.profit_loss',
                         'orders.status',
                         'orders.odd_label',
                         'orders.reason',
                         'orders.master_event_unique_id',
                         'es.score as current_score',
                         'ot.id AS odd_type_id',
                         'providers.alias',
                         'ml_bet_identifier',
                         'orders.final_score',
                         'orders.market_flag',
                         'orders.master_team_home_name',
                         'orders.master_team_away_name',
                         'em.error as multiline_error'
                     ]
                 )
                 ->where($where)
                 ->where('orders.status', '!=', 'FAILED')
                 ->when($dateFrom, function($query, $dateFrom) {
                   return $query->whereDate('orders.created_at', '>=', $dateFrom);
                 })
                 ->when($dateTo, function($query, $dateTo) {
                  return $query->whereDate('orders.created_at', '<=', $dateTo);
                 })
                 ->orderBy('orders.created_at', 'desc')
                 ->get()
                 ->toArray();
    }
}
