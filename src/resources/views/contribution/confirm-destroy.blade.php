@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Confirm deletion')
@section('body')
  <h1>Confirm deletion of #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.confirm-destroy', $review->id) !!}

  <p>
    Are you sure you want to delete <strong>{{ $review->word }}</strong> ({{ $review->sense }}) which 
    @if ($review->account_id === Auth::user()->id)
    you submitted for review <time datetime="{{ $review->created_at }}">{{ $review->created_at }}</time>?
    @else
    was submitted for review <time datetime="{{ $review->created_at }}">{{ $review->created_at }}</time> by 
    <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>?
    @endif
  </p>

  <form method="post" action="{{ route('contribution.destroy', ['contribution' => $review->id]) }}">
    {{ csrf_field() }}
    {{ method_field('DELETE') }}

    <div class="text-end">
      <div class="btn-group" role="group">
        <a href="{{ route('contribution.show', ['contribution' => $review->id]) }}" class="btn btn-secondary">Cancel deletion</a>
        <button type="submit" class="btn btn-danger"><span class="TextIcon TextIcon--trash"></span> Delete</button>
      </div>
    </div>
  </form>

@endsection
