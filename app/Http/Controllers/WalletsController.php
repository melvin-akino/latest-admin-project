<?php

namespace App\Http\Controllers;

use App\Models\{User, Wallet};
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

class WalletsController extends Controller
{
    public function getUserBalance(Request $request)
    {
        $wallet = Wallet::getUserBalance($request);

        return response()->json($wallet);
    }
}
