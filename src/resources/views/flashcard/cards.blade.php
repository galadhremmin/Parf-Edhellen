@extends('_layouts.default')

@section('title', 'Flashcards for '.$flashcard->language->name)
@section('body')
  <h1>Flashcards for {{ $flashcard->language->name }}</h1>
  
  {!! Breadcrumbs::render('flashcard.cards', $flashcard) !!}

  <div class="flashcard-container">
    <div id="ed-flashcard-component" 
      data-inject-module="flashcards"
      data-inject-prop-flashcard-id="{{ $flashcard->id }}"
      data-inject-prop-tengwar-mode="{{ $flashcard->language->tengwar_mode }}"></div>

    <aside>
      @if ($user) 
      <div class="alert alert-info">
        Your answers are saved so you can <a href="{{ route('flashcard.list', ['id' => $flashcard->id]) }}">review your performance</a>.
        Good luck!
      </div>
      @else
      @include('flashcard._login')
      @endif
    </aside>
  </div>
      
@endsection
@section('styles')
  <link href="@assetpath(/css/app.flashcard.css)" rel="stylesheet">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/flashcard.js)" async></script>
@endsection
