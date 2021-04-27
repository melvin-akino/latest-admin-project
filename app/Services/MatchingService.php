<?php

namespace App\Services;

use App\Models\{
    MasterLeague,
    MasterTeam,
    Provider,
    SystemConfiguration,
    League,
    LeagueGroup,
    Team,
    TeamGroup,
    Event,
    EventGroup,
    EventMarket,
    Matching,
    UnmatchedData,
    User
};
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Spatie\Activitylog\Contracts\Activity;

class MatchingService
{
    public function postMatchLeagues(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!$request->add_master_league) {
                $masterId = LeagueGroup::where('league_id', $request->primary_provider_league_id)
                    ->first()
                    ->master_league_id;
            } else {
                $sportId = League::where('id', $request->match_league_id)
                    ->first()
                    ->sport_id;

                $masterId = MasterLeague::create([
                    'sport_id' => $sportId,
                    'name'     => $request->master_league_alias
                ])->id;
            }

            LeagueGroup::create([
                'master_league_id' => $masterId,
                'league_id'        => $request->match_league_id,
            ]);

            $providerId = League::where('id', $request->match_league_id)
                ->first()
                ->provider_id;

            self::removeFromUnmatchedData('league', $providerId, $request->match_league_id);

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

    public function postMatchTeams(Request $request)
    {
        
        DB::beginTransaction();

        try {
            if (!$request->add_master_team) {
                $masterId = TeamGroup::where('team_id', $request->primary_provider_team_id)
                    ->first()
                    ->master_team_id;
            } else {
                $sportId = Team::where('id', $request->match_team_id)
                    ->first()
                    ->sport_id;

                $masterId = MasterTeam::create([
                    'sport_id' => $sportId,
                    'name'     => $request->master_team_alias
                ])->id;
            }

            TeamGroup::create([
                'master_team_id' => $masterId,
                'team_id'        => $request->match_team_id,
            ]);

            $providerId = Team::where('id', $request->match_team_id)
                ->first()
                ->provider_id;

            self::removeFromUnmatchedData('team', $providerId, $request->match_team_id);

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
            Log::info('Getting all unmatched HG events:');
            Log::info((array) $unmatchedEvents);
            if ($unmatchedEvents->count() > 0) {
                foreach ($unmatchedEvents as $unmatchedEvent) {
                    $leagueGroupData = LeagueGroup::getByLeagueId($unmatchedEvent['league_id']);
                    if ($leagueGroupData->count() == 0) {
                        Log::info('No Master league for this primary league_id: '. $unmatchedEvent['league_id']);
                        continue;
                    }

                    $teamGroupHomeData = TeamGroup::getByTeamId($unmatchedEvent['team_home_id']);
                    if ($teamGroupHomeData->count() == 0) {
                        Log::info('No Master Home Team for this team_id: ' . $unmatchedEvent['team_home_id']);
                        continue;
                    }

                    $teamGroupAwayData = TeamGroup::getByTeamId($unmatchedEvent['team_away_id']);
                    if ($teamGroupAwayData->count() == 0) {
                        Log::info('No Master Away Team for this team_id: ' . $unmatchedEvent['team_away_id']);
                        continue;
                    }                                       
                    
                    //if not, check if event has similar league, home team, away team and ref sched filtered by hour and currently soft deleted and hasEventGroups
                    $event = Event::getSoftDeletedEvent($unmatchedEvent);
                    Log::info('Getting soft deleted event with the same info as this event:');
                    Log::info((array) $event);
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
                }
                return 0;
            } else {
                Log::info('Matching: Nothing to match for event from primary provider');
                return 1;
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);

            if ($unmatchedEvents->count() > 0) {
                foreach ($unmatchedEvents as $unmatchedEvent) {
                    Log::info('Matching: Attempting to unmatch event ' . $unmatchedEvent['event_identifier'] . ' because of some error exception');
                    $matching->delete('EventGroup', [
                        'event_id' => $unmatchedEvent['id']
                    ]);
                }
            }
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
                            continue 2;
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
                            Log::info('Found a team for automatching with master_team_id: ' . $pt['master_team_id']);    
                            $matching->create('TeamGroup', [
                                'master_team_id' => $pt['master_team_id'],
                                'team_id'        => $team['data_id']
                            ]);

                            self::removeFromUnmatchedData('team', $team['provider_id'], $team['data_id']);
                            Log::info('Removed from Unmatched: team_id:'.$team['data_id'].' - provider_id:'.$team['provider_id'].' - teamname:'.$team['name']);
                            DB::commit();
                            continue 2;
                        }
                        else {
                            //update the is_failed to true here
                            DB::beginTransaction();
                            Log::info('No primary team found for automatching team_id: ' . $team['data_id'] . ' setting is_failed TRUE');    
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
                        Log::info('Found an event for automatching with master_event_id: ' . $masterEvent);    
                        $matching->create('EventGroup', [
                            'master_event_id' => $masterEvent,
                            'event_id'        => $event['data_id']
                        ]);

                        self::removeFromUnmatchedData('event', $event['provider_id'], $event['data_id']);
                        Log::info('Removed from Unmatched: event_id:'.$event['data_id'].' - provider_id:'.$event['provider_id']);
                    }
                    else {
                        //update the is_failed to true here
                        Log::info('No primary event found for automatching for event_id: ' . $event['data_id'] . ' setting is_failed TRUE');    
                        $matching->updateOrCreate('UnmatchedData', [
                            'data_type'     => 'event',
                            'data_id'       => $event['data_id'],
                            'provider_id'   => $event['provider_id']
                        ],
                        ['is_failed'     => true]);
                    }
                }
                return 0;
            }
            else {
                Log::info('Matching: There are no more other events to automatch.');
                return 3;
            }
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);

            if ($unmatchedEvents->count() > 0) {
                foreach ($unmatchedEvents as $event) {
                    Log::info('Matching: Attempting to unmatch event ' . $event['event_identifier'] . ' because of some error exception');
                    $matching->delete('EventGroup', [
                        'event_id' => $event['data_id']
                    ]);
                }
            }
        }
    }

    public static function logActivity($logName, $modelName, $data, $logMessage)
    {
        try {
            DB::table('activity_log')->insert([
                'log_name'     => $logName,
                'description'  => $logMessage,
                'subject_type' => "App\\Models\\" . $modelName,
                'subject_id'   => null,
                'causer_type'  => "App\\Models\\AdminUser",
                'causer_id'    => auth()->user()->id,
                'properties'   => json_encode([
                    'attributes' => $data,
                    'action'     => "Deleted",
                    'ip_address' => request()->ip(),
                ]),
                'created_at'   => Carbon::now(),
            ]);
        } catch (Exception $e) {
            Log::error('Something went wrong', (array) $e);
        }
    }

    public static function unmatchSecondaryLeague(Request $request) 
    {
        try
        {
            DB::beginTransaction();
            
            $leagueInfo = League::getLeagueInfo($request->league_id, $request->provider_id, $request->sport_id);
            
            $matching = new Matching;

            //Delete this league from the groups table
            $matching->delete('LeagueGroup', [
                'master_league_id' => $leagueInfo->master_league_id,
                'league_id'        => $request->league_id
            ]);
            Log::info('Matching: Removing this league_id:'.$request->league_id.' from league_groups table');

            self::logActivity(
                'Leagues Matching',
                'LeagueGroup', // indicate sub-folder if necessary
                [
                    'master_league_id' => $leagueInfo->master_league_id,
                    'league_id'        => $request->league_id,
                ],
                "Unmatched Raw League ID " . $request->league_id . " to Master League ID " . $leagueInfo->master_league_id,
            );

            //Add this league into the unmatched_table
            $matching->create('UnmatchedData', [
                'data_type'     => 'league',
                'data_id'       => $request->league_id,
                'provider_id'   => $request->provider_id
            ]);
            Log::info('Matching: Recreating unmatched data for league_id:'.$request->league_id.' - provider_id:'.$request->provider_id);

            //Now let's get all associated events and teams for this league and unmatch them too
            $events = Event::getLeagueEvents($request->league_id, $request->provider_id, $request->sport_id);
            if ($events) {
                foreach($events as $event) {
                    //Delete this home team from the team groups table
                    $matching->delete('TeamGroup', [
                        'master_team_id' => $event->team_master_home_id,
                        'team_id'        => $event->team_home_id
                    ]);
                    Log::info('Matching: Removing this home_team_id:'.$event->team_home_id.' from team_groups table with master_team_id:'.$event->team_master_home_id);

                    self::logActivity(
                        'Teams Matching',
                        'TeamGroup', // indicate sub-folder if necessary
                        [
                            'master_team_id' => $event->team_master_home_id,
                            'team_id'        => $event->team_home_id
                        ],
                        "Unmatched Raw Team ID " . $event->team_home_id . " to Master Team ID " . $event->team_master_home_id,
                    );

                    //Add this home team into the unmatched_table
                    $matching->updateOrCreate('UnmatchedData', [
                        'data_type'     => 'team',
                        'data_id'       => $event->team_home_id,
                        'provider_id'   => $request->provider_id
                    ],
                    ['is_failed'     => false]);

                    Log::info('Matching: Recreating unmatched data for home_team_id:'.$event->team_home_id.' - provider_id:'.$request->provider_id);

                    //Delete this away team from the team groups table
                    $matching->delete('TeamGroup', [
                        'master_team_id' => $event->team_master_away_id,
                        'team_id'        => $event->team_away_id
                    ]);
                    Log::info('Matching: Removing this away_team_id:'.$event->team_away_id.' from team_groups table with master_team_id:'.$event->team_master_away_id);

                    self::logActivity(
                        'Teams Matching',
                        'TeamGroup', // indicate sub-folder if necessary
                        [
                            'master_team_id' => $event->team_master_away_id,
                            'team_id'        => $event->team_away_id
                        ],
                        "Unmatched Raw Team ID " . $event->team_away_id . " to Master Team ID " . $event->team_master_away_id,
                    );

                    //Add this away team into the unmatched_table
                    $matching->updateOrCreate('UnmatchedData', [
                        'data_type'     => 'team',
                        'data_id'       => $event->team_away_id,
                        'provider_id'   => $request->provider_id
                    ],
                    ['is_failed'     => false]);
                    Log::info('Matching: Recreating unmatched data for away_team_id:'.$event->team_away_id.' - provider_id:'.$request->provider_id);


                    //Delete this event from the event groups table
                    $matching->delete('EventGroup', [
                        'master_event_id' => $event->master_event_id,
                        'event_id'        => $event->id
                    ]);
                    Log::info('Matching: Removing this event_id:'.$event->id.' from team_groups table with master_team_id:'.$event->master_event_id);

                    self::logActivity(
                        'Events Matching',
                        'EventGroup', // indicate sub-folder if necessary
                        [
                            'master_event_id' => $event->master_event_id,
                            'event_id'        => $event->id
                        ],
                        "Unmatched Raw Event ID " . $event->id . " to Master Event ID " . $event->master_event_id,
                    );

                    //Add this home team into the unmatched_table
                    $matching->updateOrCreate('UnmatchedData', [
                        'data_type'     => 'event',
                        'data_id'       => $event->id,
                        'provider_id'   => $request->provider_id
                    ],['is_failed'     => false]);
                    Log::info('Matching: Recreating unmatched data for event_id:'.$event->id.' - provider_id:'.$request->provider_id);
                }
            }

            DB::commit();
            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'success'
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }

    public static function unmatchSecondaryEvent(Request $request) 
    {
        try
        {           
            $matching = new Matching;

            //Get event information
            $event = Event::getEventInfo($request->event_id, $request->provider_id, $request->sport_id);

            if (!empty($event))
            {
                DB::beginTransaction();
                //Delete this home team from the team groups table
                $matching->delete('TeamGroup', [
                    'master_team_id' => $event->team_master_home_id,
                    'team_id'        => $event->team_home_id
                ]);
                Log::info('Matching: Removing this home_team_id:'.$event->team_home_id.' from team_groups table with master_team_id:'.$event->team_master_home_id);

                self::logActivity(
                    'Teams Matching',
                    'TeamGroup', // indicate sub-folder if necessary
                    [
                        'master_team_id' => $event->team_master_home_id,
                        'team_id'        => $event->team_home_id
                    ],
                    "Unmatched Raw Team ID " . $event->team_home_id . " to Master Team ID " . $event->team_master_home_id,
                );

                //Add this home team into the unmatched_table
                $matching->updateOrCreate('UnmatchedData', [
                    'data_type'     => 'team',
                    'data_id'       => $event->team_home_id,
                    'provider_id'   => $request->provider_id
                ],
                ['is_failed'     => false]);
                Log::info('Matching: Recreating unmatched data for home_team_id:'.$event->team_home_id.' - provider_id:'.$request->provider_id);

                //Delete this away team from the team groups table
                $matching->delete('TeamGroup', [
                    'master_team_id' => $event->team_master_away_id,
                    'team_id'        => $event->team_away_id
                ]);
                Log::info('Matching: Removing this away_team_id:'.$event->team_away_id.' from team_groups table with master_team_id:'.$event->team_master_away_id);

                self::logActivity(
                    'Teams Matching',
                    'TeamGroup', // indicate sub-folder if necessary
                    [
                        'master_team_id' => $event->team_master_away_id,
                        'team_id'        => $event->team_away_id
                    ],
                    "Unmatched Raw Team ID " . $event->team_away_id . " to Master Team ID " . $event->team_master_away_id,
                );

                //Add this away team into the unmatched_table
                $matching->updateOrCreate('UnmatchedData', [
                    'data_type'     => 'team',
                    'data_id'       => $event->team_away_id,
                    'provider_id'   => $request->provider_id
                ],
                ['is_failed'     => false]);
                Log::info('Matching: Recreating unmatched data for away_team_id:'.$event->team_away_id.' - provider_id:'.$request->provider_id);

                //Delete this event from the event groups table
                $matching->delete('EventGroup', [
                    'master_event_id' => $event->master_event_id,
                    'event_id'        => $event->id
                ]);
                Log::info('Matching: Removing this event_id:'.$event->id.' from team_groups table with master_team_id:'.$event->master_event_id);

                self::logActivity(
                    'Events Matching',
                    'EventGroup', // indicate sub-folder if necessary
                    [
                        'master_event_id' => $event->master_event_id,
                        'event_id'        => $event->id
                    ],
                    "Unmatched Raw Event ID " . $event->id . " to Master Event ID " . $event->master_event_id,
                );

                //Add this home team into the unmatched_table
                $matching->updateOrCreate('UnmatchedData', [
                    'data_type'     => 'event',
                    'data_id'       => $event->id,
                    'provider_id'   => $request->provider_id
                ],['is_failed'     => false]);
                Log::info('Matching: Recreating unmatched data for event_id:'.$event->id.' - provider_id:'.$request->provider_id);

                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'success'
                ], 200);
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }

    public static function setAllFailedMatchingToFalse() 
    {
        try {
            $matching = new Matching;

            $failedData = UnmatchedData::getAllFailedData();
            
            if (count($failedData) > 0) 
            {

                DB::beginTransaction();    
                $matching->updateOrCreate('UnmatchedData', 
                    ['is_failed'     => true],
                    ['is_failed'     => false]);

                self::logActivity(
                    'Matching Reprocess',
                    'UnmatchedData', // indicate sub-folder if necessary
                    $failedData,
                    "For automatch reprocessing all failed in automatching set is_failed=FALSE",
                );
                DB::commit();
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => "Reprocessed ".count($failedData)." data"
                ], 200);
            }
            else {
                Log::info('Matching: There are no more data in unmatched.');
                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'No data to reprocess'
                ], 200);
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Something went wrong', (array) $e);
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'errors'      => $e->getMessage()
            ], 500);
        }
    }
}
