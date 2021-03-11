<?php

namespace App\Http\Controllers;

use App\Models\{Team, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamsController extends Controller
{
    public function getTeams()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $teamGroups = DB::table('team_groups')->pluck('team_id');
        $teams      = Team::whereIn('id', $teamGroups)
            ->where('provider_id', $providerId)
            ->get();              

        return response()->json($teams);
    }
}
