@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Reject contribution')
@section('body')
  <h1>Reject contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.confirm-reject', $review->id) !!}

  <p>
    Are you sure you want to reject <strong>{{ $review->word }}</strong> ({{ $review->sense }}) which 
    was submitted for review <span class="date">{{ $review->created_at }}</span> by 
    <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>?
  </p>

  <form method="post" action="{{ route('contribution.reject', ['id' => $review->id]) }}">
    {{ csrf_field() }}
    {{ method_field('PUT') }}

    <div class="form-group">
      <label for="ed-rejection-justification" class="control-label">Justification</label>
      <input type="text" class="form-control" id="ed-rejection-justification" name="justification" placeholder="Optional explanation why the contribution was rejected.">
    </div>

    <div class="text-end">
      <div class="btn-group" role="group">
        <a href="{{ route('contribution.show', ['contribution' => $review->id]) }}" class="btn btn-default">Cancel rejection</a>
        <button type="submit" class="btn btn-warning"><span class="glyphicon glyphicon-minus-sign"></span> Reject</button>
      </div>
    </div>
  </form>

@endsection
