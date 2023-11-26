@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Administration of contributions')
@section('body')
  <h1>Contributions</h1>
  
  {!! Breadcrumbs::render('contribution.list') !!}
  

  <div class="card shadow-lg mb-3">
    <div class="card-body">
      <h2>Awaiting review</h2>
      @if ($pendingReviews->isEmpty())
      <em>You have no contributions awaiting to be reviewed.</em>
      @else
        @include('contribution._table', [
          'reviews' => $pendingReviews,
          'admin'   => true
        ])
        <div class="text-center">{{ $pendingReviews->links() }}</div>
      @endif
    </div>
  </div>

  <div class="card shadow-lg mb-3">
    <div class="card-body">
      <h2 class="panel-title">Approved contributions</h2>
      @if ($approvedReviews->isEmpty())
      <em>There are presently no approved contributions.</em>
      @else
        @include('contribution._table', [
          'reviews' => $approvedReviews,
          'admin'   => true
        ])
        <div class="text-center">{{ $approvedReviews->links() }}</div>
      @endif
    </div>
  </div>

  <div class="card shadow-lg">
    <div class="card-body">
      <h2 class="panel-title">Rejected contributions</h2>
      @if ($rejectedReviews->isEmpty())
      <em>There are presently no rejected contributions.</em>
      @else
        @include('contribution._table', [
          'reviews' => $rejectedReviews,
          'admin'   => true
        ])
        <div class="text-center">{{ $rejectedReviews->links() }}</div>
      @endif
    </div>
  </div>

@endsection
