<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Wallet extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\WalletService'; }
}
