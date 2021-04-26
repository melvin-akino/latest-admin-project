<?php

namespace App\Http\Controllers\Auth;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Spatie\Activitylog\Contracts\Activity;

class ApiAuthController extends Controller
{
    public function login (Request $request, WalletService $wallet) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            $toLogs = [
              "class"       => "ApiAuthController",
              "message"     => ['errors'=>$validator->errors()->all()],
              "module"      => "API_ERROR",
              "status_code" => 422
            ];
            monitorLog('monitor_api', 'error', $toLogs);

            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = AdminUser::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
              if($user->status == 1) {
                  $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                  $response = ['token' => $token, 'wallet_token' => $wallet->getAccessToken()];

                  $toLogs = [
                    "class"       => "ApiAuthController",
                    "message"     => $response,
                    "module"      => "API",
                    "status_code" => 200
                  ];
                  monitorLog('monitor_api', 'info', $toLogs);

                  activity("Admin Users")
                    ->causedBy($user)
                    ->tap(function(Activity $activity) use($request) {
                      $activity->properties = $activity->properties->put('action', 'Authenticated');
                      $activity->properties = $activity->properties->put('ip_address', $request->ip());
                    })
                    ->log("Logged in");

                  return response($response, 200);
              } else {
                $response = ["message" => 'Your administrator account was suspended.'];

                $toLogs = [
                  "class"       => "ApiAuthController",
                  "message"     => $response,
                  "module"      => "API_ERROR",
                  "status_code" => 401
                ];
                monitorLog('monitor_api', 'error', $toLogs);

                return response($response, 401);
              }
            } else {
                $response = ["message" => "Password mismatch"];

                $toLogs = [
                  "class"       => "ApiAuthController",
                  "message"     => $response,
                  "module"      => "API_ERROR",
                  "status_code" => 401
                ];
                monitorLog('monitor_api', 'error', $toLogs);

                return response($response, 401);
            }
        } else {
            $response = ["message" =>'User does not exist'];

            $toLogs = [
              "class"       => "ApiAuthController",
              "message"     => $response,
              "module"      => "API_ERROR",
              "status_code" => 401
            ];
            monitorLog('monitor_api', 'error', $toLogs);

            return response($response, 401);
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];

        $toLogs = [
          "class"       => "ApiAuthController",
          "message"     => $response,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        activity("Admin Users")
          ->tap(function(Activity $activity) use($request) {
            $activity->properties = $activity->properties->put('action', 'Unauthenticated');
            $activity->properties = $activity->properties->put('ip_address', $request->ip());
          })
          ->log("Logged out");

        return response($response, 200);
    }
}
