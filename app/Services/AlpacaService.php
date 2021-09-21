<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AlpacaService {

    public function getBars($symbol) {
        if (Cache::has("{$symbol}_alpaca")) {
            $candles = Cache::get("{$symbol}_alpaca");
        } else {
            $start = Carbon::now()->subMonths(8)->format('Y-m-d') . 'T12:00:00.000000Z';

            //end: should do it while market is open, maybe that allows us to do carbon::now()
            //utilise /v2/clock
            $end = Carbon::now()->subDay()->format('Y-m-d') . 'T12:00:00.000000Z';

            $data = json_decode(file_get_contents(
                "https://data.alpaca.markets/v2/stocks/{$symbol}/bars?timeframe=1Day&start={$start}&end={$end}",
                false,
                $this->context(),
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

    public function createMarketOrder($side, $amount, $symbol) {

        $endpoint = env('ALPACA_ENDPOINT_PAPER');
        $key = env('ALPACA_KEY_PAPER');
        $secret = env('ALPACA_SECRET_PAPER');

//        $endpoint = env('ALPACA_ENDPOINT');
//        $key = env('ALPACA_KEY');
//        $secret = env('ALPACA_SECRET');

        $postRequest = array(
            'symbol' => $symbol,
            'qty' => $amount, //sort out
            'time_in_force' => 'gtc',
            'side' => $side,
            'type' => 'market',
        );

        $cURLConnection = curl_init($endpoint);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
            'APCA-API-KEY-ID: ' . $key,
            'APCA-API-SECRET-KEY: '. $secret,
        ));

        $apiResponse = curl_exec($cURLConnection);
        curl_close($cURLConnection);

        return json_decode($apiResponse);
    }

    public function sideToSide($from, $to, $portion)
    {

    }

    public function balance($of)
    {
//        /v2/positions/{symbol}
    }

    public function price($of)
    {
        $data = json_decode(file_get_contents(
            "https://data.alpaca.markets/v2/stocks/{symbol}/trades/latest",
            false,
            $this->context(),
        ), true);

    }

    public function marketOpen(): bool
    {
        $clock = json_decode(file_get_contents(
            "https://data.alpaca.markets/v2/clock",
            false,
            $this->context(),
        ), true);

        return $clock['is_open'];
    }

    public function context()
    {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept-language: en\r\n" .
                    "APCA-API-KEY-ID: " . env('ALPACA_KEY') . "\r\n" .
                    "APCA-API-SECRET-KEY: " . env('ALPACA_SECRET') . "\r\n"
            ]
        ];

        return stream_context_create($opts);
    }
}
