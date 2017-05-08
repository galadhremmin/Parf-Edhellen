@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases in '.$language->name)
@section('body')

  {!! Breadcrumbs::render('sentence.public.language', $language->id, $language->name) !!}

  <header>
      @include('sentence.public._header')
      <h2>{{ $language->name }} <span class="tengwar" aria-hidden="true">{{ $language->tengwar }}</span></h2>
  </header>
  @foreach ($sentences as $sentence)
  <blockquote>
    <h3>{{ $sentence->name }}</h3>
    @if(!empty($sentence->description))
    <p>{{ $sentence->description }}</p>
    @endif
    <footer>
      {{ $sentence->source }}
      @if ($sentence->account_id)
      by <a href="{{ $link->author($sentence->account_id, $sentence->account_name) }}">{{ $sentence->account_name }}</a>.
      @endif
    </footer>
    
    @include('sentence.public._readmore', [ 
      'languageId'     => $language->id,
      'languageName'   => $language->name,
      'sentenceId'     => $sentence->id,
      'sentenceName'   => $sentence->name
    ])
  </blockquote>
  @endforeach
@endsection