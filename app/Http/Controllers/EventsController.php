<?php

namespace App\Http\Controllers;

use App\Models\{Events, Provider, SystemConfiguration AS SC};
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
        $searchKey = '';
        $page = 1;
        $limit = 10;

        if ($request->has('searchKey')) $searchKey = $request->searchKey;

        if ($request->has('page')) $page = $request->page;

        if ($request->has('limit')) $limit = $request->limit;

        $data = Events::getEventsByProvider($request->providerId, $searchKey, false);

        $result = [
            'total' => $data->count(),
            'pageNum' => $page,
            'pageData' => $data->skip(($page - 1) * $limit)->take($limit)->values()
        ];

        return response()->json($result);
    }

    public function getEvents()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $events    = Events::getEventsByProvider($providerId);

        return response()->json($events);
    }
}