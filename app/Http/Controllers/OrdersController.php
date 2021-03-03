<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Facades\OrderFacade;

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

    public function getUserTransactions(Request $request)
    {
        $orders = Order::getUserTransactions($request);

        return response()->json($orders);
    }

    public function update(OrderRequest $request) 
    {
        return OrderFacade::update($request);
    }
}
