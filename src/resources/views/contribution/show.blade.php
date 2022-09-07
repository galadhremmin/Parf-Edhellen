@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $contribution->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $contribution->id, $returnToAdminView) !!}

  @include('contribution._status-alert', $model)
  @include('contribution._dependencies', $model)
  @include($viewName, $model)
  @include('contribution._notes', $contribution)
  @include('contribution._pending-info', $contribution)

  @if ($returnToAdminView && $isAdmin)
  <div class="text-center">
    <button class="btn btn-sm btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#contribution-debug-info" aria-expanded="false" aria-controls="contribution-debug-info">
      Admin: show debug info
    </button>
  </div>
  <div class="collapse" id="contribution-debug-info">
  <h2>Contribution payload</h2>
    <textarea class="form-control font-monospace" rows="10">@json($model, true)</textarea>
  </div>
  @endif

  <hr>  
  @include('discuss._standalone', [
    'entity_id'   => $contribution->id,
    'entity_type' => 'contribution'
  ])

@endsection
