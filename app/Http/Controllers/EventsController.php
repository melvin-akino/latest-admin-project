<?php

namespace App\Http\Controllers;

use App\Models\{Event, LeagueGroup, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * Get unmatched `events` from parameter league id
     * 
     * @param  Request $request
     * @param  int     $leagueId
     * 
     * @return json
     */
    public function getUnmatchedEventsByLeague(Request $request, $leagueId = null)
    {
        $searchKey = '';
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';

        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';

        $events = Event::getEvents([$leagueId], null, false, $searchKey, $sortOrder);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $events->offset(($page - 1) * $limit)->limit($limit)->get()
        ]);
    }

    /**
     * Get unmatched `events` from parameter master league id
     * 
     * @param  Request $request
     * @param  int     $masterLeagueId
     * 
     * @return json
     */
    public function getUnmatchedEventsByMasterLeague(Request $request, $masterLeagueId = null)
    {
        $searchKey = '';
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';

        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';

        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagueIds = LeagueGroup::getNonPrimaryLeagueIds($masterLeagueId, $providerId)->toArray();

        $events = Event::getEvents($leagueIds, null, false, $searchKey, $sortOrder);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $events->offset(($page - 1) * $limit)->limit($limit)->get()
        ]);
    }

    /**
     * Get matched `events` from parameter league id
     * 
     * @param  Request $request
     * @param  int     $leagueId
     * 
     * @return json
     */
    public function getMatchedEventsByLeague(Request $request, $leagueId = null) {
        $page = 1;
        $limit = 10;

        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;

        $events = Event::getEvents([$leagueId], null);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $events->offset(($page - 1) * $limit)->limit($limit)->get()
        ]);
    }

    /**
     * Get matched `events` from parameter provider id
     * 
     * @param  Request $request
     * @param  int     $providerId
     * 
     * @return json
     */
    public function getMatchedEventsByProvider(Request $request, $providerId = null) {
        $page = 1;
        $limit = 10;

        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;

        $events = Event::getEvents(null, $providerId);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $events->offset(($page - 1) * $limit)->limit($limit)->get()
        ]);
    }
}