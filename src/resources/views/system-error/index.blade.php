@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>Service errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

<div data-inject-module="system-log"
     data-inject-prop-errors-by-week="@json($errorsByWeek)"></div>

@endsection
