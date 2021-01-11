<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class WalletsController extends Controller
{
    public function getUserBalance(Request $request)
    {
        $wallet = Wallet::getUserBalance($request);

        return response()->json($wallet);
    }

    public function getClients(Request $request) 
    {
        $http = new Client();
        $walletURL = env('WALLET_URL', 'http://localhost:8000/api/v1');

        $response = $http->request('GET', $walletURL.'/clients', [
          'headers' => [
            'Authorization' => 'Bearer '.$request->wallet_token
          ]
        ]);
        $response = json_decode($response->getBody());

        return response()->json($response);
    }
}
