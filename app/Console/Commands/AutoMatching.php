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

            usleep(1000000);
        }
    }
}
