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
        while (true) {
            //@TODO call leagues, teams, events and market auto matching
            $primaryProviderId    = Provider::getIdFromAlias(SystemConfiguration::getValueByType('PRIMARY_PROVIDER'));

            MatchingFacade::autoMatchPrimaryLeagues($primaryProviderId);
            usleep(500000);
            MatchingFacade::autoMatchPrimaryTeams($primaryProviderId);
            usleep(500000);
            MatchingFacade::autoMatchPrimaryEvents($primaryProviderId);
            usleep(500000);
            MatchingFacade::createUnmatchedLeagues($primaryProviderId);
            usleep(500000);
            MatchingFacade::createUnmatchedTeams($primaryProviderId);
            usleep(500000);
            MatchingFacade::createUnmatchedEvents($primaryProviderId);
            usleep(500000);
            MatchingFacade::automatchIdenticalLeagues($primaryProviderId);
            usleep(500000);
            MatchingFacade::automatchIdenticalTeams($primaryProviderId);
            usleep(500000);
            MatchingFacade::automatchIdenticalEvents($primaryProviderId);
            usleep(500000);
        }
    }
}
