<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\PairBalance;
use App\Services\AlpacaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public $alpacaService;

    public function __construct(AlpacaService $alpacaService)
    {
        $this->alpacaService = $alpacaService;
    }

    public function data(Request $request): array
    {
        if ($request->t === 'alpaca') {
            return $this->alpaca($request);
        }
        return [];
    }

    public function alpaca(Request $request): array
    {
        $response1 = $this->alpacaService->getBars($request->s1);
        $response2 = $this->alpacaService->getBars($request->s2);

        $size_max = max(sizeof($response1), sizeof($response2));
        $size_min = min(sizeof($response1), sizeof($response2));

        $pair = [];
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min) {
                $pair[] = [
                    $response1[$i][0], //timestamp
                    $response1[$i][1] / $response2[$i][1],
                    $response1[$i][2] / $response2[$i][2],
                    $response1[$i][3] / $response2[$i][3],
                    $response1[$i][4] / $response2[$i][4],
//                $response1[$i][5], // volume
                ];
            }
        }

        $lines = PairBalance::where('s1', $request->s1)->orderBy('created_at', 'DESC')->limit(3)->get();

        $midPrice1 = sizeof($lines) > 0 ? $lines->toArray()[0]['price_at_trade_s1'] / $lines->toArray()[0]['price_at_trade_s2'] : null;
        $midPrice2 = sizeof($lines) > 1 ? $lines->toArray()[1]['price_at_trade_s1'] / $lines->toArray()[1]['price_at_trade_s2'] : null;
        $midPrice3 = sizeof($lines) > 2 ? $lines->toArray()[2]['price_at_trade_s1'] / $lines->toArray()[2]['price_at_trade_s2'] : null;

        return [
            'first' => $response1,
            'pair' => $pair,
            'second' => $response2,
            'events' => [
                'middlePrice1' => $midPrice1,
                'middlePrice2' => $midPrice2,
                'middlePrice3' => $midPrice3
            ]
        ];
    }

    public function pair(): View
    {
        return view('pair');
    }

    public function getLatestData(Request $request)
    {
        $pair_balance = PairBalance::where(
            function ($query) use ($request) {
                $query->where('s1', $request->s1)
                    ->where('s2', $request->s2);
            }
        )->orWhere(
            function ($query) use ($request) {
                $query->where('s1', $request->s2)
                    ->where('s2', $request->s1);
            }
        )->orderBy('created_at', 'desc')->first();

        $input = Input::where(
            function ($query) use ($request) {
                $query->where('symbol1', $request->s1)
                    ->where('symbol2', $request->s2);
            }
        )->orWhere(
            function ($query) use ($request) {
                $query->where('symbol1', $request->s2)
                    ->where('symbol2', $request->s1);
            }
        )->orderBy('created_at', 'desc')->first();

        return [
            's1' => [
                'qty' => floatval($this->alpacaService->position($request->s1)['qty']),
                'value' => floatval($this->alpacaService->position($request->s1)['market_value']),
                'price' => $this->alpacaService->price($request->s1),
                'latest_input' => [
                    'amount' => $input->created_at > $pair_balance->created_at ? $input['amount1'] : null,
                    'amount_usd' => $input->created_at > $pair_balance->created_at ? $input['amount1_usd'] : null,
                ]
            ],
            's2' => [
                'qty' => floatval($this->alpacaService->position($request->s2)['qty']),
                'value' => floatval($this->alpacaService->position($request->s2)['market_value']),
                'price' => $this->alpacaService->price($request->s2),
                'latest_input' => [
                    'amount' => $input->created_at > $pair_balance->created_at ? $input['amount1'] : null,
                    'amount_usd' => $input->created_at > $pair_balance->created_at ? $input['amount1_usd'] : null,
                ]
            ]
        ];
    }
}
