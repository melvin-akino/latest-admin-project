<?php

namespace App\Console\Commands;

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
        //@TODO call leagues, teams, events and market auto matching
    }
}
