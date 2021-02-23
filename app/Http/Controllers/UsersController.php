<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Facades\User;

class UsersController extends Controller
{
    public function index()
    {
      return User::getUsers();
    }

    public function manage(UserRequest $request)
    {
      return User::manage($request);
    }

    public function getUser($userId)
    {
      return User::getUser($userId);
    }

    public function getUserByUuid($uuid)
    {
      return User::getUserByUuid($uuid);
    }

    public function getUsersWallet(Request $request)
    {
      return User::getWalletBalanceForCurrentItems($request);
    }
}
