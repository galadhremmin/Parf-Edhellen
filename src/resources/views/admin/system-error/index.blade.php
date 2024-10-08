@extends('_layouts.default', ['containerClass' => 'container-fluid'])

@section('title', 'System errors - Administration')
@section('body')

<h1>Service errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

<div data-inject-module="system-log"
     data-inject-prop-errors-by-week="@json($errorsByWeek)"
     data-inject-prop-error-categories="@json($errorCategories)"
     data-inject-prop-failed-jobs-by-week="@json($failedJobsByWeek)"
     data-inject-prop-failed-jobs-categories="@json($failedJobsCategories)"></div>

<hr>

<h2>Test connectivity</h2>

@foreach ([ 'IdentifiesPhrasesMonitor' ] as $component)
   <a class="btn btn-secondary" href="{{ route('system-error.connectivity', [ 'component' => $component ]) }}">{{ $component }}</a>
@endforeach

@endsection
