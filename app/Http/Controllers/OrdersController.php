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

        return response()->json($orders);
    }

    public function getUserOpenOrders(Request $request)
    {
        $openOrders = Order::getOpenOrders($request->user_id);

        return response()->json($openOrders);
    }
}
