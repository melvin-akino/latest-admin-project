<?php

namespace App\Services;

use App\Models\{MasterLeague, MasterTeam, Provider, SystemConfiguration, League, LeagueGroup, Team, TeamGroup, Event, EventGroup, EventMarket, Matching, UnmatchedData};
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

    public static function autoMatchPrimaryLeagues($primaryProviderId)
    {
        try {
            $matching = new Matching;
            $unmatchedLeagues = League::getAllActiveNotExistInPivotByProviderId($primaryProviderId);
            if ($unmatchedLeagues->count() > 0) {
                foreach ($unmatchedLeagues as $unmatchedLeague) {
                    DB::beginTransaction();
                    $masterLeague = $matching->create('MasterLeague', [
                        'sport_id' => $unmatchedLeague['sport_id'],
                        'name'     => null
                    ]);

                    $matching->create('LeagueGroup', [
                        'master_league_id' => $masterLeague->id,
                        'league_id'        => $unmatchedLeague['id']
                    ]);

                    Log::info('Matching: League: ' . $unmatchedLeague['name'] . ' is now matched');
                    DB::commit();
                }
                return 0;
            } else {
                Log::info('Matching: Nothing to match for league from primary provider');
                return 1;
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);

            DB::rollBack();
        }
    }

    public static function autoMatchPrimaryTeams($primaryProviderId)
    {
        try {
            $matching = new Matching;
            $unmatchedTeams = Team::getAllActiveNotExistInPivotByProviderId($primaryProviderId);
            if ($unmatchedTeams->count() > 0) {
                foreach ($unmatchedTeams as $unmatchedTeam) {
                    DB::beginTransaction();
                    $masterTeam = $matching->create('MasterTeam', [
                        'sport_id' => $unmatchedTeam['sport_id'],
                        'name'     => null
                    ]);

                    $matching->create('TeamGroup', [
                        'master_team_id' => $masterTeam->id,
                        'team_id'        => $unmatchedTeam['id']
                    ]);

                    Log::info('Matching: Team: ' . $unmatchedTeam['name'] . ' is now matched');
                    DB::commit();                    
                }
                return 0;
            } else {
                Log::info('Matching: Nothing to match for team from primary provider');
                return 1;
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
            DB::rollBack();
        }
    }

    public static function autoMatchPrimaryEvents($primaryProviderId)
    {
        try {
            $matching          = new Matching;

            //check if event doesnt exist in event groups
            $unmatchedEvents = Event::getAllActiveNotExistInPivotByProviderId($primaryProviderId);
            if ($unmatchedEvents->count() > 0) {
                foreach ($unmatchedEvents as $unmatchedEvent) {
                    DB::beginTransaction();
                    $leagueGroupData = LeagueGroup::getByLeagueId($unmatchedEvent['league_id']);
                    if ($leagueGroupData->count() == 0) {
                        continue;
                    }

                    $teamGroupHomeData = TeamGroup::getByTeamId($unmatchedEvent['team_home_id']);
                    if ($teamGroupHomeData->count() == 0) {
                        continue;
                    }

                    $teamGroupAwayData = TeamGroup::getByTeamId($unmatchedEvent['team_away_id']);
                    if ($teamGroupAwayData->count() == 0) {
                        continue;
                    }                                       
                    
                    //if not, check if event has similar league, home team, away team and ref sched filtered by hour and currently soft deleted and hasEventGroups
                    $event = Event::getSoftDeletedEvent($unmatchedEvent);
                    //if yes, reuse master event 
                    if ($event) {
                        $masterEventId = $event->master_event_id;
                        
                        $matching->delete('EventGroup', [
                            'master_event_id' => $event->master_event_id,
                            'event_id'        => $event->id
                        ]);
                    }
                    else {                   
                        //if no, create master event
                        $masterEventUniqueId = implode('-', [
                            date('Ymd', strtotime($unmatchedEvent['ref_schedule'])),
                            $unmatchedEvent['sport_id'],
                            $leagueGroupData[0]->master_league_id,
                            $unmatchedEvent['event_identifier']
                        ]);

                        $masterEvent = $matching->updateOrCreate('MasterEvent', [
                            'master_event_unique_id' => $masterEventUniqueId
                        ], [
                            'sport_id'            => $unmatchedEvent['sport_id'],
                            'master_league_id'    => $leagueGroupData[0]->master_league_id,
                            'master_team_home_id' => $teamGroupHomeData[0]->master_team_id,
                            'master_team_away_id' => $teamGroupAwayData[0]->master_team_id
                        ]);

                        $masterEventId = $masterEvent->id;
                    }
                    //create event groups
                    $matching->create('EventGroup', [
                        'master_event_id' => $masterEventId,
                        'event_id'        => $unmatchedEvent['id']
                    ]);

                    Log::info('Matching: Event: ' . $unmatchedEvent['event_identifier'] . ' is now matched');
                    DB::commit();
                }
                return 0;
            } else {
                Log::info('Matching: Nothing to match for event from primary provider');
                return 1;
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);

            DB::rollBack();
        }
    }

    public static function createUnmatchedLeagues($primaryProviderId) 
    {
        try
        {
            $matching = new Matching;
            $unmatchedLeagueList = League::getAllOtherProviderUnmatchedLeagues($primaryProviderId);
            Log::info('Matching: Trying to get leagues to add to unmatched_data table');
            if (!empty($unmatchedLeagueList)) {
                Log::info('Matching: There are leagues that needs to be added into unmatched_data table.');
                foreach($unmatchedLeagueList as $league) {
                    DB::beginTransaction();
                    $matching->create('UnmatchedData', [
                        'data_type'     => 'league',
                        'data_id'       => $league['id'],
                        'provider_id'   => $league['provider_id']
                    ]);
                    Log::info('Matching: Creating unmatched data league_id:'.$league['id'].' - provider_id:'.$league['provider_id']);
                    DB::commit();
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more leagues to add in the unmatched_data table.');
                return 2;
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function createUnmatchedTeams($primaryProviderId) 
    {
        try
        {
            $matching = new Matching;
            $unmatchedTeamsList = Team::getAllOtherProviderUnmatchedTeams($primaryProviderId);
            Log::info('Matching: Trying to get teams to add to unmatched_data table');
            if (!empty($unmatchedTeamsList)) {
                foreach($unmatchedTeamsList as $team) {
                    DB::beginTransaction();
                    $matching->create('UnmatchedData', [
                        'data_type'     => 'team',
                        'data_id'       => $team['id'],
                        'provider_id'   => $team['provider_id']
                    ]);
                    Log::info('Matching: Creating unmatched data team_id:'.$team['id'].' - provider_id:'.$team['provider_id']);
                    DB::commit();
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more teams to add in the unmatched_data table.');
                return 2;
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function createUnmatchedEvents($primaryProviderId) 
    {
        try
        {
            $matching = new Matching;
            $unmatchedEventsList = Event::getAllOtherProviderUnmatchedEvents($primaryProviderId);
            Log::info('Matching: Trying to get events to add to unmatched_data table');
            if (count($unmatchedEventsList) > 0) {
                foreach($unmatchedEventsList as $event) {
                    DB::beginTransaction();
                    $matching->create('UnmatchedData', [
                        'data_type'     => 'event',
                        'data_id'       => $event['id'],
                        'provider_id'   => $event['provider_id']
                    ]);
                    Log::info('Matching: Creating unmatched data event_id:'.$event['id'].' - provider_id:'.$event['provider_id']);
                    DB::commit();
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more events to add in the unmatched_data table.');
                return 2;
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function automatchIdenticalLeagues($primaryProviderId)
    {
        try {
            $matching = new Matching;

            $unmatchedLeagues = UnmatchedData::getUnmatchedLeagueData('league');

            if (count($unmatchedLeagues) > 0) 
            {
                $primaryLeagues = League::getPrimaryLeagues($primaryProviderId);
                foreach($unmatchedLeagues as $league) {
                    //get a league from raw leagues table where provider_id = primaryProviderId
                    //$masterLeague = League::getMasterLeagueId($league['name'], $league['sport_id'], $primaryProviderId);
                    foreach($primaryLeagues as $pl) {
                        if ($pl['name'] === $league['name'])
                        {
                            DB::beginTransaction();
                            Log::info('Found a league for automatching with master_league_id: ' . $pl['master_league_id']);    
                            $matching->create('LeagueGroup', [
                                'master_league_id' => $pl['master_league_id'],
                                'league_id'        => $league['data_id']
                            ]);

                            self::removeFromUnmatchedData('league', $league['provider_id'], $league['data_id']);
                            Log::info('Removed from Unmatched: league_id:'.$league['data_id'].' - provider_id:'.$league['provider_id'].' - leaguename:'.$league['name']);
                            DB::commit();
                        }
                        else {
                            //update the is_failed to true here
                            DB::beginTransaction();
                            Log::info('No primary league found for automatching league_id: ' . $league['data_id'] . ' setting is_failed TRUE');    
                            $matching->updateOrCreate('UnmatchedData', [
                                'data_type'     => 'league',
                                'data_id'       => $league['data_id'],
                                'provider_id'   => $league['provider_id']
                            ],
                            ['is_failed'     => true]);
                            DB::commit();
                        }
                    }
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more other leagues to automatch.');
                return 2;
            }            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function automatchIdenticalTeams($primaryProviderId)
    {
        try {
            $matching = new Matching;

            $unmatchedTeams = UnmatchedData::getUnmatchedTeamData('team');

            if (count($unmatchedTeams) > 0) 
            {
                $primaryTeams = Team::getPrimaryTeams($primaryProviderId);
                foreach($unmatchedTeams as $team) {
                    //get a league from raw leagues table where provider_id = primaryProviderId
                    //$masterLeague = League::getMasterLeagueId($league['name'], $league['sport_id'], $primaryProviderId);
                    foreach($primaryTeams as $pt) {
                        if ($pt['name'] === $team['name'])
                        {
                            DB::beginTransaction();
                            Log::info('Found a league for automatching with master_team_id: ' . $pt['master_team_id']);    
                            $matching->create('TeamGroup', [
                                'master_team_id' => $pt['master_team_id'],
                                'team_id'        => $team['data_id']
                            ]);

                            self::removeFromUnmatchedData('team', $team['provider_id'], $team['data_id']);
                            Log::info('Removed from Unmatched: team_id:'.$team['data_id'].' - provider_id:'.$team['provider_id'].' - teamname:'.$team['name']);
                            DB::commit();
                        }
                        else {
                            //update the is_failed to true here
                            DB::beginTransaction();
                            Log::info('No primay team found for automatching team_id: ' . $team['data_id'] . ' setting is_failed TRUE');    
                            $matching->updateOrCreate('UnmatchedData', [
                                'data_type'     => 'team',
                                'data_id'       => $team['data_id'],
                                'provider_id'   => $team['provider_id']
                            ],
                            ['is_failed'     => true]);
                            DB::commit();
                        }
                    }
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more other teams to automatch.');
                return 2;
            }
            DB::commit(); 
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function automatchIdenticalEvents($primaryProviderId)
    {
        
        try {
            
            $matching = new Matching;

            $unmatchedEvents = UnmatchedData::getUnmatchedEventData('event');

            if (count($unmatchedEvents) > 0) 
            {
                foreach($unmatchedEvents as $event) {
                    //get a league from raw leagues table where provider_id = primaryProviderId
                    $masterEvent = Event::getMasterEventId($event, $primaryProviderId);
                    if (!empty($masterEvent))
                    {
                        DB::beginTransaction();
                        Log::info('Found a team for automatching with master_team_id: ' . $masterEvent);    
                        $matching->create('EventGroup', [
                            'master_event_id' => $masterEvent,
                            'event_id'        => $event['data_id']
                        ]);

                        self::removeFromUnmatchedData('event', $event['provider_id'], $event['data_id']);
                        Log::info('Removed from Unmatched: event_id:'.$event['data_id'].' - provider_id:'.$event['provider_id']);
                        DB::commit(); 
                    }
                    else {
                        //update the is_failed to true here
                        DB::beginTransaction();
                        Log::info('No primary event found for automatching for event_id: ' . $event['data_id'] . ' setting is_failed TRUE');    
                        $matching->updateOrCreate('UnmatchedData', [
                            'data_type'     => 'event',
                            'data_id'       => $event['data_id'],
                            'provider_id'   => $event['provider_id']
                        ],
                        ['is_failed'     => true]);
                        DB::commit();
                    }
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more other events to automatch.');
                return 3;
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
        }
    }
}
