<?php

namespace App\Console\Commands;

use App\Events\CurrencyConvertEvent;
use App\Models\Currency;
use Illuminate\Console\Command;

class CurrencyConversionCommand extends Command
{
    protected $signature   = 'currency:convert';
    protected $description = 'Get Conversion';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $conversionApi = env('CURRENCY_CONVERT_URL', "https://currency-exchange.p.rapidapi.com/exchange?from=%s&to=%s&q=1.0");
        $currencies    = Currency::getCurrenciesForConversion();

        event(new CurrencyConvertEvent($conversionApi, $currencies));
    }
}
