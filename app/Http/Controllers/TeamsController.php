<?php

namespace App\Http\Controllers;

use App\Models\{Team, Provider, SystemConfiguration AS SC};
use App\Facades\MatchingFacade;
use App\Http\Requests\RawListRequest;
use Illuminate\Support\Facades\Validator;

class TeamsController extends Controller
{
    /**
     * Get raw `team_names` from paramgeter Provider
     * 
     * @param  RawListRequest $request
     * 
     * @return json
     */
    public function getRawTeams(RawListRequest $request)
    {
        $searchKey = '';
        $page = 1;
        $limit = 10;

        if ($request->has('searchKey')) $searchKey = $request->searchKey;

        if ($request->has('page')) $page = $request->page;

        if ($request->has('limit')) $limit = $request->limit;

        $data = Team::getTeamsByProvider($request->providerId, $searchKey, false);

        $result = [
            'total' => $data->count(),
            'pageNum' => $page,
            'pageData' => $data->skip(($page - 1) * $limit)->take($limit)
        ];

        return response()->json($result);
    }

    public function getTeams()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $teams      = Team::getTeamsByProvider($providerId);

        return response()->json($teams);
    }

    /**
     * Save Grouped Teams to Database to server as available entries for Teams Master List
     * 
     * @param  object   Illuminate\Http\Request $request
     *     $request->primary_provider_team_id   int         Dropdown selected value
     *     $request->match_team_id              int         Raw Name ID
     *     $request->master_team_alias          string      Alias Text input
     *     $request->add_master_team            boolean     Checkbox value
     * 
     * @param  string   MatchingFacade $matching   ['leauge', 'team']
     * 
     * @return json
     */
    public function postMatchTeams(Request $request)
    {
        return MatchingFacade::postMatch($request, 'team');
    }
}
