<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, MasterLeague, League, MasterEvent, Event};
use Carbon\Carbon;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    public function getHistory(Request $request)
    {
        $searchKey = $request->has('searchKey') ? $request->searchKey : '';
        $page      = $request->has('page') ? $request->page : 1;
        $limit     = $request->has('limit') ? $request->limit : 10;
        $data      = [];
        $query     = ActivityLog::getMatchingLogs($searchKey, $page, $limit);

        foreach ($query->get() AS $row) {
            $desc    = "";
            $options = [];
            $props   = json_decode($row->properties);

            if (strpos($row->log_name, 'Leagues') !== false) {
                $getMaster             = MasterLeague::getLeagueBaseName($props->attributes->master_league_id);
                $getRaw                = League::withTrashed()->find($props->attributes->league_id);
                $checkRawEventProvider = League::checkRawLeagueProvider($props->attributes->league_id);

                if ($checkRawEventProvider) {
                    $description = "From Auto-Matching";
                    $options     = [
                        'type'        => 'league',
                        'master_id'   => $props->attributes->master_league_id,
                        'raw_id'      => $props->attributes->league_id,
                        'sport_id'    => $getRaw->sport_id,
                        'provider_id' => $getRaw->provider_id,
                    ];
                } else {
                    $description = [ 'master' => $getMaster->name, 'raw' => $getRaw->name ];
                    $options     = [
                        'type'        => 'leagues',
                        'master_id'   => $props->attributes->master_league_id,
                        'raw_id'      => $props->attributes->league_id,
                        'sport_id'    => $getRaw->sport_id,
                        'provider_id' => $getRaw->provider_id,
                    ];
                }
            } else if (strpos($row->log_name, 'Events') !== false) {
                $checkRawEventProvider = Event::checkRawEventProvider($props->attributes->event_id);
                $getRaw                = Event::withTrashed()->find($props->attributes->event_id);

                if ($checkRawEventProvider) {
                    $description = "From Auto-Matching";
                    $options     = [
                        'type'        => "event",
                        'master_id'   => $props->attributes->master_event_id,
                        'raw_id'      => $props->attributes->event_id,
                        'sport_id'    => $getRaw->sport_id,
                        'provider_id' => $getRaw->provider_id,
                    ];
                } else {
                    $getEventInfo = MasterEvent::getEventInfo($props->attributes->master_event_id, $props->attributes->event_id);
                    $description  = [
                        [ 'primary_alias' => $getEventInfo->primary_alias, 'secondary_alias' => $getEventInfo->secondary_alias ], # Provider
                        [ 'primary_event_id' => $getEventInfo->primary_event_id, 'secondary_event_id' => $getEventInfo->secondary_event_id ], # Event ID
                        [ 'primary_league_name' => $getEventInfo->primary_league_name, 'secondary_league_name' => $getEventInfo->secondary_league_name ], # League Name
                        [ 'primary_team_home_name' => $getEventInfo->primary_team_home_name, 'secondary_team_home_name' => $getEventInfo->secondary_team_home_name ], # Home
                        [ 'primary_team_away_name' => $getEventInfo->primary_team_away_name, 'secondary_team_away_name' => $getEventInfo->secondary_team_away_name ], # Away
                        [ 'primary_ref_schedule' => $getEventInfo->primary_ref_schedule, 'secondary_ref_schedule' => $getEventInfo->secondary_ref_schedule ], # Schedule
                    ];

                    $options   = [
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
                'action'      => strtolower($props->action) == "deleted" ? "Unmatched" : "Matched",
            ];
        }

        return response()->json([
            'status'      => true,
            'status_code' => 200,
            'total'       => $query->count(),
            'pageNum'     => $page,
            'pageData'    => $data
        ]);
    }
}
