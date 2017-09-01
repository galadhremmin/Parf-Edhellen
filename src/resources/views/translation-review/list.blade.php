@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Administration of contributions')
@section('body')
  <h1>Contributions</h1>
  
  {!! Breadcrumbs::render('translation-review.list') !!}
  

  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-hourglass"></span> Awaiting review</h2>
        </div>
        <div class="panel-body">
          @if (count($pendingReviews) < 1)
          <em>You have no contributions awaiting to be reviewed.</em>
          @else
            @include('translation-review._table', [
              'reviews' => $pendingReviews
            ])
          @endif
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-ok"></span> Approved contributions</h2>
        </div>
        <div class="panel-body">
          @if (count($approvedReviews) < 1)
          <em>There are presently no approved contributions.</em>
          @else
            @include('translation-review._table', [
              'reviews' => $approvedReviews
            ])
          @endif
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-remove"></span> Rejected contributions</h2>
        </div>
        <div class="panel-body">
          @if (count($rejectedReviews) < 1)
          <em>There are presently no rejected contributions.</em>
          @else
            @include('translation-review._table', [
              'reviews' => $rejectedReviews
            ])
          @endif
        </div>
      </div>
    </div>
  </div>

@endsection
