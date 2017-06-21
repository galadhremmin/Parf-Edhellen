@extends('_layouts.default')

@section('title', 'Flashcards')
@section('body')
  <h1>Flashcards</h1>
  
  {!! Breadcrumbs::render('flashcard') !!}

  @foreach ($flashcards as $flashcard)
  <blockquote>
    <h3>
      {{ $flashcard->language->name }}
      @if (! empty($flashcard->language->tengwar))
      <span class="tengwar">{{ $flashcard->language->tengwar }}</span>
      @endif
    </h3>
    <p>{{ $flashcard->description }}</p>
    <p class="text-right">
      <a href="{{ route('flashcard.cards', ['id' => $flashcard->id]) }}" class="btn btn-primary">
        <span class="glyphicon glyphicon-circle-arrow-right"></span>
        Compile flashcards
      </a>
    </p>
  </blockquote>
  <hr />
  @endforeach
@endsection