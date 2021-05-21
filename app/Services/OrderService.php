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
            $orders = Order::where('provider_account_id', $request->id)
                ->whereNotNull('orders.bet_id')
                ->join('order_logs', 'orders.id', 'order_logs.order_id')
                ->join('provider_accounts', 'orders.provider_account_id', 'provider_accounts.id')
                ->join('provider_account_orders', 'order_logs.id', 'provider_account_orders.order_log_id')
                ->select(
                    'orders.id',
                    'provider_account_id',
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
                $where[] = ['pb.provider_id', $request->provider_id];
            }
            if ($request->currency_id) 
            {
                $where[] = ['p.currency_id', $request->currency_id];
            }
            $bets = DB::table('user_bets as ub')
                    ->join('provider_bets as pb', 'pb.user_bet_id', 'ub.id')
                    ->join('providers as p', 'pb.provider_id', 'p.id')
                    ->join('users as u', 'ub.user_id', 'u.id')
                    ->join('odd_types as ot', 'ot.id', 'ub.odd_type_id')
                    ->join('sport_odd_type as sot', 'ot.id', 'sot.odd_type_id')
                    ->select(
                        [
                            'ub.id',
                            'u.name as username',
                            'ml_bet_identifier',
                            'ub.created_at',
                            'ub.odds',
                            'odds_label',
                            'ub.status',
                            'score_on_bet',
                            'final_score',
                            'ub.odd_type_id',
                            'sot.name as column_type',
                            'market_flag',
                            'master_league_name',
                            'master_team_home_name',
                            'master_team_away_name',
                            DB::raw("(SELECT SUM(stake) FROM provider_bets WHERE user_bet_id = ub.id AND status NOT IN ('PENDING', 'UNPLACED', 'FAILED', 'CANCELLED', 'REJECTED', 'VOID', 'ABNORMAL BET', 'REFUNDED')) as stake"),
                            DB::raw("(SELECT SUM(to_win) FROM provider_bets WHERE user_bet_id = ub.id AND status NOT IN ('PENDING', 'UNPLACED', 'FAILED', 'CANCELLED', 'REJECTED', 'VOID', 'ABNORMAL BET', 'REFUNDED')) as to_win"),
                            DB::raw("(SELECT SUM(profit_loss) FROM provider_bets WHERE user_bet_id = ub.id) as profit_loss"),
                            'pb.provider_id',
                            'p.currency_id'
                        ]
                    )
                    ->where($where)
                    ->where('sot.sport_id', DB::raw('ub.sport_id'))
                    ->when($dateFrom, function($query, $dateFrom) {
                        return $query->whereDate('ub.created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo, function($query, $dateTo) {
                        return $query->whereDate('ub.created_at', '<=', $dateTo);
                    })
                    ->orderBy('ub.created_at', 'desc')
                    ->distinct()
                    ->get()
                    ->toArray();

            $data = [];
            $ouLabels = DB::table('odd_types')->where('type', 'LIKE', '%OU%')->pluck('id')->toArray();
            $oeLabels = DB::table('odd_types')->where('type', 'LIKE', '%OE%')->pluck('id')->toArray();

            foreach($bets as $bet) {
                if (strtoupper($bet->market_flag) == "DRAW") {
                    $teamname = "DRAW";
                } else {
                    $objectKey = "master_team_" . strtolower($bet->market_flag) . "_name";
                    $teamname  = $bet->{$objectKey};
                }

                if (in_array($bet->odd_type_id, $ouLabels)) {
                    $ou        = explode(' ', $bet->odds_label)[0];
                    $teamname  = $ou == "O" ? "Over" : "Under";
                    $teamname .= " " . explode(' ', $bet->odds_label)[1];
                }

                if (in_array($bet->odd_type_id, $oeLabels)) {
                    $teamname  = $bet->odds_label == "O" ? "Odd" : "Even";
                }

                $betSelection     = implode("\n", [
                    $bet->master_team_home_name . " vs " . $bet->master_team_away_name,
                    $teamname . " @ " . $bet->odds,
                    $bet->column_type. " ". $bet->odds_label ."(" . $bet->score_on_bet .")"
                ]);

                if (in_array($bet->odd_type_id, $ouLabels) || in_array($bet->odd_type_id, $oeLabels)) {
                    $betPeriod            = strpos($bet->column_type, "FT") !== false ? "FT " : (strpos($bet->column_type, "HT") !== false ? "HT " : "");
                    $betSelection         = implode("\n", [
                        $bet->master_team_home_name . " vs " . $bet->master_team_away_name,
                        $betPeriod . $teamname . " @ " . $bet->odds ."(" . $bet->score_on_bet .")"
                    ]);
                }

                $data[] = [
                    'bet_id'        => $bet->ml_bet_identifier,
                    'created_at'    => $bet->created_at,
                    'bet_selection' => nl2br($betSelection),
                    'username'      => $bet->username,
                    'stake'         => $bet->stake,
                    'odds'          => $bet->odds,
                    'to_win'        => $bet->to_win,
                    'status'        => $bet->status,
                    'valid_stake'   => $bet->profit_loss ? abs($bet->profit_loss) : 0,
                    'profit_loss'   => $bet->profit_loss,
                    'provider_id'   => $bet->provider_id,
                    'currency_id'   => $bet->currency_id
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

