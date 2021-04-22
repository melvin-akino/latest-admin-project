<?php

namespace App\Http\Controllers;

use App\Models\{Event, LeagueGroup, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\RawListRequest;
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
        $searchKey = '';
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';
        $paginated = false;

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
        $searchKey = '';
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';

        $searchKey    = $request->has('searchKey') ? $request->searchKey : '';
        $page         = $request->has('page') ? $request->page : 1;
        $limit        = $request->has('limit') ? $request->limit : 10;
        $sortOrder    = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $gameSchedule = $request->has('gameSchedule') ? $request->gameSchedule : null;

        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagueIds = LeagueGroup::getNonPrimaryLeagueIds($masterLeagueId, $providerId)->toArray();

        $events = [];
        $total  = 0;

        if(!empty($leagueIds)) {
            $events = Event::getEvents($leagueIds, null, false, $searchKey, $sortOrder, $gameSchedule)->offset(($page - 1) * $limit)->limit($limit)->get();
            $total  = $events->count();
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $total,
            'pageNum'     => $page,
            'pageData'    => $events
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
        $sortOrder = 'asc';
        $paginated = false;

        $page         = $request->has('page') ? $request->page : 1;
        $limit        = $request->has('limit') ? $request->limit : 10;
        $sortOrder    = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $paginated    = $request->has('paginated') ? $request->paginated : false;
        $gameSchedule = $request->has('gameSchedule') ? $request->gameSchedule : null;
        $leagueId     = $leagueId ? [$leagueId] : [];

        $events = Event::getEvents($leagueId, null, true, '', $sortOrder, $gameSchedule);

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