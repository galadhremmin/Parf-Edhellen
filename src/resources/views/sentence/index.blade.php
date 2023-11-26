@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases')
@section('body')

  {!! Breadcrumbs::render('sentence.public') !!}

  @include('sentence._header')
  <p>
    Studying attested phrases is a great way of learn Tolkien's languages.
    We currently have {{ $numberOfSentences }} phrases in our database, and
    {{ $numberOfNeologisms }} of them are neologisms.
  </p>

  <div class="link-blocks">
    @foreach ($languages as $language)
    <blockquote>
      <a class="block-link" href="{{ $link->sentencesByLanguage($language->id, $language->name) }}">
        <h3>{{ $language->name }}</h3>
        <p>{{ $language->description }}</p>
      </a>
    </blockquote>
    @endforeach
  </div>
@endsection

@section('styles')
  <link href="@assetpath(/css/app.sentences.css)" rel="stylesheet">
@endsection
