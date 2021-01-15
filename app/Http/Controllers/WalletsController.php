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

        return response()->json($wallet);
    }

    public function getAccessToken(Request $request, WalletService $wallet) 
    {
        $token = $wallet->getAccessToken();
        
        return response()->json(['token' => $token ]);
    }

    public function getClients(Request $request, WalletService $wallet) 
    {
        $clients = $wallet->getClients($request->wallet_token);
        
        return response()->json($clients, $clients->status_code);
    }

    public function createClient(Request $request, WalletService $wallet)
    {
      $client = $wallet->createClient($request);

      return response()->json($client, $client->status_code);
    }

    public function revokeClient(Request $request, WalletService $wallet)
    {
      $client = $wallet->revokeClient($request);

      return response()->json($client, $client->status_code);
    }

    public function getCurrencies(Request $request, WalletService $wallet)
    {
      $currencies = $wallet->getCurrencies($request->wallet_token);

      return response()->json($currencies, $currencies->status_code);
    }

    public function createCurrency(Request $request, WalletService $wallet)
    {
      $currency = $wallet->createCurrency($request);

      return response()->json($currency, $currency->status_code);
    }

    public function updateCurrency(Request $request, WalletService $wallet)
    {
      $currency = $wallet->updateCurrency($request);

      return response()->json($currency, $currency->status_code);
    }
}
