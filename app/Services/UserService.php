<?php

namespace App\Services;
use App\Models\{ User, OAuthToken, Order, Currency };
use Illuminate\Support\Facades\{DB, Hash, Log };
use Carbon\Carbon;
use Exception;
use App\Facades\Wallet;

class UserService {
  public function getUsers($request)
  {
    $page = $request->page;
    $limit = $request->limit;
    $offset = ($page * $limit) - $limit;
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
        'c.code as currency_code'
      ])
      ->offset($offset)
      ->limit($limit);

    if(!empty($request->sortBy)) {
      $users = $users->orderBy($request->sortBy, $request->sort);
    } else {
      $users = $users->orderBy('users.created_at', 'DESC');
    }

    // if(!empty($request->search)) {
    //   $users = $users->where('users.name', 'LIKE', "%".$request->search."%")
    //     ->orWhere('email', 'LIKE', "%".$request->search."%")
    //     ->orWhere('firstname', 'LIKE', "%".$request->search."%")
    //     ->orWhere('lastname', 'LIKE', "%".$request->search."%")
    //     ->orWhere('users.created_at', 'LIKE', "%".$request->search."%");
    // }

    $users = $users->get()->toArray();

    if ($users) {   
      foreach($users as $key => $value) {
        $oauth = OAuthToken::getLastLogin($value['id']);
        $orders = Order::getOpenOrders($value['id']);
        $walletData = [
          'uuid' => $value['uuid'],
          'currency' => $value['currency_code'],
          'wallet_token' => $request->wallet_token
        ]; 
        $wallet = Wallet::walletBalance((object) $walletData);
        $wallet = json_decode($wallet->getBody()->getContents(), true);

        $users[$key]['last_login'] = $oauth['last_login_date'];
        $users[$key]['open_bets'] = $orders['open_orders'];
        $users[$key]['last_bet'] = $orders['last_bet'];
        $users[$key]['credits'] = $wallet['data']['balance'];
        $users[$key]['currency'] = $wallet['data']['currency'];
      }
    }
    $users = !empty($users) ? $users : [];
    return response()->json([
      'data' => $users,
      'total' => User::count()
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

    return response()->json($user);
  }
}