@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Words in '.$language->name)
@section('body')
  <h1>Words in {{ $language->name }}</h1>
  
  {!! Breadcrumbs::render('translation.list', $language) !!}

  <ul class="list-group">
    @foreach ($translations as $t)
    <li class="list-group-item">
      <a href="{{ route('translation.edit', [ 'id' => $t->id ]) }}"{!! $t->is_rejected ? 'style="text-decoration:line-through"' : '' !!}>
        <strong>{{ $t->word }}</strong>
      </a>
      @if (! empty($t->speech))
      <em>{{ $t->speech }}</em>
      @endif
      {{ $t->translation }}
      @if (! empty($t->source))
      [<span class="text-info">{{ $t->source }}</span>]
      @else
      [<em class="text-danger">Source missing</em>]
      @endif
      by 
      <a href="{{ $link->author($t->account_id, $t->account_name) }}">{{ $t->account_name }}</a>
      |
      #<a href="{{ $link->translation($t->id) }}">{{ $t->id }}</a>
    </li>
    @endforeach
  </ul>
  
@endsection