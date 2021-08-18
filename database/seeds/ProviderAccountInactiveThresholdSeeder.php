<?php

use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class ProviderAccountInactiveThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SystemConfiguration::updateOrCreate([
            'type' => 'PROVIDER_ACCOUNT_INACTIVE_THRESHOLD'
        ], [
            'value' => 80
        ]);
    }
}
