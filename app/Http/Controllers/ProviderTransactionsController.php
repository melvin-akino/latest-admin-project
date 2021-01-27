<?php

namespace App\Http\Controllers;

use App\Requests\ProviderTransactionRequest;
use App\Facades\ProviderTransaction as ProviderTransactionFacade;

class TransactionsController extends Controller
{
    public function transactions(ProviderTransactionRequest $request)
    {
        return ProviderTransactionFacade::getOrders($request);
    }
}