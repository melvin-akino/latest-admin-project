<?php

namespace App\Http\Controllers;

use App\Models\{Events, Provider, SystemConfiguration AS SC};
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
        $data = Events::getEventsByProvider($providerId, false);

        return response()->json($data);
    }

    public function getEvents()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $events    = Events::getEventsByProvider($providerId);

        return response()->json($events);
    }
}