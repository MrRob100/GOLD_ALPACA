@extends('layouts.app')

@section('content')
    <pair-page
        balance-route="{{ route('balance') }}"
        cr="{{ route('chart.data') }}"
        position-route="{{ route('position-route') }}"
        record-route="{{ route('inputs.create') }}"
        pr="{{ route('price') }}"
        transfer-route="{{ route('transfer-route') }}"
        spr="{{ route('saved.pairs') }}"
        cpr="{{ route('create.pair') }}"
        dlr="{{ route('delete.pair') }}"
        bdr="{{ route('brecord') }}"
        rand="{{ route('randomize') }}"
        market-open-route="{{ route('open') }}"
    >
    </pair-page>
@endsection
