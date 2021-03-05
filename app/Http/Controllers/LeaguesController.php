<?php

namespace App\Http\Controllers;

use App\Models\League;
use Illuminate\Http\Request;

class LeaguesController extends Controller
{
    public function getLeaguesByProviderId($providerId)
    {
        $leagues = League::where('provider_id', $providerId)->get();              

        return response()->json($leagues);
    }
}
