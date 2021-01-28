<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderTransactionRequest;
use App\Facades\ProviderTransaction as ProviderTransactionFacade;

class ProviderTransactionsController extends Controller
{
    public function transactions(ProviderTransactionRequest $request)
    {
        return ProviderTransactionFacade::getOrders($request);
    }
}