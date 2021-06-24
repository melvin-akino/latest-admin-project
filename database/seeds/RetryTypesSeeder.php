<?php

use App\Models\RetryType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RetryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seedArray = [
            'auto-new-account'    => "auto retry using new account",
            'auto-new-line'       => "auto retry using the new account from the different line",
            'auto-same-account'   => "auto retry using same account",
            'manual-same-account' => "manual retry using same account",
        ];

        foreach ($seedArray AS $key => $desc) {
            RetryType::firstOrCreate([
                'type' => $key,
            ], [
                'description' => $desc,
                'created_at'  => Carbon::now(),
            ]);
        }
    }
}
