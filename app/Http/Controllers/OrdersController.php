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
            $pl = 0;
            $openOrders = 0;
            $lastBetDate = '';
            foreach($orders as $key => $order) {
                if ($key == 0) {
                    $lastBetDate = $order['created_at'];
                }

                if ($order['settled_date'] != '') {
                    $pl += $order['actual_profit_loss'];
                }
                else {
                    $openOrders += $order['actual_stake'];
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
