<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, MasterLeague, League, MasterEvent, Event, EventGroup, Provider};
use App\Facades\MatchingFacade;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    public function getHistory(Request $request)
    {
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $sortOrder = $request->has('sortOrder') ? $request->sortOrder : 'asc';
        $data      = [];
        $count     = ActivityLog::getMatchingLogs($searchKey)->count();
        $get       = ActivityLog::getMatchingLogs($searchKey)->limit($limit)->offset(($page - 1) * $limit)->orderBy('id', $sortOrder)->get();

        foreach ($get AS $row) {
            $desc        = "";
            $action      = "";
            $options     = [];
            $props       = json_decode($row->properties);
            $autoMatchIP = "127.0.0.1";

            if (strpos($row->log_name, 'Leagues') !== false) {
                $getMaster             = MasterLeague::getLeagueBaseName($props->attributes->master_league_id);
                $getRaw                = League::withTrashed()->find($props->attributes->league_id);

                if ($props->ip_address == $autoMatchIP) {
                    $description = [
                        'master' => [
                            'name'     => $getMaster->leaguename,
                            'provider' => strtoupper($getMaster->provider),
                        ],
                        'raw' => [
                            'name'     => $getRaw->name,
                            'provider' => strtoupper(Provider::find($getRaw->provider_id)->alias),
                        ]
                    ];
                    $action      = "Auto-Matched";
                    $options     = [
                        'type'        => 'leagues',
                        'master_id'   => $props->attributes->master_league_id,
                        'raw_id'      => $props->attributes->league_id,
                        'sport_id'    => $getRaw->sport_id,
                        'provider_id' => $getRaw->provider_id,
                    ];
                } else {
                    $checkRawLeagueProvider = League::checkRawLeagueProvider($props->attributes->league_id);

                    if ($checkRawLeagueProvider) {
                        $description = [
                            'master' => [
                                'name'     => $getMaster->leaguename,
                                'provider' => strtoupper($getMaster->provider),
                            ],
                            'raw' => [
                                'name'     => $getRaw->name,
                                'provider' => strtoupper(Provider::find($getRaw->provider_id)->alias),
                            ]
                        ];
                        $action      = "Auto-Matched";
                        $options     = [
                            'type'        => 'league',
                            'master_id'   => $props->attributes->master_league_id,
                            'raw_id'      => $props->attributes->league_id,
                            'sport_id'    => $getRaw->sport_id,
                            'provider_id' => $getRaw->provider_id,
                        ];
                    } else {
                        $description = [
                            'master' => [
                                'name'     => $getMaster->leaguename,
                                'provider' => strtoupper($getMaster->provider),
                            ],
                            'raw' => [
                                'name'     => $getRaw->name,
                                'provider' => strtoupper(Provider::find($getRaw->provider_id)->alias),
                            ]
                        ];
                        $action      = strtolower($props->action) == "deleted" ? "Unmatched" : "Matched";
                        $options     = [
                            'type'        => 'leagues',
                            'master_id'   => $props->attributes->master_league_id,
                            'raw_id'      => $props->attributes->league_id,
                            'sport_id'    => $getRaw->sport_id,
                            'provider_id' => $getRaw->provider_id,
                        ];
                    }
                }
            } else if (strpos($row->log_name, 'Events') !== false) {
                $checkMatchExist       = EventGroup::checkMatchExist($props->attributes->master_event_id, $props->attributes->event_id);
                $getRaw                = Event::withTrashed()->find($props->attributes->event_id);
                $getPrimaryEventInfo   = MasterEvent::getEventInfo($props->attributes->master_event_id);
                $getSecondaryEventInfo = Event::getEventData($props->attributes->event_id);

                if ($checkMatchExist) {
                    $checkRawEventProvider = Event::checkRawEventProvider($props->attributes->event_id);

                    if ($props->ip_address == $autoMatchIP) {
                        $description = [
                            [ 'primary_alias' => $getPrimaryEventInfo->primary_alias, 'secondary_alias' => $getSecondaryEventInfo->alias ], # Provider
                            [ 'primary_event_id' => $getPrimaryEventInfo->primary_event_id, 'secondary_event_id' => $getSecondaryEventInfo->event_id ], # Event ID
                            [ 'primary_league_name' => $getPrimaryEventInfo->primary_league_name, 'secondary_league_name' => $getSecondaryEventInfo->league_name ], # League Name
                            [ 'primary_team_home_name' => $getPrimaryEventInfo->primary_team_home_name, 'secondary_team_home_name' => $getSecondaryEventInfo->team_home_name ], # Home
                            [ 'primary_team_away_name' => $getPrimaryEventInfo->primary_team_away_name, 'secondary_team_away_name' => $getSecondaryEventInfo->team_away_name ], # Away
                            [ 'primary_ref_schedule' => $getPrimaryEventInfo->primary_ref_schedule, 'secondary_ref_schedule' => $getSecondaryEventInfo->ref_schedule ], # Schedule
                        ];
                        $action      = "Auto-Matched";
                        $options     = [
                            'type'        => "events",
                            'master_id'   => $props->attributes->master_event_id,
                            'raw_id'      => $props->attributes->event_id,
                            'sport_id'    => $getRaw->sport_id,
                            'provider_id' => $getRaw->provider_id,
                        ];
                    } else {
                        if ($checkRawEventProvider) {
                            $description = "From Auto-Matching";
                            $action      = "Auto-Matched";
                            $options     = [
                                'type'        => "event",
                                'master_id'   => $props->attributes->master_event_id,
                                'raw_id'      => $props->attributes->event_id,
                                'sport_id'    => $getRaw->sport_id,
                                'provider_id' => $getRaw->provider_id,
                            ];
                        } else {
                            $description = [
                                [ 'primary_alias' => $getPrimaryEventInfo->primary_alias, 'secondary_alias' => $getSecondaryEventInfo->alias ], # Provider
                                [ 'primary_event_id' => $getPrimaryEventInfo->primary_event_id, 'secondary_event_id' => $getSecondaryEventInfo->event_id ], # Event ID
                                [ 'primary_league_name' => $getPrimaryEventInfo->primary_league_name, 'secondary_league_name' => $getSecondaryEventInfo->league_name ], # League Name
                                [ 'primary_team_home_name' => $getPrimaryEventInfo->primary_team_home_name, 'secondary_team_home_name' => $getSecondaryEventInfo->team_home_name ], # Home
                                [ 'primary_team_away_name' => $getPrimaryEventInfo->primary_team_away_name, 'secondary_team_away_name' => $getSecondaryEventInfo->team_away_name ], # Away
                                [ 'primary_ref_schedule' => $getPrimaryEventInfo->primary_ref_schedule, 'secondary_ref_schedule' => $getSecondaryEventInfo->ref_schedule ], # Schedule
                            ];
                            $action      = strtolower($props->action) == "deleted" ? "Unmatched" : "Matched";
                            $options     = [
                                'type'        => "events",
                                'master_id'   => $props->attributes->master_event_id,
                                'raw_id'      => $props->attributes->event_id,
                                'sport_id'    => $getRaw->sport_id,
                                'provider_id' => $getRaw->provider_id,
                            ];
                        }
                    }
                } else {
                    $description  = [
                        [ 'primary_alias' => $getPrimaryEventInfo->primary_alias, 'secondary_alias' => $getSecondaryEventInfo->alias ], # Provider
                        [ 'primary_event_id' => $getPrimaryEventInfo->primary_event_id, 'secondary_event_id' => $getSecondaryEventInfo->event_id ], # Event ID
                        [ 'primary_league_name' => $getPrimaryEventInfo->primary_league_name, 'secondary_league_name' => $getSecondaryEventInfo->league_name ], # League Name
                        [ 'primary_team_home_name' => $getPrimaryEventInfo->primary_team_home_name, 'secondary_team_home_name' => $getSecondaryEventInfo->team_home_name ], # Home
                        [ 'primary_team_away_name' => $getPrimaryEventInfo->primary_team_away_name, 'secondary_team_away_name' => $getSecondaryEventInfo->team_away_name ], # Away
                        [ 'primary_ref_schedule' => $getPrimaryEventInfo->primary_ref_schedule, 'secondary_ref_schedule' => $getSecondaryEventInfo->ref_schedule ], # Schedule
                    ];
                    $action      = strtolower($props->action) == "deleted" ? "Unmatched" : "Matched";
                    $options     = [
                        'type'        => "events",
                        'master_id'   => $props->attributes->master_event_id,
                        'raw_id'      => $props->attributes->event_id,
                        'sport_id'    => $getRaw->sport_id,
                        'provider_id' => $getRaw->provider_id,
                    ];
                }
            }

            $data[] = [
                'log_name'    => $row->log_name,
                'description' => $description,
                'options'     => $options,
                'ip_address'  => $props->ip_address,
                'created_at'  => Carbon::createFromFormat("Y-m-d H:i:s", $row->created_at)->format("Y-m-d H:i:s"),
                'action'      => $action,
            ];
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $count,
            'pageNum'     => $page,
            'pageData'    => $data
        ]);
    }

    public function reprocess()
    {
        return MatchingFacade::setAllFailedMatchingToFalse();
    }

    public function clearDuplicates()
    {
        return MatchingFacade::clearDuplicates();
    }
}
