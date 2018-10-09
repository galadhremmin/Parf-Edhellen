@extends('_layouts.default')

@section('title', 'Flashcards')
@section('body')
  <h1>Flashcards</h1>
  
  {!! Breadcrumbs::render('flashcard') !!}

  <p>
    Welcome to <em>Flashcards</em>, where you can challenge your Elvish vocabulary.
    Choose a language beneath to get started. 
  </p>
  @if ($statistics && $statistics['_total'] > 0)
  <p>
    You have been here before! You have reviewed <strong>{{ $statistics['_total'] }}</strong> cards, 
    <strong>{{ $statistics['_total_correct'] }}</strong> of which you answered correctly 
    ({{ round($statistics['_total_correct'] / $statistics['_total'] * 100, 0) }} %).
  </p>
  @endif

  @foreach ($flashcards as $flashcard)
  <hr />
  <blockquote>
    <h3>
      {{ $flashcard->language->name }}
      @if (! empty($flashcard->language->tengwar))
      <span class="tengwar">{{ $flashcard->language->tengwar }}</span>
      @endif
      @if ($statistics && isset($statistics[$flashcard->language->name]))
      <span class="pull-right label label-info">
        {{ round($statistics[$flashcard->language->name]['correct'] / $statistics[$flashcard->language->name]['total'] * 100, 0) }} %
      </span>
      @endif
    </h3>
    <p>
      {{ $flashcard->description }}
      @if ($statistics && isset($statistics[$flashcard->language->name]))
        <span>You have reviewed <strong>{{ $statistics[$flashcard->language->name]['total'] }}</strong> cards,
        <strong>{{ $statistics[$flashcard->language->name]['correct'] }}</strong> of which you answered correctly 
        ({{ round($statistics[$flashcard->language->name]['correct'] / $statistics[$flashcard->language->name]['total'] * 100, 0) }} %).</span>
      @endif
    </p>
    <p class="text-right">
      @if ($statistics)
      <a href="{{ route('flashcard.list', ['id' => $flashcard->id]) }}" class="btn btn-default">
        <span class="glyphicon glyphicon-th-list"></span>
        Review performance
      </a>
      @endif
      <a href="{{ route('flashcard.cards', ['id' => $flashcard->id]) }}" class="btn btn-primary">
        <span class="glyphicon glyphicon-circle-arrow-right"></span>
        Start with {{ $flashcard->language->name }}
      </a>
    </p>
  </blockquote>
  @endforeach
@endsection
