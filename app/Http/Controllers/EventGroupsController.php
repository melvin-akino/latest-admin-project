<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventGroupRequest;
use App\Facades\EventGroupFacade;

class EventGroupsController extends Controller
{
    public function match(EventGroupRequest $request)
    {
        return EventGroupFacade::match($request);
    }
}