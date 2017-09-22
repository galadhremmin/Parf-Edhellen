@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Confirm deletion')
@section('body')
  <h1>Confirm deletion of #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.confirm-destroy', $review->id) !!}

  <p>
    Are you sure you want to delete <strong>{{ $review->word }}</strong> ({{ $review->sense }}) which 
    @if ($review->account_id === Auth::user()->id)
    you submitted for review {{ $review->created_at->format('Y-m-d H:i') }}?
    @else
    was submitted for review {{ $review->created_at->format('Y-m-d H:i') }} by 
    <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>?
    @endif
  </p>

  <form method="post" action="{{ route('contribution.destroy', ['id' => $review->id]) }}">
    {{ csrf_field() }}
    {{ method_field('DELETE') }}

    <div class="text-right">
      <div class="btn-group" role="group">
        <a href="{{ route('contribution.show', ['id' => $review->id]) }}" class="btn btn-default">Cancel deletion</a>
        <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-remove-sign"></span> Delete</button>
      </div>
    </div>
  </form>

@endsection
