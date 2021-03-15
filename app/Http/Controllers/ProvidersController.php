<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderRequest;
use App\Facades\ProviderFacade;
class ProvidersController extends Controller
{
    public function index()
    {
        return ProviderFacade::getAllProviders();
    }

    public function getNonPrimaryProviders()
    {
        return ProviderFacade::getNonPrimaryProviders();
    }

    public function create(ProviderRequest $request)
    {
        return ProviderFacade::create($request);
    }

    public function update(ProviderRequest $request)
    {
        return ProviderFacade::update($request);
    }
    public function getIdFromAlias($alias)
    {
        return ProviderFacade::getIdFromAlias($alias);
    }
}
