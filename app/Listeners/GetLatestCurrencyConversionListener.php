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
            echo "Running Currency Conversion...\n";

            if (!empty($event->currencies)) {
                $hasAPIError = 0;

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

                    if (empty($response) || (!empty($response) && !floatval($response))) {
                        echo "Error Converting " . trim($currency->from_code) . " to " . trim($currency->to_code) . ". Response return invalid amount.\n";
                        Log::channel($this->channel)->error("Error Converting " . trim($currency->from_code) . " to " . trim($currency->to_code) . ". Response return invalid amount.");
                        Log::channel($this->channel)->error("Response: " . json_encode($response));

                        continue;
                    }

                    if (!empty($err)) {
                        $hasAPIError += 1;

                        echo "Error Converting " . trim($currency->from_code) . " to " . trim($currency->to_code) . ". Please check the logs.\n";
                        Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : " . $err);

                        continue;
                    }

                    ExchangeRate::updateOrCreate([
                        'from_currency_id' => $currency->from_id,
                        'to_currency_id'   => $currency->to_id,
                    ], [
                        'default_amount' => 1,
                        'exchange_rate'  => $response
                    ]);

                    echo trim($currency->from_code) . " to " . trim($currency->to_code) . " equals " . $response . ".\n";
                    Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : " . trim($currency->from_code) . " to " . trim($currency->to_code) . " equals " . $response);
                }

                if ($hasAPIError) {
                    self::mailNotify();
                }

                echo "Currency Conversion DONE!\n";
                Log::channel($this->channel)->info("[CURRENCY_CONVERSION] : Done!");
            } else {
                echo "No Currencies found to convert.";
                Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : No Currencies found to convert.");
            }
        } catch (Exception $e) {
            echo "Something went wrong. Please check the logs.";
            Log::channel($this->channel)->error("[CURRENCY_CONVERSION_ERROR] : " . $e->getMessage());
        }
    }

    private static function mailNotify()
    {
        $to = SC::getSystemConfigurationValue('CSV_EMAIL_TO');
        $to = explode(',', $to->value);

        $cc = SC::getSystemConfigurationValue('CSV_EMAIL_CC');
        $cc = explode(',', $cc->value);

        $bcc = SC::getSystemConfigurationValue('CSV_EMAIL_BCC');
        $bcc = explode(',', $bcc->value);

        Mail::to($to)
          ->cc($cc)
          ->bcc($bcc)
          ->send(new NotifyCurrencyConvertError("Currency Conversion Error"));
    }
}
