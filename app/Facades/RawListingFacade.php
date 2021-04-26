<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class RawListingFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\RawListingService'; }
}