<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Facades\OrderFacade;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return OrderFacade::getProviderOrders($request);
    }
    public function getUserOpenOrders(Request $request)
    {
        return OrderFacade::getOpenOrders($request);
    }
    public function getUserTransactions(Request $request)
    {
        return OrderFacade::getUserTransactions($request);
    }
    public function update(OrderRequest $request) 
    {
        return OrderFacade::update($request);
    }
}
