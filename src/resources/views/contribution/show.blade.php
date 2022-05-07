@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $contribution->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $contribution->id, $admin) !!}

  @include('contribution._status-alert', $model)
  @include($viewName, $model)
  @include('contribution._notes', $contribution)
  @include('contribution._pending-info', $contribution)

  <hr>  
  @include('discuss._standalone', [
    'entity_id'   => $contribution->id,
    'entity_type' => 'contribution'
  ])

@endsection
