@extends('layouts.app')

@section('content')
    <pair-page
        cr="{{ route('chart.data') }}"
        position-route="{{ route('position-route') }}"
        pr="{{ route('price') }}"
        tr="{{ route('transfer') }}"
        spr="{{ route('saved.pairs') }}"
        cpr="{{ route('create.pair') }}"
        dlr="{{ route('delete.pair') }}"
        bdr="{{ route('brecord') }}"
        rand="{{ route('randomize') }}"
        market-open-route="{{ route('open') }}"
    >
    </pair-page>
@endsection
