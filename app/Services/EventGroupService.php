<?php

namespace App\Services;

use App\Http\Requests\EventGroupRequest;
use Illuminate\Support\Facades\{DB, Log};
use App\Models\{Event, EventGroup, LeagueGroup, TeamGroup};
use App\Services\MatchingService;
use Exception;

class EventGroupService
{
    public static function match(EventGroupRequest $request)
    {
        DB::beginTransaction();

        try {
            $primaryProviderEventId = $request->primary_provider_event_id;
            $matchEventId = $request->match_event_id;

            $primaryProviderEvent = Event::find($primaryProviderEventId)
            $matchEvent = Event::find($matchEventId);

            // check if league is already matched
            $matchLeagueId = $matchEvent->league_id;
            $matchMasterLeagueId = self::getMasterLeagueId($matchLeagueId);
            if (!$matchMasterLeagueId) {
                $leagueGroup = new LeagueGroup([
                    'master_league_id' => self::getMasterLeagueId($primaryProviderEvent->league_id),
                    'league_id'        => $matchLeagueId
                ]);

                if ($leagueGroup->save()) {
                    $providerId = $matchEvent->provider_id;
    
                    MatchingService::removeFromUnmatchedData('league', $providerId, $matchLeagueId);
    
                    DB::commit();
                }
            }

            // check if team home is already matched
            $matchTeamHomeId = $matchEvent->team_home_id;
            $matchMasterTeamHomeId = self::getMasterTeamId($matchTeamHomeId);
            if (!$matchMasterTeamHomeId) {
                $teamGroup = new TeamGroup([
                    'master_team_id' => self::getMasterTeamId($primaryProviderEvent->team_home_id),
                    'team_id'        => $matchTeamHomeId
                ]);

                if ($teamGroup->save()) {
                    $providerId = $matchEvent->provider_id;
    
                    MatchingService::removeFromUnmatchedData('team', $providerId, $matchTeamHomeId);
    
                    DB::commit();
                }
            }

            // check if team away is already matched
            $matchTeamAwayId = $matchEvent->team_away_id;
            $matchMasterTeamAwayId = self::getMasterTeamId($matchTeamAwayId);
            if (!$matchMasterTeamAwayId) {
                $teamGroup = new TeamGroup([
                    'master_team_id' => self::getMasterTeamId($primaryProviderEvent->team_away_id),
                    'team_id'        => $matchTeamAwayId
                ]);

                if ($teamGroup->save()) {
                    $providerId = $matchEvent->provider_id;
    
                    MatchingService::removeFromUnmatchedData('team', $providerId, $matchTeamAwayId);
    
                    DB::commit();
                }
            }

            $eventGroup = new EventGroup([
                'master_event_id' => self::getMasterEventId($primaryProviderEventId),
                'event_id'        => $matchEventId
            ]);

            if ($eventGroup->save()) {
                $providerId = $matchEvent->provider_id;

                MatchingService::removeFromUnmatchedData('event', $providerId, $matchEventId);

                DB::commit();

                return response()->json([
                    'status'      => true,
                    'status_code' => 200,
                    'message'     => 'Event Group has been successfully added.',
                ], 200);
            }
        } catch (Exception $e) {
            DB::rollBack();

            Log::info('Creating event group for event id: ' . $request->match_event_id . ' has failed.');
            Log::error($e->getMessage());

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'error'       => trans('responses.internal-error')
            ], 500);
        }
    }

    private static function getMasterLeagueId($leagueId) {
        return LeagueGroup::where('league_id', $leagueId)->first()->master_league_id;
    }

    private static function getMasterTeamId($teamId) {
        return TeamGroup::where('team_id', $teamId)->first()->master_team_id;
    }

    private static function getMasterEventId($primaryProviderEventId) {
        return EventGroup::where('event_id', $primaryProviderEventId)->first()->master_event_id;
    }
}
