<?php

namespace App\Http\Controllers;

use App\Models\{Event, LeagueGroup, Provider, SystemConfiguration AS SC, MasterLeague};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\{RawListRequest, EventUnmatchRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $paginated = $request->has('paginated') ? $request->paginated : false;

        $events = Event::getEvents([$leagueId], null, false, $searchKey, $sortOrder);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $paginated ? $events->offset(($page - 1) * $limit)->limit($limit)->get() : $events->get()
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
        $searchKey    = $request->has('searchKey') ? $request->searchKey : '';
        $page         = $request->has('page') ? $request->page : 1;
        $limit        = $request->has('limit') ? $request->limit : 10;
        $sortOrder    = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $gameSchedule = $request->has('gameSchedule') ? $request->gameSchedule : null;

        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagueIds  = LeagueGroup::getNonPrimaryLeagueIds($masterLeagueId, $providerId)->toArray();

        $events = [];
        $total  = 0;

        if(!empty($leagueIds)) {
            $events = Event::getEvents($leagueIds, null, false, $searchKey, $sortOrder, $gameSchedule);
            $total  = $events->count();
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $total,
            'pageNum'     => $page,
            'pageData'    => !empty($leagueIds) ? $events->offset(($page - 1) * $limit)->limit($limit)->get() : $events
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

    public function getMatchedEventsByLeague(Request $request, $leagueId = null) 
    {
        $page         = $request->has('page') ? $request->page : 1;
        $limit        = $request->has('limit') ? $request->limit : 10;
        $sortOrder    = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $paginated    = $request->has('paginated') ? $request->paginated : false;
        $gameSchedule = $request->has('gameSchedule') ? $request->gameSchedule : null;
        $leagueId     = $leagueId ? [$leagueId] : [];
        $providerId   = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        $events = Event::getEvents($leagueId, $providerId, true, '', $sortOrder, $gameSchedule);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $events->count(),
            'pageNum'     => $page,
            'pageData'    => $paginated ? $events->offset(($page - 1) * $limit)->limit($limit)->get() : $events->get()
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
    public function getMatchedEventsByProvider(Request $request, $providerId = null) 
    {
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

    public function getMatchedEvents(Request $request)
    {
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        $masterLeagues = MasterLeague::getMasterLeaguesForUnmatching($providerId, $searchKey, $sortOrder);
        $total = $masterLeagues->count();
        $pageData = $masterLeagues->offset(($page - 1) * $limit)->limit($limit)->get();

        $result = [];

        foreach($pageData as $data) {
            $result[] = [
                'master_league_id' => $data->id,
                'master_league_name' => $data->master_league_name,
                'events' => Event::getMatchedEventsByMasterLeagueId($data->id)->toArray()
            ];
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $total,
            'pageNum'     => $page,
            'pageData'    => $result
        ]);
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
    public function postUnmatchEvent(EventUnmatchRequest $request)
    {
        return MatchingFacade::unmatchSecondaryEvent($request);
    }
}