<?php

namespace App\Http\Controllers;

use App\Models\{Event, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * Get raw `events` from parameter Provider
     * 
     * @param  int $providerId
     * 
     * @return json
     */
    public function getRawEvents($providerId)
    {
        $data = Event::getEventsByProvider($providerId, false);

        return response()->json($data);
    }

    public function getEvents()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $events    = Event::getEventsByProvider($providerId);

        return response()->json($events);
    }
}