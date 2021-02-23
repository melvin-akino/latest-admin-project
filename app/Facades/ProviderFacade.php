<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ProviderFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\ProviderService'; }
}
