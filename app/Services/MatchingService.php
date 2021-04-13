<?php

namespace App\Services;

use App\Models\{MasterLeague, MasterTeam, Provider, SystemConfiguration, League, LeagueGroup, Matching, UnmatchedData};
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

                    var_dump('Matching: League: ' . $unmatchedLeague['name'] . ' is now matched');
                    Log::info('Matching: League: ' . $unmatchedLeague['name'] . ' is now matched');
                    
                }
            } else {
                var_dump('Matching: Nothing to match for league from primary provider');
                Log::info('Matching: Nothing to match for league from primary provider');
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function createUnmatchedLeagues() 
    {
        try {
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
            $matching = new Matching;

            $unmatchedLeagueList = League::getAllOtherProviderUnmatchedLeagues($primaryProviderId);

            if (count($unmatchedLeagueList) > 0) {
                foreach($unmatchedLeagueList as $league) {
                    $matching->create('UnmatchedData', [
                        'data_type'     => 'league',
                        'data_id'       => $league['id'],
                        'provider_id'   => $league['provider_id']
                    ]);
                }
            }
            else {
                var_dump('Matching: There are no more leagues to add in the unmatched_data table.');
            }

        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function automatchIdenticalLeagues()
    {
        try {
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
            $matching = new Matching;
            $unmatchedLeagues = UnmatchedData::getUnmatchedLeagueData('league');

            if (count($unmatchedLeagues) > 0) 
            {
                foreach($unmatchedLeagues as $league) {
                    //get a league from raw leagues table where provider_id = primaryProviderId
                    $masterLeague = League::getMasterLeagueId($league['name'], $league['sport_id'], $primaryProviderId);
                    if (!empty($masterLeague))
                    {
                        var_dump('Found a league for automatching with master_league_id: ' . $masterLeague->master_league_id);    
                        $matching->create('LeagueGroup', [
                            'master_league_id' => $masterLeague->master_league_id,
                            'league_id'        => $league['data_id']
                        ]);

                        self::removeFromUnmatchedData('league', $league['provider_id'], $league['data_id']);
                        var_dump('Removed: league_id:'.$league['data_id'].' - provider_id:'.$league['provider_id'].' - leaguename:'.$league['name']);
                    }
                } 
            }
            else {
                var_dump('Matching: There are no more other leagues to automatch.');
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }
}
