<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ProviderAccountFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\ProviderAccountService'; }
}
