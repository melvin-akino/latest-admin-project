<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CurrencyConvertEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversionApi;
    public $currencies;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($conversionApi, $currencies)
    {
        $this->conversionApi = $conversionApi;
        $this->currencies    = $currencies;
    }
}
