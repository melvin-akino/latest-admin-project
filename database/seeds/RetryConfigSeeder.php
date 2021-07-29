<?php

use App\Models\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RetryConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seedArray = [
            'RETRY_COUNT'  => "5",
            'RETRY_EXPIRY' => "15"
        ];

        foreach ($seedArray as $key => $value) {
            SystemConfiguration::firstOrCreate(
                [
                    'type' => $key,
                ],
                [
                    'module'     => 'Bet Queue',
                    'value'      => $value,
                    'created_at' => Carbon::now(),
                ]
            );
        }
    }
}
