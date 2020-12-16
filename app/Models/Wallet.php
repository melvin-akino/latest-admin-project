<?php

namespace App\Models;

use App\Models\{User, Currency, Source, WalletLedger};
use Illuminate\Database\Eloquent\Model;
use Exception;

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

    public static function getUserBalance($params)
    {
        return self::where('user_id', $params->user_id)
            ->where('currency_id', $params->currency_id)
            ->select(['balance', 'currency.code'])
            ->join('currency', 'currency.id', 'currency_id')
            ->get()
            ->toArray();
    }
}
