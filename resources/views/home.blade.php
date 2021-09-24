@extends('layouts.app')

@section('content')
    <pair-page
        cr="{{ route('chart.data') }}"
        position-route="{{ route('position-route') }}"
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
