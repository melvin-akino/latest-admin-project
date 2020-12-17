<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Wallet, Source};

class WalletLedger extends Model
{
    protected $table    = 'wallet_ledger';
    protected $fillable = [
        'wallet_id',
        'source_id',
        'debit',
        'credit',
        'balance',        
    ];


    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}