<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AdminUser;

class IsAdminActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        if($request->user()->status == 0) {
          $token = $request->user()->token();
          $token->revoke();

          return response()->json([
            'status'      => false,
            'status_code' => 401,
            'message'     => 'Your administrator account was suspended.'
          ], 401);
        }

        return $next($request);
    }
}
