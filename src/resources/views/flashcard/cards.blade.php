@extends('_layouts.default')

@section('title', 'Flashcards for '.$flashcard->language->name)
@section('body')
  <h1>Flashcards for {{ $flashcard->language->name }}</h1>
  
  {!! Breadcrumbs::render('flashcard.cards', $flashcard) !!}

  <div class="flashcard-container">
    <aside>
      @if ($user) 
        Your answers are saved so you can <a href="{{ route('flashcard.list', ['id' => $flashcard->id]) }}">review your performance</a>.
        Good luck!
      @else
        <strong><span class="TextIcon TextIcon--info-sign"></span> Sign in to record your progress!</strong>
        We store your answer to every single flashcard when you are logged in. 
        You can use this information to review your past performance and to find gaps in your vocabulary.
        And only you can see your answers, of course. 
        <a href="{{ route('login', ['redirect' => route('flashcard')]) }}">Sign in and start over</a>.
      @endif
    </aside>
    <div id="ed-flashcard-component" 
      data-inject-module="flashcards"
      data-inject-prop-flashcard-id="{{ $flashcard->id }}"
      data-inject-prop-tengwar-mode="{{ $flashcard->language->tengwar_mode }}"></div>
  </div>
      
@endsection
@section('styles')
  <link href="@assetpath(/css/app.flashcard.css)" rel="stylesheet">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/flashcard.js)" async></script>
@endsection
