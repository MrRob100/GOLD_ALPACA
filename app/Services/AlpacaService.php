<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AlpacaService {

    public function getBars($symbol) {
        if (Cache::has("{$symbol}_alpaca")) {
            $candles = Cache::get("{$symbol}_alpaca");
        } else {
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Accept-language: en\r\n" .
                        "APCA-API-KEY-ID: " . env('ALPACA_KEY') . "\r\n" .
                        "APCA-API-SECRET-KEY: " . env('ALPACA_SECRET') . "\r\n"
                ]
            ];

            $context = stream_context_create($opts);

            $start = Carbon::now()->subMonths(6)->format('Y-m-d') . 'T12:00:00.000000Z';

            //end: should do it while market is open, maybe that allows us to do carbon::now()
            $end = Carbon::now()->subDay()->format('Y-m-d') . 'T12:00:00.000000Z';

            $data = json_decode(file_get_contents(
                "https://data.alpaca.markets/v2/stocks/{$symbol}/bars?timeframe=1Day&start={$start}&end={$end}",
                false,
                $context,
            ), true);

            $candles = [];

            foreach ($data['bars'] as $item) {
                $candles[] = [
                    Carbon::parse($item['t'])->unix() * 1000,
                    $item['o'],
                    $item['h'],
                    $item['l'],
                    $item['c'],
                ];
            }

            Cache::put("{$symbol}_alpaca", $candles, 600);

        }
        return $candles;
    }
}
