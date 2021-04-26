<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SidebarFacade extends Facade
{
    protected static function getFacadeAccessor() { return 'App\Services\SidebarService'; }
}
