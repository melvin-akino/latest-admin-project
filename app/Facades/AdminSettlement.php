<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AdminSettlement extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\AdminSettlementService'; }
}
