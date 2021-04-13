<?php

namespace App\Services;

use App\Models\{MasterLeague, MasterTeam, Provider, SystemConfiguration, League, LeagueGroup, Team, Matching};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Exception;
use Validator;

class MatchingService
{
    public function postMatch(Request $request, string $type)
    {
        DB::beginTransaction();

        try {
            $types = [
                'league' => MasterLeague::class,
                'team'   => MasterTeam::class,
            ];

            $master = !$request->{ 'add_master_' . $type } ? 'required|numeric|exists:' . $type . '_groups,' . $type . '_id' : '';
            $raw    = 'numeric|exists:' . $type . 's,id';
            $alias  = $request->{ 'add_master_' . $type } ? 'required|min:1|max:100' : 'max:100';

            $validator = Validator::make($request->all(), [
                'primary_provider_' . $type . '_id' => $master,
                'match_' . $type . '_id'            => $raw,
                'master_' . $type . '_alias'        => $alias,
                'add_master_' . $type               => 'boolean',
            ]);
    
            if ($validator->fails()) {
                return response([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            if (!$request->{ 'add_master_' . $type }) {
                $masterId = DB::table($type . '_groups')
                    ->where($type . '_id', $request->{ 'primary_provider_' . $type . '_id' })
                    ->first()
                    ->{ 'master_' . $type . '_id' };
            } else {
                $sportId = DB::table($type . 's')
                    ->where('id', $request->{ 'match_' . $type . '_id' })
                    ->first()
                    ->sport_id;

                $masterId = $types[$type]::create([
                    'sport_id' => $sportId,
                    'name'     => $request->{ 'master_' . $type . '_alias' }
                ])->id;
            }

            DB::table($type . '_groups')
                ->insert([
                    'master_' . $type . '_id' => $masterId,
                    $type . '_id'             => $request->{ 'match_' . $type . '_id' }
                ]);

            $providerId = DB::table($type . 's')
                ->where('id', $request->{ 'match_' . $type . '_id' })
                ->first()
                ->provider_id;

            self::removeFromUnmatchedData(strtolower($type), $providerId, $request->{ 'match_' . $type . '_id' });

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

    /**
     * Remove Processed data to `unmatched_data` Database Table.
     * 
     * @param  string $type
     * @param  int    $providerId
     * @param  int    $id
     */
    public static function removeFromUnmatchedData(string $type, int $providerId, int $id)
    {
        $unmatched = DB::table('unmatched_data')
            ->where('data_type', strtolower($type))
            ->where('provider_id', $providerId)
            ->where('data_id', $id);

        if ($unmatched->count()) {
            $unmatched->delete();
        }
    }

    public static function autoMatchPrimaryLeagues()
    {
        try {
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
            $matching = new Matching;

            $unmatchedLeagues = League::getAllActiveNotExistInPivotByProviderId($primaryProviderId);
            if ($unmatchedLeagues->count() > 0) {
                foreach ($unmatchedLeagues as $unmatchedLeague) {
                    $masterLeague = $matching->create('MasterLeague', [
                        'sport_id' => $unmatchedLeague['sport_id'],
                        'name'     => null
                    ]);

                    $matching->create('LeagueGroup', [
                        'master_league_id' => $masterLeague->id,
                        'league_id'        => $unmatchedLeague['id']
                    ]);

                    Log::info('Matching: League: ' . $unmatchedLeague['name'] . ' is now matched');
                    
                }
            } else {
                Log::info('Matching: Nothing to match for league from primary provider');
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function autoMatchPrimaryTeams()
    {
        try {
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
            $matching = new Matching;

            $unmatchedTeams = Team::getAllActiveNotExistInPivotByProviderId($primaryProviderId);
            if ($unmatchedTeams->count() > 0) {
                foreach ($unmatchedTeams as $unmatchedTeam) {
                    $masterTeam = $matching->create('MasterTeam', [
                        'sport_id' => $unmatchedTeam['sport_id'],
                        'name'     => null
                    ]);

                    $matching->create('TeamGroup', [
                        'master_team_id' => $masterTeam->id,
                        'team_id'        => $unmatchedTeam['id']
                    ]);

                    Log::info('Matching: Team: ' . $unmatchedTeam['name'] . ' is now matched');
                    
                }
            } else {
                Log::info('Matching: Nothing to match for team from primary provider');
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }
}
