<?php

namespace App\Http\Controllers;

use App\Models\{League, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaguesController extends Controller
{
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
