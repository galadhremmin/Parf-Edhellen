@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Administration of contributions')
@section('body')
  <h1>Contributions</h1>
  
  {!! Breadcrumbs::render('contribution.list') !!}
  

  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Awaiting review</h2>
        </div>
        <div class="panel-body">
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
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Approved contributions</h2>
        </div>
        <div class="panel-body">
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
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Rejected contributions</h2>
        </div>
        <div class="panel-body">
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
    </div>
  </div>

@endsection
