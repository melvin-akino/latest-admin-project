<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::getAllOrders($request->id);        

        $toLogs = [
          "class"       => "OrdersController",
          "message"     => $orders,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);
        
        return response()->json($orders);
    }

    public function getUserOpenOrders(Request $request)
    {
        $openOrders = Order::getOpenOrders($request->user_id);

        $toLogs = [
          "class"       => "OrdersController",
          "message"     => $openOrders,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($openOrders);
    }

    public function getUserTransactions(Request $request)
    {
        $orders = Order::getUserTransactions($request);

        $toLogs = [
          "class"       => "OrdersController",
          "message"     => $orders,
          "module"      => "API",
          "status_code" => 200
        ];
        monitorLog('monitor_api', 'info', $toLogs);

        return response()->json($orders);
    }
}
