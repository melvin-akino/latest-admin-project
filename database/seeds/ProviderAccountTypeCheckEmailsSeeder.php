<?php

use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class ProviderAccountTypeCheckEmailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SystemConfiguration::updateOrCreate([
            'type' => 'PROVIDER_ACCOUNT_TYPE_CHECK_EMAIL_TO'
        ], [
            'value' => 'melvinaquino@ninepinetech.com,reynaldodorado@ninepinetech.com,johnvirtucio@ninepinetech.com,kevinuy@ninepinetech.com,giorodriguez@ninepinetech.com,mechelledon@ninepinetech.com'
        ]);
    }
}
