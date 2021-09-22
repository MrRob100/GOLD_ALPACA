<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AlpacaService {

    protected string $endpoint;
    protected string $key;
    protected string $secret;

    public function __construct() {
        $this->endpoint = env('ALPACA_ENDPOINT_PAPER');
        $this->key = env('ALPACA_KEY_PAPER');
        $this->secret = env('ALPACA_SECRET_PAPER');

//        $this->endpoint = env('ALPACA_ENDPOINT');
//        $this->key = env('ALPACA_KEY');
//        $this->secret = env('ALPACA_SECRET');
    }

    public function getBars($symbol) {
        if (Cache::has("{$symbol}_alpaca")) {
            $candles = Cache::get("{$symbol}_alpaca");
        } else {
            $start = Carbon::now()->subMonths(8)->format('Y-m-d') . 'T12:00:00.000000Z';

            if ($this->marketOpen()) {
                $end = Carbon::now()->format('Y-m-d') . 'T12:00:00.000000Z';
            } else {
                $end = Carbon::now()->subDay()->format('Y-m-d') . 'T12:00:00.000000Z';
            }

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
        $postRequest = array(
            'symbol' => $symbol,
            'qty' => $amount, //sort out
            'time_in_force' => 'gtc',
            'side' => $side,
            'type' => 'market',
        );

        $cURLConnection = curl_init($this->endpoint);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
            'APCA-API-KEY-ID: ' . $this->key,
            'APCA-API-SECRET-KEY: '. $this->secret,
        ));

        $apiResponse = curl_exec($cURLConnection);
        curl_close($cURLConnection);

        return json_decode($apiResponse);
    }

    public function sideToSide($from, $to, $portion)
    {

    }

    public function position($of)
    {
        $positions = collect($this->positions());

        $position_of = $positions->where('symbol', '=', $of);

        if ($position_of->isNotEmpty()) {
            return $position_of->first();
        } else {
            return false;
        }
    }

    public function positions()
    {
        return json_decode(file_get_contents(
            "{$this->endpoint}/v2/positions",
            false,
            $this->context(),
        ), true);
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
            "https://api.alpaca.markets/v2/clock",
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
                    "APCA-API-KEY-ID: " . $this->key . "\r\n" .
                    "APCA-API-SECRET-KEY: " . $this->secret . "\r\n"
            ]
        ];

        return stream_context_create($opts);
    }
}
