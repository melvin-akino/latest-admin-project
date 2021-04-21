<?php

namespace App\Http\Controllers;

use App\Models\{Event, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\RawListRequest;
use Illuminate\Http\Request;
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
        $events     = Event::getByProvider($providerId);

        return response()->json($events);
    }

    /**
    * Remove Grouped teams and events from the Database, recreate the delete records into the unmatched_data table
    * 
    * @param  object   Illuminate\Http\Request $request
    *     $request->event_id                int         event id
    *     $request->provider_id             int         provider id of the league
    *     $request->sport_id                int         sport id of the league
    * 
    * @return json
    */
    public function postUnmatchEvent(Request $request)
    {
        return MatchingFacade::unmatchSecondaryEvent($request);
    }
}