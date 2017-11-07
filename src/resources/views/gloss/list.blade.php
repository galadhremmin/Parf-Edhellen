@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary for '.$language->name)
@section('body')
  <h1>Glossary for {{ $language->name }}</h1>
  
  {!! Breadcrumbs::render('gloss.list', $language) !!}

  <ul class="list-group">
    @foreach ($glosses as $t)
    <li class="list-group-item">
      <p>
        <a href="{{ route('gloss.edit', [ 'id' => $t->id ]) }}"{!! $t->is_rejected ? 'style="text-decoration:line-through"' : '' !!}>
          <strong>{{ $t->word->word }}</strong>
        </a>
        @if ($t->speech)
        <em>{{ $t->speech->name }}</em>
        @endif
        {{ $t->translations->implode('translation', ', ') }}
        @if (! empty($t->source))
        [<span class="text-info">{{ $t->source }}</span>]
        @else
        [<strong class="text-danger">SOURCE MISSING</strong>]
        @endif
        by 
        <a href="{{ $link->author($t->account_id, $t->account->nickname) }}">{{ $t->account->nickname }}</a>
        in 
        @if ($t->sense)
        <strong>{{ $t->sense->word->word }}</strong>
        @else
        <strong class="text-danger">SENSE MISSING</strong>
        @endif
        |
        #<a href="{{ $link->gloss($t->id) }}">{{ $t->id }}</a>
      </p>
      @foreach ($t->keywords as $k)
      <span class="label label-info">{{ $k->keyword }}</span>
      @endforeach
    </li>
    @endforeach
  </ul>

  {{ $glosses->links() }}
  
@endsection
