<?php

namespace App\Http\Controllers;

use App\Models\{Events, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\RawListRequest;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    /**
     * Get raw `events` from parameter Provider
     * 
     * @param  RawListRequest $request
     * 
     * @return json
     */
    public function getRawEvents(RawListRequest $request)
    {
        return RawListingFacade::getByProvider($request, 'event');
    }

    public function getEvents()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $events    = Events::getByProvider($providerId);

        return response()->json($events);
    }
}