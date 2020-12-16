<?php

namespace App\Models;

use App\Models\{User, Currency, Source, WalletLedger};
use App\Models\CRM\WalletLedger;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table    = "wallet";
    protected $fillable = [
        'balance',
        'currency_id',
        'user_id'
    ];

    const TYPE_CHARGE           = 'Credit';
    const TYPE_DISCHARGE        = 'Debit';
    const ERR_WALLET_DEDUCT     = 'Wallet Deduction Exceeded';
    const ERR_NEW_WALLET_DEDUCT = 'Currency not Set';

    public function Order()
    {
        return $this->hasMany('App\Models\Order','user_id','user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select([
            "id",
            "firstname",
            "lastname",
            "email",
            "name",
        ]);
    }

    public function wallet_ledger()
    {
        return $this->hasMany(WalletLedger::class, 'wallet_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
