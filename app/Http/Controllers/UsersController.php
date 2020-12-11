<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB};

class UsersController extends Controller
{
    public function index()
    {
        $users = User::getAll();

        return response()->json($users);
    }

    public function manage(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            if (empty($request->id)) 
            {
                $user = new User([
                    'name'      => explode('@', $request->email)[0],
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'firstname' => $request->firstname,
                    'lastname'  => $request->lastname,
                    'status'    => $request->status
                ]);
            }
            else {
                $user = User::where('id', $request->id)->first();
                $user->firstname    = $request->firstname;
                $user->lastname     = $request->lastname;
                $user->status       = $request->status;
                if ($request->password) 
                {
                    $user->password = Hash::make($request->password);
                }
            }

            if ($user->save())
            {
                if (!empty($request->id))
                {
                    //Make user wallet and deposit amount in it
                    //This will be discussed on tuesday
                }
            }

            DB::commit();

            return response()->json([
                'status'                => true,
                'status_code'           => 200,
                'message'               => 'success',
            ], 200);
        } catch (Exception $e) {
            DB::rollback;
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => $e->getMessage()
            ], 500);
        }
    }
}
