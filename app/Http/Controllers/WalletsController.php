<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Services\WalletService;

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

      return response()->json(json_decode($client->getBody()->getContents()), $client->getStatusCode());
    }

    public function revokeClient(Request $request, WalletService $wallet)
    {
      $client = $wallet->revokeClient($request);

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

      return response()->json(json_decode($currency->getBody()->getContents()), $currency->getStatusCode());
    }

    public function updateCurrency(Request $request, WalletService $wallet)
    {
      $currency = $wallet->updateCurrency($request);

      return response()->json(json_decode($currency->getBody()->getContents()), $currency->getStatusCode());
    }
}
