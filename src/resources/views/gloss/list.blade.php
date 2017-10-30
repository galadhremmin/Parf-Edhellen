@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary for '.$language->name)
@section('body')
  <h1>Glossary for {{ $language->name }}</h1>
  
  {!! Breadcrumbs::render('gloss.list', $language) !!}

  <ul class="list-group">
    @foreach ($glossary as $t)
    <li class="list-group-item">
      <a href="{{ route('gloss.edit', [ 'id' => $t->id ]) }}"{!! $t->is_rejected ? 'style="text-decoration:line-through"' : '' !!}>
        <strong>{{ $t->word }}</strong>
      </a>
      @if (! empty($t->speech))
      <em>{{ $t->speech }}</em>
      @endif
      {{ $t->translations }}
      @if (! empty($t->source))
      [<span class="text-info">{{ $t->source }}</span>]
      @else
      [<strong class="text-danger">SOURCE MISSING</strong>]
      @endif
      by 
      <a href="{{ $link->author($t->account_id, $t->account_name) }}">{{ $t->account_name }}</a>
      in 
      @if (! empty($t->sense))
      <strong>{{ $t->sense }}</strong>
      @else
      <strong class="text-danger">SENSE MISSING</strong>
      @endif
      |
      #<a href="{{ $link->gloss($t->id) }}">{{ $t->id }}</a>
    </li>
    @endforeach
  </ul>
  
@endsection
