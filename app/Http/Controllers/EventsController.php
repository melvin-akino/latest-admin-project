<?php

namespace App\Http\Controllers;

use App\Models\Events;
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
}