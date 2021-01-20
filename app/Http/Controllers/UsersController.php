<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet, WalletLedger, Source, Currency};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};
use Carbon\Carbon;
use App\Services\WalletService;
class UsersController extends Controller
{
    public function index()
    {
        $users = User::getAll();

        return response()->json($users);
    }

    public function manage(UserRequest $request, WalletService $wallet)
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
                    'uuid'          => uniqid()
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
                  $walletData = [
                    'uuid'          => $user->uuid,
                    'currency'      => $request->currency,
                    'amount'        => $request->balance,
                    'reason'        => 'Initial deposit',
                    'wallet_token'  => $request->wallet_token
                  ];

                  $wallet->walletCredit((object) $walletData);
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
                    'currency'      => empty($request->id) ? $request->currency : "",
                    'credits'       => empty($request->id) ? $request->balance : "",
                    'status'        => $user->status,
                    'uuid'          => $user->uuid,
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

    public function getUser($userId)
    {
        $user = User::getUser($userId);

        return response()->json($user);
    }
}
