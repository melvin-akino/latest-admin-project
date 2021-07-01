<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;
use Exception;

class OrderService
{
    public static function getProviderOrders(Request $request) 
    {
        try 
        {
            $orders = Order::where('orders.provider_account_id', $request->id)
                ->whereNotNull('orders.bet_id')
                ->join('order_logs', 'orders.id', 'order_logs.order_id')
                ->join('provider_accounts', 'orders.provider_account_id', 'provider_accounts.id')
                ->join('provider_account_orders', 'order_logs.id', 'provider_account_orders.order_log_id')
                ->select(
                    'orders.id',
                    'orders.provider_account_id',
                    'actual_stake',
                    'actual_profit_loss',
                    'orders.created_at',
                    'orders.settled_date',
                    'provider_accounts.updated_at',
                    'orders.status'
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
                $dups = [];
                foreach($orders as $key => $order) {
                    if (!in_array($order['id'], $dups)) {
                        if ($key == 0) {
                            $lastBetDate = $order['created_at'];
                            $providerAccountLastUpdate = $order['updated_at'];
                            $lastAction = 'Check Balance';
                        }
        
                        if (!empty($order['settled_date'])) {
                            $pl += $order['actual_profit_loss'];
        
                            if (Carbon::create($providerAccountLastUpdate)->lte(Carbon::create($order['settled_date']))) {
                                $providerAccountLastUpdate = $order['settled_date'];
                                $lastAction = 'Settlement';
                            }
                        }
                        if (in_array($order['status'], ['SUCCESS', 'PENDING'])) {
                            $openOrders += $order['actual_stake'];                    
                        }
                        $dups[] = $order['id'];
                    }                
                }

                $data = [
                    'provider_account_id' => $request->providerAccountId,
                    'pl' => $pl,
                    'open_orders' => $openOrders,
                    'last_bet' => $lastBetDate,
                    'last_scrape' => $providerAccountLastUpdate,
                    'last_sync' => $lastAction
                ];
            }

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $data
            ], 200);
        }
        catch (Exception $e) 
        {
            DB::rollBack();
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }

    public static function getOpenOrders(Request $request)
    {
        try {    
            $lastBetDate = '-';
            $openOrdersSum = 0;

            $openOrders = Order::where('user_id', $request->userId)
                ->whereNotNull('bet_id')
                ->where('status', ['SUCCESS','PENDING'])
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

                    $openOrdersSum += $order['stake'];
                }
            }

            $data = [
                'open_orders'   => $openOrdersSum,
                'last_bet'      => $lastBetDate
            ];

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $data
            ], 200);
        }
        catch (Exception $e) 
        {
            DB::rollBack();
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }

    public static function getUserTransactions(Request $request)
    {
        try 
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
            $orders = Order::leftJoin('providers', 'providers.id', 'orders.provider_id')
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
                    
            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $orders
            ], 200);
        }
        catch (Exception $e) 
        {
            DB::rollBack();
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }

    public function update(OrderRequest $request)
    {
        DB::beginTransaction();
        try
        {
            $order = Order::where('id', $request->id)->first();
            $forUpdate = [
                'status'        => $request->status,        
                'profit_loss'   => $request->pl,
                'reason'        => $request->reason
            ];
            if ($order->update($forUpdate))
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Order successfully updated.',
                    'data'        => $order
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Updating order ' . $request->id . ' failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }
}

