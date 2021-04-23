<?php

namespace App\Console\Commands;

use App\Facades\MatchingFacade;
use Illuminate\Console\Command;
use App\Models\{Provider, SystemConfiguration};

class AutoMatching extends Command
{
    protected $signature   = 'automatch';
    protected $description = 'Auto Matching';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $counter = 0;
        $automatchPrimaryLeaguesCount=$automatchPrimaryTeamsCount=$automatchPrimaryEventsCount=0;
        $unmatchedLeaguesCount=$unmatchedTeamsCount=$unmatchedEventsCount=0;
        $automatchSecondaryLeaguesCount=$automatchSecondaryTeamsCount=$automatchSecondaryEventsCount=0;
        while (true) {
            //@TODO call leagues, teams, events and market auto matching
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));

            if ($automatchPrimaryLeaguesCount <= 0) {
                $automatchPrimaryLeaguesCount = MatchingFacade::autoMatchPrimaryLeagues($primaryProviderId);
            }
            else {
                $automatchPrimaryLeaguesCount--;
            }
            usleep(500000);

            if ($automatchPrimaryLeaguesCount <= 0) {
                $automatchPrimaryTeamsCount = MatchingFacade::autoMatchPrimaryTeams($primaryProviderId);
            }
            else {
                $automatchPrimaryTeamsCount--;
            }
            usleep(500000);
            
            if ($automatchPrimaryEventsCount <= 0) {
                $automatchPrimaryEventsCount = MatchingFacade::autoMatchPrimaryEvents($primaryProviderId);
            }
            else {
                $automatchPrimaryEventsCount--;
            }
            usleep(500000);

            if ($unmatchedLeaguesCount <= 0) {
                $unmatchedLeaguesCount = MatchingFacade::createUnmatchedLeagues($primaryProviderId);
            }
            else {
                $unmatchedLeaguesCount--;
            }
            usleep(500000);
            
            if ($unmatchedTeamsCount <= 0) {
                $unmatchedTeamsCount = MatchingFacade::createUnmatchedTeams($primaryProviderId);
            }
            else {
                $unmatchedTeamsCount--;
            }
            usleep(500000);

            if ($unmatchedEventsCount <= 0) {
                $unmatchedEventsCount = MatchingFacade::createUnmatchedEvents($primaryProviderId);
            }
            else {
                $unmatchedEventsCount--;
            }
            usleep(500000);

            //put a waiting time
            if ($automatchSecondaryLeaguesCount <= 0) {
                $automatchSecondaryLeaguesCount = MatchingFacade::automatchIdenticalLeagues($primaryProviderId);
            }
            else {
                $automatchSecondaryLeaguesCount--;
            }
            usleep(500000);

            if ($automatchSecondaryTeamsCount <= 0) {
                $automatchSecondaryTeamsCount = MatchingFacade::automatchIdenticalTeams($primaryProviderId);
            }
            else {
                $automatchSecondaryTeamsCount--;
            }
            usleep(500000);

            if ($automatchSecondaryEventsCount <= 0) {
                $automatchSecondaryEventsCount = MatchingFacade::automatchIdenticalEvents($primaryProviderId);
            }
            else {
                $automatchSecondaryEventsCount--;
            }
            usleep(500000);

            $counter++;

            if ($counter % 100 == 0) {
                die("Auto matching service will terminate");
            }
        }
    }
}
