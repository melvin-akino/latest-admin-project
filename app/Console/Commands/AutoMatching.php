<?php

namespace App\Console\Commands;

use App\Facades\MatchingFacade;
use Illuminate\Console\Command;

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


            MatchingFacade::autoMatchPrimaryLeagues();
            MatchingFacade::autoMatchPrimaryTeams();
            MatchingFacade::autoMatchPrimaryEvents();
            MatchingFacade::autoMatchPrimaryEventMarkets();

            //usleep(1000000);

            //@todo Get all leagues, teams and events that are not from the primary providers
            //validate that these information does not have pivot table data and not exist in the unmatched_data table

            MatchingFacade::createUnmatchedLeagues(); 
            MatchingFacade::automatchIdenticalLeagues();
            usleep(1000000);
        }
    }
}
