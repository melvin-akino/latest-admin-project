<?php

namespace App\Services;

use App\Http\Requests\ProviderAccountRequest;
use App\Models\{Currency, ProviderAccount};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
class ProviderAccountService
{
    public static function getProviderAccounts(ProviderAccountRequest $request) 
    {
        try {
            $where = null;        
            if (!empty($request->providerId)) {
                $where = ['provider_id' => $request->providerId];
            }
            $accounts = ProviderAccount::where($where)
                ->join('providers as p', 'p.id', 'provider_id')
                ->join('currency as c', 'c.id', 'p.currency_id')
                ->select([
                    'provider_accounts.id',
                    'username',
                    'password',
                    'type',
                    'provider_accounts.punter_percentage',
                    'provider_accounts.is_enabled',
                    'is_idle',
                    'provider_id',
                    'p.currency_id',
                    'c.code as currency',
                    'uuid',
                    DB::raw("(SELECT created_at FROM oauth_access_tokens WHERE user_id = users.id ORDER BY created_at DESC LIMIT 1)
                    as last_login_date"),
                    DB::raw("(SELECT created_at FROM orders WHERE status IN ('SUCCESS', 'PENDING') AND user_id = users.id AND bet_id IS NOT NULL
                    ORDER BY created_at DESC LIMIT 1) as last_bet"),
                    DB::raw("(SELECT SUM (stake) FROM orders WHERE status IN ('SUCCESS', 'PENDING') AND user_id = users.id AND bet_id IS NOT NULL)
                    as open_bets")
                ])                                    
                ->orderBy('provider_accounts.created_at', 'DESC')
                ->skip($request->offset)
                ->take($request->limit)
                ->get()
                ->toArray();

            $data = [];
            if (!empty($accounts)) {
                foreach($accounts as $account) {
                    $data[] = [
                        'id'                => $account['id'],
                        'username'          => $account['username'],
                        'password'          => $account['password'],
                        'type'              => $account['type'],
                        'punter_percentage' => $account['punter_percentage'],
                        'is_enabled'        => $account['is_enabled'],
                        'is_idle'           => $account['is_idle'],
                        'provider_id'       => $account['provider_id'],
                        'currency_id'       => $account['currency_id'],
                        'currency'          => $account['currency'],
                        'uuid'              => $account['uuid']
                    ];
                }
            }
            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $data
            ], 200);
        }
        catch (Exception $e)
        {
            Log::info('Viewing provider accounts failed.');
            Log::error($e->getMessage());
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    public static function getProviderAccountByUuid($uuid)
    {
        try {
            $accounts = ProviderAccount::where('uuid', $uuid)
                ->join('providers as p', 'p.id', 'provider_id')
                ->join('currency as c', 'c.id', 'p.currency_id')
                ->select(['username', 'c.code as currency', 'uuid'])
                ->first();
            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $accounts
            ], 200);
        }
        catch (Exception $e)
        {
            Log::info('Viewing provider account with uuid: '. $uuid .' failed.');
            Log::error($e->getMessage());
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $id = null;
            if (!empty($request->id)) {
                $id = $request->id;
            }
            $accounts = ProviderAccount::getProviderAccounts($id);

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $accounts
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => trans('generic.internal-server-error')
            ], 500);
        }
    }

    public function manage(ProviderAccountRequest $request, WalletService $wallet) 
    {
        DB::beginTransaction();
        try 
        {
            if (!empty($request)) {                
                !empty($request->id) ? $data['id'] = $request->id : null;
                !empty($request->username) ? $data['username'] = $request->username : null;
                !empty($request->password) ? $data['password'] = $request->password : null;
                !empty($request->provider_id) ? $data['provider_id'] = $request->provider_id : 0;
                !empty($request->type) ? $data['type'] = $request->type : null;
                !empty($request->punter_percentage) ? $data['punter_percentage'] = $request->punter_percentage : 0;
                !empty($request->credits) ? $data['credits'] = $request->credits : 0;
                !empty($request->is_enabled) ? $data['is_enabled'] = true : $data['is_enabled'] = false;
                !empty($request->is_idle) ? $data['is_idle'] = true : $data['is_idle'] = false;
                $data['updated_at'] = Carbon::now();

                //Record is on update process
                if (!empty($request->id))  {
                    $providerAccount = ProviderAccount::where('id', $request->id)->first();
                }
                else {
                    $providerAccount = ProviderAccount::withTrashed()->where('username', $request->username)
                        ->where('provider_id', $request->provider_id)->first();

                    if (!empty($providerAccount)) {
                        ProviderAccount::withTrashed()->where('username', $request->username)
                            ->where('provider_id', $request->provider_id)->first()->restore();
                    }    
                    
                }               

                if (!empty($providerAccount)) {
                    $providerAccount->update($data);
                    $message  = 'success';   
                }
                else
                {    
                    $data['uuid'] = uniqid();
                    $providerAccount = ProviderAccount::create($data);

                    $walletData = [
                      'uuid'          => $providerAccount->uuid,
                      'currency'      => Currency::getCodeById($providerAccount->provider()->first()->currency_id),
                      'amount'        => 0,
                      'reason'        => 'Initial deposit',
                      'wallet_token'  => $request->wallet_token
                    ];
  
                    $wallet->walletCredit((object) $walletData);

                    $message = 'success';       
                }
            
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => $message,
                    'data'        => $providerAccount
                ], 200);
            }
            
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

    public function softDelete($id) 
    {
        try {

            $deleted = ProviderAccount::find($id)->delete();
            $message = 'failed';

            if ($deleted) {
                $message = 'success';                
            }

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $message
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => trans('generic.internal-server-error')
            ], 500);
        }
    }

    public static function getProviderAccount($id)
    {
        try {
            $account = ProviderAccount::find($id);

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $account
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => trans('generic.internal-server-error')
            ], 500);
        }
    }
}
