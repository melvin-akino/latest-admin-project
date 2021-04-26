<?php

namespace App\Services;
use App\Models\{ User, Currency };
use Illuminate\Support\Facades\{DB, Hash, Log };
use Carbon\Carbon;
use Exception;
use App\Facades\Wallet;

class UserService {
  public function getUsers()
  {
    $users = User::join('currency as c', 'c.id', 'users.currency_id')
      ->select([
        'users.id',
        'email',
        'firstname',
        'lastname',
        'status',
        'currency_id',
        'users.created_at',
        'users.updated_at',
        'uuid',
        'c.code as currency_code',
        DB::raw("(SELECT created_at FROM oauth_access_tokens WHERE user_id = users.id ORDER BY created_at DESC LIMIT 1)
        as last_login_date"),
        DB::raw("(SELECT created_at FROM orders WHERE status IN ('SUCCESS', 'PENDING') AND user_id = users.id AND bet_id IS NOT NULL
        ORDER BY created_at DESC LIMIT 1) as last_bet"),
        DB::raw("(SELECT SUM (stake) FROM orders WHERE status IN ('SUCCESS', 'PENDING') AND user_id = users.id AND bet_id IS NOT NULL)
        as open_bets")
      ])
      ->orderBy('created_at', 'DESC')
      ->get()
      ->toArray();
      
    $users = !empty($users) ? $users : [];
    return response()->json([
      'status'      => true,
      'status_code' => 200,
      'data'        => $users,
    ]);
  }
  
  public function getWalletBalanceForCurrentItems($request) 
  {
    $walletDataArray = [];
    foreach($request->users as $user) {
      if(!is_array($user)) {
        $user = json_decode($user, true);
      }
      $walletData = [
        'uuid' => $user['uuid'],
        'currency' => $user['currency'],
        'wallet_token' => $request->wallet_token
      ]; 
      $wallet = Wallet::walletBalance((object) $walletData);
      $wallet = json_decode($wallet->getBody()->getContents(), true);
      $walletDataArray[] = [
        'uuid' => $user['uuid'],
        'credits' => $wallet['data']['balance'],
        'currency' => $wallet['data']['currency']
      ];
    }
    return response()->json([
      'status'      => true,
      'status_code' => 200,
      'data'        => $walletDataArray,
    ]);
  }

  public function manage($request)
  {
    DB::beginTransaction();
    try {
      if (empty($request->id)) {
          $user = new User([
              'name'          => explode('@', $request->email)[0],
              'email'         => $request->email,
              'password'      => Hash::make($request->password),
              'firstname'     => $request->firstname,
              'lastname'      => $request->lastname,
              'status'        => $request->status,
              'currency_id'   => $request->currency_id,
              'uuid'          => uniqid()
          ]);
      } else {
          $user = User::where('id', $request->id)->first();
          $user->firstname    = $request->firstname;
          $user->lastname     = $request->lastname;
          $user->status       = $request->status;
          if ($request->password) 
          {
              $user->password = Hash::make($request->password);
          }
      }

      if ($user->save()) {
        if (empty($request->id)) {
          $walletData = [
            'uuid'          => $user->uuid,
            'currency'      => Currency::getCodeById($request->currency_id),
            'amount'        => $request->balance,
            'reason'        => 'Initial deposit',
            'wallet_token'  => $request->wallet_token
          ];

          Wallet::walletCredit((object) $walletData);
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
            'currency'      => empty($request->id) ? Currency::getCodeById($request->currency_id) : "",
            'credits'       => empty($request->id) ? $request->balance : "",
            'status'        => $user->status,
            'uuid'          => $user->uuid,
            'created_at'    => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
            'updated_at'    => Carbon::parse($user->updated_at)->format('Y-m-d H:i:s')
        ]
      ], 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
          'status'      => false,
          'status_code' => 500,
          'message'     => $e->getMessage()
      ], 500);
    }
  }

  public function getUser($userId)
  {
    $user = User::find($userId);

    return response()->json($user);
  }

  public function getUserByUuid($uuid)
  {
    $user = User::where('uuid', $uuid)
      ->join('currency as c', 'c.id', 'users.currency_id')
      ->select(['email', 'firstname', 'lastname', 'c.code as currency', 'uuid'])
      ->first();

    return response()->json([
      'status'      => true,
      'status_code' => 200,
      'data'        => $user
    ], 200);
  }
}