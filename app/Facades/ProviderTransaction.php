<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ProviderTransaction extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\ProviderTransactionService'; }
}
