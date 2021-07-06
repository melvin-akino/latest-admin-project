<?php
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

if (!function_exists('getMilliseconds')) {
    function getMilliseconds()
    {
        $mt = explode(' ', microtime());
        return bcadd($mt[1], $mt[0], 8);
    }
}

if (!function_exists('kafkaPush')) {
    function kafkaPush($kafkaTopic, $message, $key)
    {
        $kafkaProducer   = app('KafkaProducer');
        $producerHandler = app('ProducerHandler');

        try {
            Log::info('Sending to Kafka Topic: ' . $kafkaTopic);
            $producerHandler->setTopic($kafkaTopic)->send($message, $key);

            if (env('APP_ENV') != 'local') {
                for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
                    $result = $kafkaProducer->flush(10000);

                    if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                        break;
                    }
                }
            }

            Log::info('Sent to Kafka Topic: ' . $kafkaTopic);
        } catch (Exception $e) {
            Log::critical('Sending Kafka Message Failed', [
                'error' => $e->getMessage(),
                'code'  => $e->getCode()
            ]);
        } finally {
            if (env('CONSUMER_PRODUCER_LOG', false)) {
                Log::channel('kafkaproducelog')->info(json_encode($message));
            }
        }
    }
}

if (!function_exists('monitorLog')) {
    function monitorLog(string $channel, string $level, $data)
    {
      Log::channel($channel)->{$level}(json_encode($data));
    }
}

if (!function_exists('updateSettledDateTimezone')) {
    function updateSettledDateTimezone($date = null, $timezone = 'Etc/UTC')
    {
        return empty($date) ? $date : Carbon::createFromFormat("Y-m-d H:i:sO", $date, 'Etc/UTC')->setTimezone($timezone)->format("Y-m-d H:i:sO");
    }
}