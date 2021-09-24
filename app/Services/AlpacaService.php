<?php

namespace App\Services;

use App\Models\PairBalance;
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

    public function barsCall(string $symbol, string $end)
    {
        $start = Carbon::now()->subMonths(8)->format('Y-m-d') . 'T12:00:00.000000Z';

        try {
            $data = json_decode(file_get_contents(
                "https://data.alpaca.markets/v2/stocks/{$symbol}/bars?timeframe=1Day&start={$start}&end={$end}",
                false,
                $this->context(),
            ), true);
        } catch(\Exception $e) {
            return false;
        }

        return $data;
    }

    public function getBars($symbol) {
        if (Cache::has("{$symbol}_alpaca")) {
            $candles = Cache::get("{$symbol}_alpaca");
        } else {
            $endOfDay = Carbon::now()->format('Y-m-d') . 'T12:00:00.000000Z';
            $endOfYesterday = Carbon::now()->subDay()->format('Y-m-d') . 'T12:00:00.000000Z';

            $data = $this->barsCall($symbol, $endOfDay) ?: $this->barsCall($symbol, $endOfYesterday);

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
        $postRequest = [
            'symbol' => $symbol,
            'qty' => $amount,
            'time_in_force' => 'day',
            'side' => $side,
            'type' => 'market',
        ];

        $cURLConnection = curl_init("{$this->endpoint}/v2/orders");
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($postRequest));
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
            'APCA-API-KEY-ID: ' . $this->key,
            'APCA-API-SECRET-KEY: '. $this->secret,
        ));

        $apiResponse = curl_exec($cURLConnection);
        curl_close($cURLConnection);

        return json_decode($apiResponse);
    }

    public function orderFilled(string $id): bool
    {
        $n = 0;
        $order = json_decode(file_get_contents(
            "{$this->endpoint}/v2/orders/{$id}",
            false,
            $this->context(),
        ), true);

        if ($order['status'] === 'filled') {
            return true;
        } else {
            sleep(1);
            $n ++;

            if ($n >= 5) {
                return false;
            }

            return $this->orderFilled($id);
        }
    }

    public function sideToSide($from, $to, $portion)
    {
        $position_from = $this->position($from);

        if (!$position_from) {
            return [
                'success' => false,
                'message' => "no $from position",
            ];
        }

        $position_to = $this->position($from);

        $price_from = $this->price($to);
        $price_to = $this->price($to);

        $asset_value_from = $position_from['qty'] * $position_from['current_price'];
        $position_to_qty = $position_to ? $position_to['qty'] : 0;

        PairBalance::create([
            's1' => $from,
            'balance_s1' => $position_from['qty'],
            'balance_s1_usd' => $asset_value_from,
            'price_at_trade_s1' => $price_from,
            's2' => $to,
            'balance_s2' => $position_to_qty,
            'balance_s2_usd' => $position_to_qty * $price_to,
            'price_at_trade_s2' => $price_to,
        ]);

        $sell = $this->createMarketOrder('sell', $position_from['qty'] / $portion, $from);


        sleep(1);

        //delay logic?
        $buy = $this->createMarketOrder('buy', $sale_value_from / $price_to, $to);

        return [
            'success' => true,
            'message' => "sold $from and bought $to",
        ];

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

    public function price($of): float
    {
        return json_decode(file_get_contents(
            "https://data.alpaca.markets/v2/stocks/{$of}/trades/latest",
            false,
            $this->context(),
        ), true)['trade']['p'];
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
