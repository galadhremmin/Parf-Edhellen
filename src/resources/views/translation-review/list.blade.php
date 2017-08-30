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
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Date</th>
                <th>Word</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($pendingReviews as $review)
              <tr>
                <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  <a href="{{ route('translation-review.show', ['id' => $review->id]) }}">{{ $review->word }} ({{ $review->sense }})</a></td>
                <td>
                  <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
      </div>

    </div>
  </div>

@endsection
