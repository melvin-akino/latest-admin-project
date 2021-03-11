<?php

namespace App\Http\Controllers;

use App\Models\{MasterTeam, Team, Provider, SystemConfiguration AS SC};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TeamsController extends Controller
{
    /**
     * Get raw `team_names` from paramgeter Provider
     * 
     * @param  int $providerId
     * 
     * @return json
     */
    public function getRawTeams($providerId)
    {
        $teamGroups = DB::table('team_groups')->pluck('team_id');
        $data       = Team::whereNotIn('id', $teamGroups)
            ->where('provider_id', $providerId)
            ->get();

        return response()->json($data);
    }

    public function getTeams()
    {
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));
        $teamGroups = DB::table('team_groups')->pluck('team_id');
        $teams      = Team::whereIn('id', $teamGroups)
            ->where('provider_id', $providerId)
            ->get();              

        return response()->json($teams);
    }

    /**
     * Save Grouped Teams to Database to server as available entries for Teams Master List
     * 
     * @param  Request $request
     *     $request->primary_provider_team_id   int         Dropdown selected value
     *     $request->match_team_id              int         Raw Name ID
     *     $request->master_team_alias          string      Alias Text input
     *     $request->add_master_team            boolean     Checkbox value
     * 
     * @return json
     */
    public function postMatchTeams(Request $request)
    {
        DB::beginTransaction();

        try {
            $masterTeamId = MasterTeam::create([
                'sport_id' => 1, // temporary value
                'name'     => $request->master_team_alias
            ])->id;

            if (!$request->add_master_team) {
                $masterTeamId = $request->primary_provider_team_id;
            }

            DB::table('team_groups')
                ->insert([
                    'master_team_id' => $masterTeamId,
                    'team_id'        => $request->match_team_id
                ]);

            DB::commit();

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'success'
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }
}
