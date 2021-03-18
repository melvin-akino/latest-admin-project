<?php

namespace App\Http\Controllers;

use App\Models\{Events, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    /**
     * Get raw `events` from parameter Provider
     * 
     * @param  Request $request
     * 
     * @return json
     */
    public function getRawEvents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'providerId' => 'required',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $searchKey = '';
        $page = 1;
        $limit = 10;

        if ($request->has('searchKey')) $searchKey = $request->searchKey;

        if ($request->has('page')) $page = $request->page;

        if ($request->has('limit')) $limit = $request->limit;

        $data = Events::getEventsByProvider($request->providerId, $searchKey, $page, $limit, false);

        return response()->json($data);
    }

    public function getEvents()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $events    = Events::getEventsByProvider($providerId);

        return response()->json($events);
    }
}