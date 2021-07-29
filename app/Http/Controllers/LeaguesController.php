<?php

namespace App\Http\Controllers;

use App\Models\{MasterLeague, League, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\{RawListRequest, LeagueRequest, LeaguesMatchingRequest};
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Exception;

class LeaguesController extends Controller
{
    /**
     * Get raw `league_names` from paramgeter Provider
     * 
     * @param  RawListRequest $request
     * 
     * @return json
     */
    public function getUnmatchedLeagues(Request $request, $providerId = null)
    {
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';

        $leagues = League::getLeagues($providerId, false, $searchKey, $sortOrder);

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $leagues->count(),
            'pageNum'     => $page,
            'pageData'    => $leagues->offset(($page - 1) * $limit)->limit($limit)->get()
        ]);
    }

    public function getPrimaryProviderMatchedLeagues()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagues = League::getLeagues($providerId)->get();

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'data'        => $leagues
        ]);
    }

    public function getMatchedLeagues(Request $request)
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
                'is_priority' => $data->is_priority,
                'alias'   => $data->alias,
                'leagues' => League::getMatchedLeaguesByMasterLeagueId($data->id)->toArray(),
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
     * Save Grouped Leagues to Database to server as available entries for Leagues Master List
     * 
     * @param  object   Illuminate\Http\Request $request
     *     $request->primary_provider_league_id   int         Dropdown selected value
     *     $request->match_league_id              int         Raw Name ID
     *     $request->master_league_alias          string      Alias Text input
     *     $request->add_master_league            boolean     Checkbox value
     * 
     * @param  string   MatchingFacade $matching   ['leauge', 'team']
     * 
     * @return json
     */
    public function postMatchLeagues(LeaguesMatchingRequest $request)
    {
        return MatchingFacade::postMatchLeagues($request);
    }

    /**
    * Remove Grouped Leagues, teams and events from the Database, recreate the delete records into the unmatched_data table
    * 
    * @param  object   Illuminate\Http\Request $request
    *     $request->league_id               int         league id
    *     $request->provider_id             int         provider id of the league
    *     $request->sport_id                int         sport id of the league
    * 
    * @return json
    */
    public function postUnmatchLeague(LeagueRequest $request)
    {
        return MatchingFacade::unmatchSecondaryLeague($request);
    }

    public function togglePriority(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'master_league_id' => 'required|int|exists:master_leagues,id',
                'is_priority'      => 'boolean'
            ]);

            if ($validator->fails()) {
                return response([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $masterLeague = MasterLeague::find($request->master_league_id);
            $masterLeague->is_priority = $request->is_priority;
            $masterLeague->save();

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'success'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }

    public function updateAlias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'master_league_id' => 'required|int|exists:master_leagues,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $masterLeague = MasterLeague::find($request->master_league_id);
            $masterLeague->update([
                'name' => $request->alias
            ]);

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'League alias was updated.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }
}
