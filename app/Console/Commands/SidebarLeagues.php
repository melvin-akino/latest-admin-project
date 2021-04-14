<?php

namespace App\Console\Commands;

use App\Facades\SidebarFacade;
use Illuminate\Console\Command;

class SidebarLeagues extends Command
{
    protected $signature   = 'sidebar:leagues';
    protected $description = 'Sidebar Leagues';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        while (true) {
            SidebarFacade::generateSidebarLeagues();

            usleep(15000000);
        }
    }
}
