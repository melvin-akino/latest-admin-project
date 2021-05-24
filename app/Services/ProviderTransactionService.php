<?php

namespace App\Services;

use App\Http\Requests\ProviderTransactionRequest;
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class ProviderTransactionService
{
    public static function getOrders(ProviderTransactionRequest $request)
    {
        try
        {    
            $dups = [];
            //filter out failed orders by default
            $where[] = ['pb.status', '<>', 'FAILED'];
            $whereDate = [];
            if ($request->created_from)
            {
                $where[] = ['ub.created_at', '>=', $request->created_from . ' 00:00:00'];
                $where[] = ['ub.created_at', '<=', $request->created_to . ' 23:59:59'];
            }

            if ($request->settled_from)
            {
                $where[] = ['pb.settled_at', '>=', $request->settled_from . ' 00:00:00'];
                $where[] = ['pb.settled_at', '<=', $request->settled_to . ' 23:59:59'];
            }

            if ($request->status)
            {
                if ($request->status == 'open') {
                    $where[] = ['pb.settled_date', null];
                }
                else {
                    $where[] = ['pb.settled_date', '<>', null];
                }
            }
            
            if ($request->provider)
            {
                $where[] = ['p.id', $request->provider_id];
            }
                
            if ($request->provider_account_id)
            {
                $where[] = ['pb.provider_account_id', $request->provider_account_id];
            }

            $data = DB::table('user_bets AS ub')
                ->join('provider_bets AS pb', 'pb.user_bet_id', 'ub.id')
                ->join('sports AS s', 's.id', 'ub.sport_id')
                ->join('providers AS p', 'p.id', 'pb.provider_id')
                ->join('provider_accounts AS pa', 'pa.id', 'pb.provider_account_id')
                ->join('users AS u', 'u.id', 'ub.user_id')
                ->join('provider_bet_transactions AS pbt', 'pbt.provider_bet_id', 'pb.id')
                ->join('odd_types AS ot', 'ot.id', 'ub.odd_type_id')
                ->join('sport_odd_type AS sot', 'sot.odd_type_id', 'ot.id')
                ->where($where)
                ->orderBy('ub.id', 'DESC')
                ->orderBy('pbt.id', 'DESC')
                ->distinct()
                ->get([
                    'ub.id',
                    'pbt.id AS provider_bet_transaction_id',
                    'p.id as provider_id',
                    'p.alias as provider',
                    's.id as sport_id',
                    's.sport',
                    'u.email',
                    'ub.ml_bet_identifier',
                    'pb.bet_id',
                    'pa.username',
                    'ub.created_at',
                    'pb.status',
                    'pb.stake',
                    'pb.to_win',
                    'pb.profit_loss',
                    'pbt.actual_stake',
                    'pbt.actual_to_win',
                    'pbt.actual_profit_loss',
                    'ub.odds',
                    'ub.odds_label',
                    'ub.master_league_name',
                    'ub.master_team_home_name',
                    'ub.master_team_away_name',
                    'ub.market_flag',
                    'ub.score_on_bet',
                    'ub.final_score',
                    'ub.odd_type_id',
                    'sot.name as column_type',
                ]);

            $ouLabels = DB::table('odd_types')->where('type', 'LIKE', '%OU%')->pluck('id')->toArray();
            $oeLabels = DB::table('odd_types')->where('type', 'LIKE', '%OE%')->pluck('id')->toArray();

            foreach ($data as $row) {
                if (!in_array($row->id, $dups)) {
                    if (strtoupper($row->market_flag) == "DRAW") {
                        $teamname = "DRAW";
                    } else {
                        $objectKey = "master_team_" . strtolower($row->market_flag) . "_name";
                        $teamname  = $row->{$objectKey};
                    }

                    if (in_array($row->odd_type_id, $ouLabels)) {
                        $ou        = explode(' ', $row->odds_label)[0];
                        $teamname  = $ou == "O" ? "Over" : "Under";
                        $teamname .= " " . explode(' ', $row->odds_label)[1];
                    }

                    if (in_array($row->odd_type_id, $oeLabels)) {
                        $teamname  = $row->odds_label == "O" ? "Odd" : "Even";
                    }

                    $betSelection = implode("\n", [
                        $row->master_team_home_name . " vs " . $row->master_team_away_name,
                        $teamname . " @ " . $row->odds,
                        $row->column_type. " ". $row->odds_label ."(" . $row->score_on_bet .")"
                    ]);

                    if (in_array($row->odd_type_id, $ouLabels) || in_array($row->odd_type_id, $oeLabels)) {
                        $betPeriod    = strpos($row->column_type, "FT") !== false ? "FT " : (strpos($row->column_type, "HT") !== false ? "HT " : "");
                        $betSelection = implode("\n", [
                            $row->master_team_home_name . " vs " . $row->master_team_away_name,
                            $betPeriod . $teamname . " @ " . $row->odds ."(" . $row->score_on_bet .")"
                        ]);
                    }

                    $transactions[] = [
                        'email'                 => $row->email,
                        'bet_identifier'        => $row->ml_bet_identifier,
                        'provider_id'           => $row->provider_id,
                        'provider'              => $row->provider,
                        'sport_id'              => $row->sport_id,
                        'sport'                 => $row->sport,
                        'bet_id'                => $row->bet_id,
                        'bet_selection'         => nl2br($betSelection),
                        'username'              => $row->username,
                        'created_at'            => $row->created_at,
                        'status'                => $row->status,
                        'stake'                 => $row->stake,
                        'towin'                 => $row->to_win,
                        'profit_loss'           => $row->profit_loss,
                        'actual_stake'          => $row->actual_stake,
                        'actual_to_win'         => $row->actual_to_win,
                        'actual_profit_loss'    => $row->actual_profit_loss,
                        'odds'                  => $row->odds,
                        'odds_label'            => $row->odds_label
                    ];

                    $dups[] = $row->id;
                }
            }

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => !empty($transactions) ? $transactions : null
            ], 200);
        }
        catch (Exception $e)
        {
            Log::info('Viewing open orders failed.');
            Log::error($e->getMessage());
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
        
    }
}
