<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamsController extends Controller
{
    public function getTeamsByProviderId($providerId)
    {
        $teams = Team::where('provider_id', $providerId)->get();              

        return response()->json($teams);
    }
}
