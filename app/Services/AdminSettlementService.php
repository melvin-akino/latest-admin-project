<?php

namespace App\Services;

use App\Http\Requests\AdminSettlementRequest;
use Illuminate\Support\Facades\{DB, Log};
use App\Models\AdminSettlement;
use Illuminate\Support\Str;
use Exception;

class AdminSettlementService
{
    public static function create(AdminSettlementRequest $request)
    {
        DB::beginTransaction();
        try {
                preg_match_all('!\d+!', $request->bet_id, $id);
                $requestId = (string) Str::uuid();
                //Generate kafka json payload here
                $payload = [
                    'request_id'    => $requestId,
                    'request_ts'    => getMilliseconds(),
                    'command'       => 'settlement',
                    'sub_command'   => 'transform',
                    'data' => [
                        'provider'      => $request->provider,
                        'sport'         => $request->sport,
                        'id'            => $id[0][0],
                        'username'      => $request->username,
                        'status'        => $request->status,
                        'odds'          => $request->odds,
                        'score'         => $request->score,
                        'stake'         => $request->stake,
                        'profit_loss'   => $request->pl,
                        'bet_id'        => $request->bet_id,
                        'reason'        => $request->reason
                    ]
                ];
                $settlement = new AdminSettlement([
                    'reason'    => $request->reason,
                    'payload'   => serialize(json_encode($payload)),
                    'bet_id'    => $request->bet_id,
                    'processed' => false
                ]);

                $settlement->updated_at = null;

            if ($settlement->save())
            {
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Settlement Request successfully added.',
                    'data'        => [
                        'reason' => $settlement->reason,
                        'bet_id' => $settlement->bet_id,
                        'created_at' => $settlement->created_at
                    ]
                ], 200);
            }
        }
        catch (Exception $e)
        {
            DB::rollBack();

            Log::info('Creating settlement for bet id: ' . $request->bet_id . ' failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    public function generate_settlement(AdminSettlementRequest $request) 
    {
        try {
            if (!empty($request)) {
                
                $data = $request->toArray();

                if (!empty($data)) {
                    preg_match_all('!\d+!', $data['bet_id'], $id);
                    $requestId = (string) Str::uuid();
                    //Generate kafka json payload here
                    $payload = [
                        'request_id'    => $requestId,
                        'request_ts'    => getMilliseconds(),
                        'command'       => 'settlement',
                        'sub_command'   => 'transform',
                        'data' => [
                            'provider'      => $data['provider'],
                            'sport'         => $data['sport'],
                            'id'            => $id[0][0],
                            'username'      => $data['username'],
                            'status'        => $data['status'],
                            'odds'          => $data['odds'],
                            'score'         => $data['score'],
                            'stake'         => $data['stake'],
                            'profit_loss'   => $data['pl'],
                            'bet_id'        => $data['bet_id'],
                            'reason'        => $data['reason']
                        ]
                    ];

                    $data['payload'] = serialize(json_encode($payload));
                    DB::beginTransaction();
                    if (AdminSettlement::create($data)) {
                        if (!in_array(env('APP_ENV'), ['testing'])) {
                           KafkaPush::dispatch('SCRAPING-SETTLEMENTS', $payload, $requestId);
                        }
                        $message = 'success';
                    }
                    DB::commit();                    
                }
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'data'        => $message
                ], 200);
            }            
        }  
        catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }
}
