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
        $data = [];
        if (!empty($orders)) {
            $pl = 0;
            $openOrders = 0;
            $lastBetDate = '';
            $providerAccountLastUpdate = '';
            foreach($orders as $key => $order) {
                if ($key == 0) {
                    $lastBetDate = $order['created_at'];
                    $providerAccountLastUpdate = $order['updated_at'];
                    $lastAction = 'Check Balance';
                }

                if ($order['settled_date'] != '') {
                    $pl += $order['actual_profit_loss'];

                    if (Carbon::createFromFormat("Y-m-d H:i:s", $providerAccountLastUpdate, 'Etc/UTC') <= Carbon::createFromFormat("Y-m-d H:i:s", $order['settled_date'], 'Etc/UTC')) {
                        $providerAccountLastUpdate = $order['settled_date'];
                        $lastAction = 'Settlement';
                    }
                }
                else {
                    $openOrders += $order['actual_stake'];
                    
                }
                
            }

            $data = [
                'provider_account_id' => $request->id,
                'pl' => $pl,
                'open_orders' => $openOrders,
                'last_bet' => $lastBetDate,
                'last_scrape' => $providerAccountLastUpdate,
                'last_sync' => $lastAction
            ];
        }

        return response()->json($data);
    }
}
