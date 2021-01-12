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

    public function getClients(Request $request, WalletService $wallet) 
    {
        $clients = $wallet->getClients($request->wallet_token);

        return response()->json($clients);
    }
}
