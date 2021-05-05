<?php

use App\Models\{Order, UserBet, ProviderBet, ProviderBetLog, ProviderBetTransaction};
use Illuminate\Database\Seeder;

class BettingV2Seeder extends Seeder
{
    protected $settled = [
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
        $orders = Order::orderBy('id', 'ASC')->get();

        foreach ($orders AS $row) {
            $userBetId     = self::populateUserBets($row);
            $providerBetId = self::populateProviderBets($row, $userBetId);

            self::populateProviderBetLogs($providerBetId, $row->status);
        }

        $betIds      = [];
        $settledBets = Order::join('order_logs AS ol', 'orders.id', 'ol.order_id')
            ->leftJoin('provider_account_orders AS pao', 'pao.order_log_id', 'ol.id')
            ->whereIn('ol.status', $this->settled)
            ->select([
                'ol.id AS order_log_id',
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
    }

    private static function populateUserBets($orderData)
    {
        $id = UserBet::create([
            'user_id'                       => $orderData->user_id,
            'sport_id'                      => $orderData->sport_id,
            'odd_type_id'                   => $orderData->odd_type_id,
            'market_id'                     => $orderData->market_id,
            'status'                        => $orderData->status,
            'odds'                          => $orderData->odds,
            'stake'                         => $orderData->stake,
            'market_flag'                   => $orderData->market_flag,
            'order_expiry'                  => $orderData->order_expiry,
            'odd_label'                     => $orderData->odd_label,
            'ml_bet_identifier'             => $orderData->ml_bet_identifier,
            'score_on_bet'                  => $orderData->score_on_bet,
            'final_score'                   => $orderData->final_score,
            'master_event_market_unique_id' => $orderData->master_event_market_unique_id,
            'master_event_unique_id'        => $orderData->master_event_unique_id,
            'master_league_name'            => $orderData->master_league_name,
            'master_team_home_name'         => $orderData->master_team_home_name,
            'master_team_away_name'         => $orderData->master_team_away_name,
        ]);

        return $id;
    }

    private static function populateProviderBets($orderData, int $userBetId)
    {
        $id = ProviderBet::create([
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
    }

    private static function populateProviderBetLogs(int $providerBetId, string $status)
    {

        # Create Initial PENDING Record
        ProviderBetLog::create([
            'provider_bet_id' => $providerBetId,
            'status'          => 'PENDING',
        ]);

        if (in_array(strtoupper($status), $this->settled)) {
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
            'provider_bet_id'    => $data->order_log_id,
            'exchange_rate_id'   => $data->exchange_rate_id,
            'actual_stake'       => $data->actual_stake,
            'actual_to_win'      => $data->actual_to_win,
            'actual_profit_loss' => $data->actual_profit_loss,
            'exchange_rate'      => $data->exchange_rate,
        ]);
    }
}
