<?php

namespace App\Http\Controllers\Auth;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiAuthController extends Controller
{
    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = AdminUser::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;

                $http = new Client();
                $walletURL = env('WALLET_URL', 'http://localhost:8000/api/v1');
                $walletClientId = env('WALLET_CLIENT_ID', 'client_id');
                $walletClientSecret = env('WALLET_CLIENT_SECRET', 'client_secret');

                $walletResponse = $http->request('POST', $walletURL.'/oauth/token', [
                  'form_params' => [
                    'client_id' => $walletClientId,
                    'client_secret' => $walletClientSecret,
                    'grant_type' => 'client_credentials'
                  ]
                ]);
                $walletResponse = json_decode($walletResponse->getBody());
                $walletToken = $walletResponse->data->access_token;

                $response = ['token' => $token, 'wallet_token' => $walletToken];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 401);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 401);
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
