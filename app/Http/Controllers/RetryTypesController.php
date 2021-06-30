<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetryType;

class RetryTypesController extends Controller
{
    public function index() 
    {
        try {
            $retryTypes = RetryType::all();

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'data'        => $retryTypes
            ], 200);
        } catch(Exception $e) {
            $toLogs = [
                "class"       => "RetryTypesController",
                "message"     => "Line " . $e->getLine() . " | " . $e->getMessage(),
                "module"      => "API_ERROR",
                "status_code" => $e->getCode()
            ];
            monitorLog('monitor_api', 'error', $toLogs);

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'     => $e->getMessage()
            ], 500);
        }
    }
}
