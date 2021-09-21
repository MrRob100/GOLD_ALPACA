<?php

namespace App\Http\Controllers;

use App\Models\DudPair;
use Illuminate\Http\Request;

class RandomizeController extends Controller
{
    public function trash(Request $request): void
    {
        if (!is_null($request->params['s1'] && !is_null($request->params['s2']))) {
            DudPair::create(['symbol' => $request->params['s1'].''.$request->params['s2']]);
        }
    }

    public function randomPair(): array
    {
        $blueChips = [
            1 => 'ORCL',
            2 => 'OXY',
            3 => 'CSCO',
            4 => 'VZ',
            5 => 'UNH',
            6 => 'PEP',
            7 => 'T',
            8 => 'XOM',
            9 => 'JNJ',
            10 => 'IBM',
            11 => 'MO',
            12 => 'INTC',
            13 => 'KO',
            14 => 'GOOG',
            15 => 'MRK',
        ];

        $s1 = $blueChips[rand(1, sizeof($blueChips))];
        $s2 = $blueChips[rand(1, sizeof($blueChips))];

        $trash = DudPair::pluck('symbol')->all();

        if(in_array($s1.$s2, $trash)
            || in_array($s2.$s1, $trash))
        {
            DudPair::create(['symbol' => $s1.$s2]);

            return($this->randomPair());
        }

        return [
            'v1' => $s2,
            'v2' => $s1,
        ];
    }
}
