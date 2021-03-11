<?php

namespace App\Http\Controllers;

use App\Models\{League, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaguesController extends Controller
{
    /**
     * Get raw `league_names` from paramgeter Provider
     * 
     * @param  int $providerId
     * 
     * @return json
     */
    public function getRawLeagues($providerId)
    {
        $leagueGroups = DB::table('league_groups')->pluck('league_id');
        $data         = League::whereNotIn('id', $leagueGroups)
            ->where('provider_id', $providerId)
            ->get();

        return response()->json($data);
    }

    public function getLeagues()
    {
        $providerId   = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $leagueGroups = DB::table('league_groups')->pluck('league_id');
        $leagues      = League::whereIn('id', $leagueGroups)
            ->where('provider_id', $providerId)
            ->get();

        return response()->json($leagues);
    }
}
