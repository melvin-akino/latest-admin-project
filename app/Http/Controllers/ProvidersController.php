<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProvidersController extends Controller
{
    public function index()
    {
        $providers = Provider::getAllProviders();              

        $toLogs = [
          "class" => "ProvidersController",
          "message" => $providers,
          "module" => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($providers);
    }
}
