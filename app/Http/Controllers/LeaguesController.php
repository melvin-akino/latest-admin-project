<?php

namespace App\Http\Controllers;

use App\Models\{MasterLeague, League, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\RawListRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
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
        $searchKey = '';
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';

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
        $page = 1;
        $limit = 10;
        $sortOrder = 'asc';
        
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';

        $masterLeagues = MasterLeague::orderBy('id', $sortOrder);
        $total = $masterLeagues->count();
        $pageData = $masterLeagues->offset(($page - 1) * $limit)->limit($limit)->get();

        $result = [];

        foreach($pageData as $data) {
            $result[] = [
                'master_league_id' => $data->id,
                'leagues' => League::getMatchedLeaguesByMasterLeagueId($data->id)->toArray()
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
    public function postMatchLeagues(Request $request)
    {
        return MatchingFacade::postMatch($request, 'league');
    }
}
