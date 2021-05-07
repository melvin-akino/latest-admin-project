<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;

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
        'min',
        'max',
        'created_at',
        'updated_at',
    ];

    protected static $logAttributes = [
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
        'min',
        'max',
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
        $provider = Provider::find(request()->provider_id);
        $currency = trim(Currency::getCodeById($provider->currency_id));

        if (request()->settled_date) {
            return "Settled Provider Bet on " . $provider->alias . " with $currency " . request()->stake . " @ " . request()->odds . ". " . request()->status . " $currency " . request()->profit_loss;
        } else if (request()->provider_error_message_id) {
            return ucfirst($eventName) . " Provider Bet on " . $provider->alias . ": " . request()->reason;
        }

        return ucfirst($eventName) . " Provider Bet on " . $provider->alias . " with $currency " . request()->stake . " @ " . request()->odds;
    }
}
