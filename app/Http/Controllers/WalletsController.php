<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Spatie\Activitylog\Contracts\Activity;

class WalletsController extends Controller
{
    public function getUserBalance(Request $request)
    {
        $wallet = Wallet::getUserBalance($request);

        $toLogs = [
          "class"       => "WalletsController",
          "message"     => $wallet,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($wallet);
    }

    public function getAccessToken(Request $request, WalletService $wallet) 
    {
        $token = $wallet->getAccessToken();

        $toLogs = [
          "class"       => "WalletsController",
          "message"     => ['token' => $token ],
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);
        
        return response()->json(['token' => $token ]);
    }

    public function getClients(Request $request, WalletService $wallet) 
    {
        $clients = $wallet->getClients($request->wallet_token);
        
        return response()->json(json_decode($clients->getBody()->getContents()), $clients->getStatusCode());
    }

    public function createClient(Request $request, WalletService $wallet)
    {
      $client = $wallet->createClient($request);

      activity("Wallet Clients")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Created');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Created client: ".$request->client_id);

      return response()->json(json_decode($client->getBody()->getContents()), $client->getStatusCode());
    }

    public function revokeClient(Request $request, WalletService $wallet)
    {
      $client = $wallet->revokeClient($request);

      activity("Wallet Clients")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Updated');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Revoked client: ".$request->client_id);

      return response()->json(json_decode($client->getBody()->getContents()), $client->getStatusCode());
    }

    public function getCurrencies(Request $request, WalletService $wallet)
    {
      $currencies = $wallet->getCurrencies($request->wallet_token);

      return response()->json(json_decode($currencies->getBody()->getContents()), $currencies->getStatusCode());
    }

    public function createCurrency(Request $request, WalletService $wallet)
    {
      $currency = $wallet->createCurrency($request);

      activity("Currencies")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Created');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Created currency: ".$request->name);

      return response()->json(json_decode($currency->getBody()->getContents()), $currency->getStatusCode());
    }

    public function updateCurrency(Request $request, WalletService $wallet)
    {
      $currency = $wallet->updateCurrency($request);
      $action   = $request->is_enabled ? "Enabled" : "Disabled";
      activity("Currencies")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Updated');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log($action." currency: ".$request->name);

      return response()->json(json_decode($currency->getBody()->getContents()), $currency->getStatusCode());
    }

    public function walletUpdate(Request $request, WalletService $wallet)
    {
      $user = User::where('uuid', $request->uuid)->first();

      if($request->transactionType == 'Deposit') {
        $update = $wallet->walletCredit($request);

        activity("Wallet Update")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Credited');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Credited ".$request->amount." to: ".$user->email." (".$request->reason.")");
      } else {
        $update = $wallet->walletDebit($request);

        activity("Wallet Update")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Debited');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Debited ".$request->amount." from: ".$user->email." (".$request->reason.")");
      }

      return response()->json(json_decode($update->getBody()->getContents()), $update->getStatusCode());
    }

    public function walletBalance(Request $request, WalletService $wallet)
    {
      $balance = $wallet->walletBalance($request);

      return response()->json(json_decode($balance->getBody()->getContents()), $balance->getStatusCode());
    }

    public function walletTransaction(Request $request, WalletService $wallet)
    {
      $transactions = $wallet->walletTransaction($request);

      return response()->json(json_decode($transactions->getBody()->getContents()), $transactions->getStatusCode());
    }
}
