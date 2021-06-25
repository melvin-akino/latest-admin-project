<?php

use App\Models\SystemConfiguration AS SC;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProviderAccountUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SC::firstOrCreate([
            'type'  => "PROVIDER_ACCOUNT_USAGE",
            'value'  => "OPEN,CLOSE",
            'module' => "ProviderAccountUsage",
        ], [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
