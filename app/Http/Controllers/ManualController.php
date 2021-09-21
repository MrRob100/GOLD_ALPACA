<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Services\AlpacaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ManualController extends Controller
{
    public AlpacaService $alpacaService;

    public function __construct(AlpacaService $alpacaService)
    {
        $this->alpacaService = $alpacaService;
    }

    public function transfer()
    {
        $transfer = $this->alpacaService->sideToSide($_GET['from'], $_GET['to'], $_GET['portion']);

        if ($transfer) {

            Log::info('transfer success data to log in db: '.json_encode($transfer));
            //transfer is the order array

            $balance = $this->alpacaService->balance();

            $price = $transfer['fills'][0]['price'];

            //to after
            $b_record = Balance::create([
                'symbol' => $_GET['to'],
                'balance' => $balance,
                'balance_usd' => $balance * $price,
                'price_at_trade' => $price,
                'note' => 'after trade',
            ]);

            return true;

        } else {
            Log::error("transfer from {$_GET['from']} to {$_GET['to']} failed");
            return false;
        }
    }

    public function balance()
    {
        return $this->alpacaService->balance()[$_GET['of']]['available'];
    }

    public function price()
    {
        return $this->alpacaService->price($_GET['symbol']);
    }

    public function brecord(Request $request)
    {
        return $request->user()->balances()
            ->where('symbol', $request->c)
            ->limit(20)
            ->orderBy('created_at', 'DESC')
            ->get()->toArray();
    }
}
