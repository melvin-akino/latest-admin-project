<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Facades\OrderFacade;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return OrderFacade::getAllOrders($request);
    }
    public function getUserOpenOrders(Request $request)
    {
        return OrderFacade::getOpenOrders($request);
    }
    public function getUserTransactions(Request $request)
    {
        return OrderFacade::getUserTransactions($request);
    }
}
