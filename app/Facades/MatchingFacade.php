<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class MatchingFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\MatchingService'; }
}