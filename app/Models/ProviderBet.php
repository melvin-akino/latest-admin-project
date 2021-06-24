<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Support\Facades\DB;

class ProviderBet extends Model
{
    use LogsActivity;

    protected $table = 'provider_bets';

    protected $fillable = [
        'user_bet_id',
        'provider_id',
        'provider_account_id',
        'provider_error_message_id',
        'status',
        'bet_id',
        'odds',
        'stake',
        'to_win',
        'profit_loss',
        'reason',
        'settled_date',
        'game_schedule',
        'market_id',
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = [
        'user_bet_id',
        'provider_id',
        'provider_account_id',
        'provider_error_message_id',
        'market_id',
        'status',
        'bet_id',
        'odds',
        'stake',
        'to_win',
        'profit_loss',
        'reason',
        'settled_date',
        'game_schedule',
        'market_id',
    ];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Placed Provider Bet';

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('action', ucfirst($eventName));
        $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $provider = Provider::find($this->provider_id);
        $currency = trim(Currency::getCodeById($provider->currency_id));

        if ($this->settled_date) {
            return "Settled Provider Bet on " . $provider->alias . " with $currency " . $this->stake . " @ " . $this->odds . ". " . $this->status . " $currency " . $this->profit_loss;
        } else if ($this->provider_error_message_id) {
            return ucfirst($eventName) . " Provider Bet on " . $provider->alias . ": " . $this->reason;
        }

        return ucfirst($eventName) . " Provider Bet on " . $provider->alias . " with $currency " . $this->stake . " @ " . $this->odds;
    }

    public static function getReportData($from, $to)
    {
        return DB::table('provider_bets AS pb')
                ->join('user_bets AS ub', 'ub.id', 'pb.user_bet_id')
                ->join('provider_accounts AS pa', 'pa.id', '=', 'pb.provider_account_id')
                ->join('users AS u', 'u.id', '=', 'ub.user_id')
                ->join('provider_bet_transactions AS pbt', 'pbt.provider_bet_id', '=', 'pb.id')
                ->join('odd_types AS ot', 'ot.id', '=', 'ub.odd_type_id')
                ->where('pb.created_at', '>=', $from)
                ->where('pb.created_at', '<=', $to)
                ->where('pb.bet_id', '!=', "")
                ->orderBy('pb.id', 'ASC')
                ->orderBy('pbt.id', 'DESC')
                ->distinct()
                ->get([
                    'pb.id',
                    'pbt.id',
                    'u.email',
                    'ub.ml_bet_identifier',
                    'pb.bet_id',
                    'pa.username',
                    'pb.created_at',
                    'pb.status',
                    'pb.settled_date',
                    'pb.stake',
                    'pb.profit_loss',
                    'pbt.actual_stake',
                    'pbt.actual_profit_loss',
                    'pb.odds',
                    'ub.odds_label'
                ]);
    }
}
