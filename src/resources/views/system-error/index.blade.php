@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>Service errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

<div data-inject-module="system-log"
     data-inject-prop-errors-by-week="@json($errorsByWeek)"></div>

<hr>

<h2>Test connectivity</h2>

@foreach ([ 'IdentifiesPhrasesMonitor' ] as $component)
   <a class="btn btn-default" href="{{ route('system-error.connectivity', [ 'component' => $component ]) }}">{{ $component }}</a>
@endforeach

@endsection
