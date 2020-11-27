<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::getAllOrders($request->id);
        $data = [];
        if (!empty($orders)) {
            foreach($orders as $key => $order) {
                if ($key == 0) {
                    $lastBetDate = $order['created_at']
                }
                
            }

            $data = [
                'provider_account_id' => $request->id,
                'pl' => $pl,
                'open_orders' => $openOrders,
                'last_bet' => $lastBetDate
            ];
        }

        return response()->json($data);
    }
}
