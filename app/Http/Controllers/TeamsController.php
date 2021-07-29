<?php

namespace App\Http\Controllers;

use App\Models\{Team, Provider, SystemConfiguration AS SC, MasterTeam};
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

    public function getMatchedTeams(Request $request)
    {
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $providerId = Provider::getIdFromAlias(SC::getValueByType('PRIMARY_PROVIDER'));

        $masterTeams = MasterTeam::getMasterTeams($providerId, $searchKey, $sortOrder);
        $total = $masterTeams->count();
        $pageData = $masterTeams->offset(($page - 1) * $limit)->limit($limit)->get();

        $result = [];

        foreach($pageData as $data) {
            $result[] = [
                'master_team_id' => $data->id,
                'alias' => $data->alias,
                'teams' => Team::getMatchedTeamsByMasterTeamId($data->id)->toArray(),
            ];
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $total,
            'pageNum'     => $page,
            'pageData'    => $result
        ]);
    }

    public function updateAlias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'master_team_id' => 'required|int|exists:master_teams,id',
            ]);

            if ($validator->fails()) {
                return response([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $masterTeam = MasterTeam::find($request->master_team_id);
            $masterTeam->update([
                'name' => $request->alias
            ]);

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'Team alias was updated.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }
}
