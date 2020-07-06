@extends('_layouts.default')

@section('title', 'Flashcards')
@section('body')
  <h1>Flashcards</h1>
  
  {!! Breadcrumbs::render('flashcard') !!}

  <p>@lang('flashcard.description')</p>
  <p>@lang('flashcard.instructions')</p>

  @if ($statistics && $statistics['_total'] > 0)
  <p>
    You have been here before! You have reviewed <strong>{{ $statistics['_total'] }}</strong> cards, 
    <strong>{{ $statistics['_total_correct'] }}</strong> of which you answered correctly 
    ({{ round($statistics['_total_correct'] / $statistics['_total'] * 100, 0) }} %).
  </p>
  @endif

  <div class="link-blocks">
    @foreach ($flashcards as $flashcard)
    <blockquote>
      <a class="block-link" href="{{ route('flashcard.cards', ['id' => $flashcard->id]) }}">
        <h3>
          {{ $flashcard->language->name }}
          @if (! empty($flashcard->language->tengwar))
          <span class="tengwar">{{ $flashcard->language->tengwar }}</span>
          @endif
        </h3>
        <p>
          {{ $flashcard->description }}
        </p>
      </a>
      
      @if ($statistics && isset($statistics[$flashcard->language->name]))
      <footer>
        @if ($statistics && isset($statistics[$flashcard->language->name]))
          You have reviewed <strong>{{ $statistics[$flashcard->language->name]['total'] }}</strong> cards,
          <strong>{{ $statistics[$flashcard->language->name]['correct'] }}</strong> of which you answered correctly 
          ({{ round($statistics[$flashcard->language->name]['correct'] / $statistics[$flashcard->language->name]['total'] * 100, 0) }} %).
          <a href="{{ route('flashcard.list', ['id' => $flashcard->id]) }}">Review performance</a>
        @endif
      </footer>
      @endif
    </blockquote>
    @endforeach
  </div>
@endsection
@section('styles')
  <link href="@assetpath(/css/app.flashcard.css)" rel="stylesheet">
@endsection
