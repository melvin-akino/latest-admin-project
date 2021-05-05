<?php

use Illuminate\Database\Seeder;
use App\Models\{ User, SystemConfiguration, UserMaxBetLimit };

class UserMaxBetLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users  = User::all();
        $maxBet = SystemConfiguration::getValueByType('MAX_BET');
        foreach($users as $user) {
            UserMaxBetLimit::updateOrCreate(
                [ 'user_id' => $user->id ], 
                [ 'max_bet_limit' =>  $maxBet ]
            );
        }
    }
}
