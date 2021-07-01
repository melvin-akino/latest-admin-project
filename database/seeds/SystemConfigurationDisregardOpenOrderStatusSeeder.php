<?php

use App\Models\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SystemConfigurationDisregardOpenOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SystemConfiguration::firstOrCreate([
            'type' => "DISREGARDED_OPEN_ORDERS_STATUS",
        ], [
            'value' => "PENDING",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
