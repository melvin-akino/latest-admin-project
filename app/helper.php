<?php

if (!function_exists('monitorLog')) {
  function monitorLog(string $channel, string $level, $data)
  {
    Log::channel($channel)->{$level}(json_encode($data));
  }
}