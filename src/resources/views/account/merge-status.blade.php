@extends('_layouts.default')

@section('title', 'Security')
@section('body')

<h1>Account linking status</h1>
{!! Breadcrumbs::render('account.merge-status', $mergeRequest) !!}

@endsection
