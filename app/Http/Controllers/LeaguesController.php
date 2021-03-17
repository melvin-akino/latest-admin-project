<?php

namespace App\Http\Controllers;

use App\Models\{League, Provider, SystemConfiguration AS SC};
use App\Facades\MatchingFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaguesController extends Controller
{
    /**
     * Get raw `league_names` from paramgeter Provider
     * 
     * @param  Request $request
     * 
     * @return json
     */
    public function getRawLeagues(Request $request)
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

        $data = League::getLeaguesByProvider($request->providerId, $searchKey, $page, $limit, false);

        return response()->json($data);
    }

    public function getLeagues()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagues    = League::getLeaguesByProvider($providerId);

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
