<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::getAll();

        return response()->json($users);
    }

    public function getUser($userId)
    {
      $user = User::getUser($userId);

      return response()->json($user);
    }
}
