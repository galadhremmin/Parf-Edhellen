@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases')
@section('body')

  {!! Breadcrumbs::render('sentences.sentence', $language->ID, $language->Name,
      $sentence->SentenceID, $sentence->Name) !!}

  <header>
    @include('sentences._header')
    <h2>{{ $sentence->Name }}</h2>
  </header>

  @if (!empty($sentence->Description))
  <p>
    {{ $sentence->Description }}
  </p>
  @endif

  <div id="ed-fragment-navigator"></div>
  <script type="application/json" id="ed-preload-fragments">{!! $fragments !!}</script>

  {{ $sentence->LongDescription }}

  <footer class="sentence-footer">
    Published {{ $sentence->DateCreated }}
    @if ($sentence->AuthorID)
    by 
    <a href="{{ $link->author($sentence->AuthorID, $sentence->author->Nickname) }}">
      {{ $sentence->author->Nickname }}
    </a>
    @endif
  </footer>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence.js" async></script>
@endsection
