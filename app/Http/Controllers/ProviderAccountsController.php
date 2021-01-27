<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderAccountRequest;
use App\Models\{ProviderAccount,Currency};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\WalletService;
class ProviderAccountsController extends Controller
{
    public function index(Request $request)
    {
        $id = null;
        if (!empty($request->id)) {
            $id = $request->id;
        }
        $accounts = ProviderAccount::getProviderAccounts($id);

        return response()->json($accounts);
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
}
