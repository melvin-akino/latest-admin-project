<?php

use App\Models\{Order, Provider, UserBet, ProviderBet, ProviderBetLog, ProviderBetTransaction};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BettingV2Seeder extends Seeder
{
    protected static $settled = [
        'WIN',
        'WON',
        'LOSE',
        'LOSS',
        'HALF WIN',
        'HALF LOSE',
        'PUSH',
        'VOID',
        'DRAW',
        'CANCELLED',
        'REJECTED',
        'ABNORMAL BET',
        'REFUNDED',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (file_exists(storage_path('logs/monitor/database/laravel.log')) {
                unlink(storage_path('logs/monitor/database/laravel.log'));
            }

            $orders = Order::orderBy('id', 'ASC')->get();

            foreach ($orders AS $row) {
                $userBetId     = self::populateUserBets($row);
                $providerBetId = self::populateProviderBets($row, $userBetId);

                self::populateProviderBetLogs($providerBetId, $row->status);
            }

            $betIds      = [];
            $settledBets = Order::join('order_logs AS ol', function ($join) {
                    $join->on('orders.id', 'ol.order_id');
                    $join->whereNotNull(DB::raw('orders.settled_date'));
                    $join->whereNotNull('ol.settled_date');
                })
                ->leftJoin('provider_account_orders AS pao', 'pao.order_log_id', 'ol.id')
                ->leftJoin('providers AS p', 'p.id', 'orders.provider_id')
                ->leftJoin('user_provider_configurations AS upc', function ($join) {
                    $join->on('upc.user_id', 'orders.user_id');
                    $join->where('upc.provider_id', DB::raw('orders.provider_id'));
                })
                ->whereIn('ol.status', self::$settled)
                ->groupBy([
                    DB::raw('COALESCE(upc.punter_percentage, p.punter_percentage)'),
                    'orders.id',
                    'ol.bet_id',
                    'exchange_rate_id',
                    'actual_stake',
                    'actual_to_win',
                    'actual_profit_loss',
                    'exchange_rate',
                ])
                ->orderBy('o.id', 'ASC')
                ->select([
                    DB::raw('COALESCE(upc.punter_percentage, p.punter_percentage) AS punter_percentage'),
                    'orders.id AS order_id',
                    'ol.bet_id',
                    'exchange_rate_id',
                    'actual_stake',
                    'actual_to_win',
                    'actual_profit_loss',
                    'exchange_rate',
                ])
                ->get();

            foreach ($settledBets AS $row) {
                if (!in_array($row->bet_id, $betIds)) {
                    self::populateProviderBetTransactions($row);

                    $betIds[] = $row->bet_id;
                }
            }
        } catch (Exception $e) {
            \Log::channel('monitor_database')->error(json_encode($e));
        }
    }

    private static function populateUserBets($orderData)
    {
        $insert = UserBet::create([
            'user_id'                => $orderData->user_id,
            'sport_id'               => $orderData->sport_id,
            'odd_type_id'            => $orderData->odd_type_id,
            'market_id'              => $orderData->market_id,
            'status'                 => $orderData->status,
            'odds'                   => $orderData->odds,
            'stake'                  => $orderData->stake,
            'market_flag'            => $orderData->market_flag,
            'order_expiry'           => $orderData->order_expiry,
            'odds_label'             => $orderData->odd_label,
            'ml_bet_identifier'      => $orderData->ml_bet_identifier,
            'score_on_bet'           => $orderData->score_on_bet,
            'final_score'            => $orderData->final_score,
            'mem_uid'                => $orderData->master_event_market_unique_id,
            'master_event_unique_id' => $orderData->master_event_unique_id,
            'master_league_name'     => $orderData->master_league_name,
            'master_team_home_name'  => $orderData->master_team_home_name,
            'master_team_away_name'  => $orderData->master_team_away_name,
        ]);

        return $insert->id;
    }

    private static function populateProviderBets($orderData, int $userBetId)
    {
        $insert = ProviderBet::create([
            'user_bet_id'               => $userBetId,
            'provider_id'               => $orderData->provider_id,
            'provider_account_id'       => $orderData->provider_account_id,
            'provider_error_message_id' => $orderData->provider_error_message_id,
            'status'                    => $orderData->status,
            'bet_id'                    => $orderData->bet_id,
            'odds'                      => $orderData->odds,
            'stake'                     => $orderData->stake,
            'to_win'                    => $orderData->to_win,
            'profit_loss'               => $orderData->profit_loss,
            'reason'                    => $orderData->reason,
            'settled_date'              => $orderData->settled_date,
        ]);

        \Log::channel('monitor_database')->debug(json_encode([
            'qwe' => [
                'user_bet_id'               => $userBetId,
                'provider_id'               => $orderData->provider_id,
                'provider_account_id'       => $orderData->provider_account_id,
                'provider_error_message_id' => $orderData->provider_error_message_id,
                'status'                    => $orderData->status,
                'bet_id'                    => $orderData->bet_id,
                'odds'                      => $orderData->odds,
                'stake'                     => $orderData->stake,
                'to_win'                    => $orderData->to_win,
                'profit_loss'               => $orderData->profit_loss,
                'reason'                    => $orderData->reason,
                'settled_date'              => $orderData->settled_date,
            ],
            'asd' => $insert->id,
        ]));

        return $insert->id;
    }

    private static function populateProviderBetLogs(int $providerBetId, string $status)
    {

        # Create Initial PENDING Record
        ProviderBetLog::create([
            'provider_bet_id' => $providerBetId,
            'status'          => 'PENDING',
        ]);

        if (in_array(strtoupper($status), self::$settled)) {
            # Create SUCCESS Status Record for Settled Bets
            ProviderBetLog::create([
                'provider_bet_id' => $providerBetId,
                'status'          => 'SUCCESS',
            ]);
        }

        # Create SUCCESS/FAILED/Settled Status Record
        ProviderBetLog::create([
            'provider_bet_id' => $providerBetId,
            'status'          => strtoupper($status),
        ]);
    }

    private static function populateProviderBetTransactions($data)
    {
        ProviderBetTransaction::create([
            'provider_bet_id'    => $data->order_id,
            'exchange_rate_id'   => $data->exchange_rate_id,
            'actual_stake'       => $data->actual_stake,
            'actual_to_win'      => $data->actual_to_win,
            'actual_profit_loss' => $data->actual_profit_loss,
            'exchange_rate'      => $data->exchange_rate,
            'punter_percentage'  => $data->punter_percentage,
        ]);
    }
}
