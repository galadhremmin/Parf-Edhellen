@extends('_layouts.default')

@section('title', 'Flashcards for '.$flashcard->language->name)
@section('body')
  <h1>Flashcards for {{ $flashcard->language->name }}</h1>
  
  {!! Breadcrumbs::render('flashcard.cards', $flashcard) !!}

  <div id="ed-flashcard-component" 
       data-flashcard-id="{{ $flashcard->id }}" 
       data-language-tengwar-mode="{{ $flashcard->language->tengwar_mode }}"></div>

@endsection
@section('styles')
  <link href="/css/app.flashcard.css" rel="stylesheet">
@endsection
@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/flashcard.js" async></script>
@endsection

