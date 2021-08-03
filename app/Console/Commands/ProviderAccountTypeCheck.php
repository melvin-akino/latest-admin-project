<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{SystemConfiguration, ProviderAccount};
use Illuminate\Support\Facades\Mail;
use App\Mail\ProviderAccountTypeCheckMail;

class ProviderAccountTypeCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider-account:type-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking each provider account type if all accounts of that type are inactive.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $types = SystemConfiguration::where('module', 'ProviderAccount')->pluck('type')->toArray();
        $allInactiveTypes = [];
        $allInactiveAccounts = [];

        foreach($types as $type) {
            $accounts         = ProviderAccount::where('type', $type);
            $inactiveAccounts = ProviderAccount::where('type', $type)->where('is_enabled', false);
            $inactivePercentage = round(($inactiveAccounts->count() * 100) / $accounts->count(), 2);
            $invalidAccountThreshold = SystemConfiguration::getSystemConfigurationValue('PROVIDER_ACCOUNT_INACTIVE_THRESHOLD')->value;

            if($accounts->exists()) {
                if($inactivePercentage >= $invalidAccountThreshold) {
                    $this->error($type . ' type HAVE TOO MANY inactive accounts.');
                    $allInactiveTypes[] = $type; 
                    $allInactiveAccounts[$type] = $inactiveAccounts->with('provider')->get(); 
                } else {
                    $this->info($type . ' type HAVE enough active accounts.');
                }
            }
        }

        if(!empty($allInactiveTypes)) {
            $this->line(implode(', ', $allInactiveTypes) . ' type have too many inactive accounts.');

            $to = SystemConfiguration::getSystemConfigurationValue('PROVIDER_ACCOUNT_TYPE_CHECK_EMAIL_TO');
            $to = explode(',', $to->value);

            $subject = '[CRITICAL] Provider Account Warning';
            $env = env('APP_ENV', 'local');

            Mail::to($to)
              ->send(new ProviderAccountTypeCheckMail($env, $subject, $allInactiveAccounts));
        } else {
            $this->line('All types have enough active accounts.');
        }
    }
}
