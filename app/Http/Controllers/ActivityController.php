<?php

namespace App\Http\Controllers;

use App\Models\PairBalance;
use Illuminate\Support\Carbon;

class ActivityController extends Controller
{
    public function getActivity(): array
    {
        return [
            'last_5_days' => PairBalance::where('created_at', '>', Carbon::now()->subDays(5))->pluck('note', 'created_at'),
            'today' => PairBalance::whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->pluck('note', 'created_at'),
        ];
    }
}
