<?php

namespace App\Http\Controllers;

use App\Models\{Team, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\{RawListRequest, TeamsMatchingRequest};
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
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
        return RawListingFacade::getByProvider($request, 'team');
    }

    public function getTeams()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $teams      = Team::getByProvider($providerId);

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
    public function postMatchTeams(TeamsMatchingRequest $request)
    {
        return MatchingFacade::postMatchTeams($request);
    }
}
