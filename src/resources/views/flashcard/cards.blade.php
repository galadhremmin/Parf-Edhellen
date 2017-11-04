@extends('_layouts.default')

@section('title', 'Flashcards for '.$flashcard->language->name)
@section('body')
  <h1>Flashcards for {{ $flashcard->language->name }}</h1>
  
  {!! Breadcrumbs::render('flashcard.cards', $flashcard) !!}

  <div id="ed-flashcard-component" 
       data-flashcard-id="{{ $flashcard->id }}" 
       data-language-tengwar-mode="{{ $flashcard->language->tengwar_mode }}"></div>

  <hr />
  <p>
    Your answers are saved automatically so you can <a href="{{ route('flashcard.list', ['id' => $flashcard->id]) }}">review your performance</a>.
    Good luck!
  </p>
      
@endsection
@section('styles')
  <link href="@assetpath(/css/app.flashcard.css)" rel="stylesheet">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/flashcard.js)" async></script>
@endsection
