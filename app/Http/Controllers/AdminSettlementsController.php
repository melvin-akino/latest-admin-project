<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminSettlementRequest;
use App\Facades\AdminSettlement as AdminSettlementFacade;

class AdminSettlementsController extends Controller
{
    public function create(AdminSettlementRequest $request)
    {
        return AdminSettlementFacade::create($request);
    }
}