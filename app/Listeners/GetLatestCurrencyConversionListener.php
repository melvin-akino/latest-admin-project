<?php

namespace App\Listeners;

use App\Mail\NotifyCurrencyConvertError;
use App\Models\{ExchangeRate, SystemConfiguration AS SC};
use Illuminate\Support\Facades\{Log, Mail};

class GetLatestCurrencyConversionListener
{
    public  $channel = "currency-conversion";

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : Running Command...");

        try {
            if (!empty($event->currencies)) {
                foreach ($event->currencies as $currency) {
                    $api  = sprintf($event->conversionApi, trim($currency->from_code), trim($currency->to_code));
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL            => $api,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_ENCODING       => "",
                        CURLOPT_MAXREDIRS      => 10,
                        CURLOPT_TIMEOUT        => 30,
                        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST  => "GET",
                        CURLOPT_HTTPHEADER     => [
                            "x-rapidapi-host: currency-exchange.p.rapidapi.com",
                            "x-rapidapi-key: " . env('CURRENCY_CONVERT_ACCESS_KEY', '9278a72838mshba28daea8b296c3p10eaf0jsn33ee42d5eb56')
                        ],
                    ]);

                    $response = curl_exec($curl);
                    $err      = curl_error($curl);

                    curl_close($curl);

                    if (empty($err) && !empty($response) && floatval($response) && ($currency->from_id != $currency->to_id)) {
                        ExchangeRate::updateOrCreate([
                            'from_currency_id' => $currency->from_id,
                            'to_currency_id'   => $currency->to_id,
                        ], [
                            'default_amount' => 1,
                            'exchange_rate'  => $response
                        ]);

                        echo trim($currency->from_code) . " to " . trim($currency->to_code) . " equals " . $response . "\n";

                        Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : " . trim($currency->from_code) . " to " . trim($currency->to_code) . " equals " . $response);
                    } else if ($currency->from_id == $currency->to_id) {
                        echo "Skipped converting from " . trim($currency->from_code) . " to " . trim($currency->to_code) . ".\n";

                        Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : Skipped converting from " . trim($currency->from_code) . " to " . trim($currency->to_code) . ".\n");
                    } else {
                        // If $err is empty but $response is not a valid converted amount.
                        if (!empty($response) || !floatval($response)) {
                            Log::channel($this->channel)->error("Error Converting " . trim($currency->from_code) . " to " . trim($currency->to_code) . ". Please check the logs.");
                            Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : Response return invalid amount.");
                        }

                        if (!empty($err)) {
                            Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : " . $err);
                        }

                        echo "Error Converting " . trim($currency->from_code) . " to " . trim($currency->to_code) . ". Please check the logs.\n";
                    }
                }

                Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : Done!");
            } else {
                Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : No Currencies found to convert.");
            }
        } catch (Exception $e) {
            Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : " . $e->getMessage());
        }
    }
}
