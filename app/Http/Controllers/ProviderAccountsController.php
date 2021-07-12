<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderAccountRequest;
use App\Facades\ProviderAccountFacade;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Exception;

class ProviderAccountsController extends Controller
{
    public function index(Request $request)
    {
        return ProviderAccountFacade::index($request);
    }
    public function manage(ProviderAccountRequest $request, WalletService $wallet)
    {
        return ProviderAccountFacade::manage($request, $wallet);
    }
    public function softDelete($id)
    {
        return ProviderAccountFacade::softDelete($id);
    }
    public function getProviderAccount($id)
    {
        return ProviderAccountFacade::getProviderAccount($id);
    }
    public function getProviderAccountByUuid($uuid)
    {
        return ProviderAccountFacade::getProviderAccountByUuid($uuid);
    }

    public function getProviderAccountUsages()
    {
        return ProviderAccountFacade::getProviderAccountUsages();
    }
}
