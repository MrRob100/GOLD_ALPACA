<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Input;
use App\Models\PairBalance;
use App\Services\AlpacaService;
use Illuminate\Support\Carbon;
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

//        if ($transfer) {
//
//            Log::info('transfer success data to log in db: '.json_encode($transfer));
//            //transfer is the order array
//
//            $balance = $this->alpacaService->balance();
//
//            $price = $transfer['fills'][0]['price'];
//
//        $balance =
//
//            $b_record = Balance::create([
//                'symbol' => $_GET['to'],
//                'balance' => $transfer['balance_before'],
//                'balance_usd' => $balance * $price,
//                'price_at_trade' => $price,
//                'note' => 'after trade',
//            ]);
//
//            return true;
//
//        } else {
//            Log::error("transfer from {$_GET['from']} to {$_GET['to']} failed");
//            return false;
//        }



        return $transfer;
    }

    public function position()
    {
        return $this->alpacaService->position($_GET['of']);
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

    public function open(): bool
    {
        return $this->alpacaService->marketOpen();
    }

    public function getPairData(Request $request)
    {
        $month = $request->month;

        $year = $month > Carbon::now()->month ? Carbon::now()->subYear()->year : Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = Carbon::createFromDate($year, $month, 1)->addMonth();

        $query = PairBalance::where('s1', $request->s1)
            ->where('s2', $request->s2);

        if ($month) {
            $query = $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $pair_balances = $query->orderBy('created_at')->get();

        $inputsQuery = Input::where(
            function ($query) use ($request) {
                $query->where('symbol1', $request->s1)
                    ->where('symbol2', $request->s2);
            }
        )->orWhere(
            function ($query) use ($request) {
                $query->where('symbol1', $request->s2)
                    ->where('symbol2', $request->s1);
            }
        );

        if ($month) {
            $inputsQuery = $inputsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $inputs = $inputsQuery->orderBy('created_at')->get();

        $data = [];
        foreach($pair_balances as $pair_balance) {
            for($i = 0; $i < sizeof($inputs); $i++) {
                if ($inputs[$i]->created_at <= $pair_balance->created_at) {

                    $relInputs = $inputs->where('created_at', '<=', $pair_balance->created_at);

//                    $relInputs = $pair_balances[0]
                    //separate column for this months proffits

                    $merged = array_merge($pair_balance->toArray(), [
                        'input_symbol1' => $relInputs->sum('amount1'),
                        'input_symbol1_usd' => $relInputs->sum('amount1_usd'),
                        'input_symbol2' => $relInputs->sum('amount2'),
                        'input_symbol2_usd' => $relInputs->sum('amount2_usd'),
                        'wbw_usd_1' => $relInputs->sum('amount1') * $pair_balance->price_at_trade_s1,
                        'wbw_usd_2' => $relInputs->sum('amount2') * $pair_balance->price_at_trade_s2,
                    ]);

                    $data[] = $merged;
                }
            }
        }

        return [
            'records' => collect($data)->unique()->toArray(),
            'current_month' => $request->month ?: Carbon::now()->month,
            'months' => [
                0 => [
                    'value' => 1,
                    'name' => 'Jan',
                ],
                [
                    'value' => 2,
                    'name' => 'Feb',
                ],
                [
                    'value' => 3,
                    'name' => 'Mar',
                ],
                [
                    'value' => 4,
                    'name' => 'Apr',
                ],
                [
                    'value' => 5,
                    'name' => 'May',
                ],
                [
                    'value' => 6,
                    'name' => 'Jun',
                ],
                [
                    'value' => 7,
                    'name' => 'Jul',
                ],
                [
                    'value' => 8,
                    'name' => 'Aug',
                ],
                [
                    'value' => 9,
                    'name' => 'Sep',
                ],
                [
                    'value' => 10,
                    'name' => 'Oct',
                ],
                [
                    'value' => 11,
                    'name' => 'Nov',
                ],
                [
                    'value' => 12,
                    'name' => 'Dec',
                ],
            ]
        ];
    }
}
