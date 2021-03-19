<?php

namespace App\Http\Controllers;

use App\Models\{League, Provider, SystemConfiguration AS SC};
use App\Facades\{RawListingFacade, MatchingFacade};
use App\Http\Requests\RawListRequest;
use Illuminate\Support\Facades\Validator;

class LeaguesController extends Controller
{
    /**
     * Get raw `league_names` from paramgeter Provider
     * 
     * @param  RawListRequest $request
     * 
     * @return json
     */
    public function getRawLeagues(RawListRequest $request)
    {
        return RawListingFacade::getByProvider($request, 'league');
    }

    public function getLeagues()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagues    = League::getByProvider($providerId);

        return response()->json($leagues);
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
