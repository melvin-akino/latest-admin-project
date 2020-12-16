<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet, WalletLedger, Source, Currency};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};
use Carbon\Carbon;
class UsersController extends Controller
{
    public function index()
    {
        $users = User::getAll();

        return response()->json($users);
    }

    public function manage(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            if (empty($request->id)) 
            {
                $user = new User([
                    'name'          => explode('@', $request->email)[0],
                    'email'         => $request->email,
                    'password'      => Hash::make($request->password),
                    'firstname'     => $request->firstname,
                    'lastname'      => $request->lastname,
                    'status'        => $request->status,
                    'currency_id'   => $request->currency_id
                ]);
            }
            else {
                $user = User::where('id', $request->id)->first();
                $user->firstname    = $request->firstname;
                $user->lastname     = $request->lastname;
                $user->status       = $request->status;
                if ($request->password) 
                {
                    $user->password = Hash::make($request->password);
                }
            }

            if ($user->save())
            {
                if (empty($request->id))
                {
                    //Make user wallet and deposit amount in it
                    $wallet = new Wallet([
                        'user_id'       => $user->id,
                        'currency_id'   => $request->currency_id,
                        'balance'       => $request->balance
                    ]);

                    $wallet->save();
                    
                    $sourceId = Source::getIdByName('REGISTRATION');

                    //Create a wallet ledger here
                    $walletLedger = new WalletLedger([
                        'wallet_id'     => $wallet->id,
                        'balance'       => $request->balance,
                        'credit'        => $request->balance,
                        'debit'         => 0,
                        'source_id'     => $sourceId
                    ]);

                    $walletLedger->save();
                    //This will be discussed on tuesday
                }
            }

            DB::commit();

            return response()->json([
                'status'                => true,
                'status_code'           => 200,
                'message'               => 'success',
                'data'                  => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'firstname'     => $user->firstname,
                    'lastname'      => $user->lastname,
                    'currency'      => empty($request->id) ? Currency::getCodeById($wallet->currency_id) : "",
                    'credits'       => empty($request->id) ? $wallet->balance : "",
                    'status'        => $user->status,
                    'created_at'    => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
                    'updated_at'    => Carbon::parse($user->updated_at)->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (Exception $e) {
            DB::rollback;
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => $e->getMessage()
            ], 500);
        }
    }
}
