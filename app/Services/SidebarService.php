<?php

namespace App\Services;

use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use App\Models\{Provider, SystemConfiguration, Sport, MasterLeague};
use Exception;

class SidebarService
{
    public static function generateSidebarLeagues()
    {
        $matchedProcess = SystemConfiguration::getValueByType('MATCHED_PROCESS');
        if (empty($matchedProcess)) {
            Log::info('[SIDEBAR-LEAGUES] No changes in the last 15 seconds');
            return;
        }

        $primaryProviderId         = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));
        $eventValidMaxMissingCount = SystemConfiguration::getValueByType('EVENT_VALID_MAX_MISSING_COUNT');
        $sports = Sport::getAllActive();
        $gameSchedule = [
            'early', 'today', 'inplay'
        ];

        foreach ($sports->toArray() as $sport) {
            foreach ($gameSchedule as $schedule) {
                $sidebarLeagues = MasterLeague::getSideBarLeaguesBySportAndGameSchedule(
                    $sport['id'],
                    $primaryProviderId,
                    $eventValidMaxMissingCount,
                    $schedule
                );
        
                $sidebarResult = array_map(function ($value) {
                    return (array) $value;
                }, $sidebarLeagues);
        
                self::sendToKafka($sidebarResult, $schedule, $sport['id']);
            }
        }

        SystemConfiguration::updateOrCreate([
            'type' => 'MATCHED_PROCESS'
        ], [
            'value' => '0'
        ]);
    }

    private static function sendToKafka($message, $gameSchedule, $sportId)
    {
        $data[$gameSchedule] = $message ? $message : [];
        $payload             = [
            'request_uid' => (string) Str::uuid(),
            'request_ts'  => getMilliseconds(),
            'command'     => 'sidebar',
            'sub_command' => 'transform',
            'data'        => [
                'sport_id' => $sportId,
                'sidebar'  => $data
            ]
        ];

        kafkaPush(env('KAFKA_SIDEBAR_LEAGUES'), $payload, $payload['request_uid']);

        Log::info('[SIDEBAR-LEAGUES] Payload sent: ' . $payload['request_uid']);
    }
}
