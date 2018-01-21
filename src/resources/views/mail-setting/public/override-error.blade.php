@extends('_layouts.default')

@section('title', 'Unsubscribe')
@section('body')

<h1>Erroneous token</h1>
<p>The token you have provided is erroneous. <em>{{ config('app.name') }}</em> cannot find an entity associated with it.</p>

@endsection
