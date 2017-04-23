@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases')
@section('body')

  {!! Breadcrumbs::render('sentence.public.sentence', $language->id, $language->name,
      $sentence->id, $sentence->name) !!}

  <header>
    @include('sentence.public._header')
    <h2>{{ $sentence->name }}</h2>
  </header>

  @if (!empty($sentence->description))
  <p>
    {{ $sentence->description }}
  </p>
  @endif

  <div id="ed-fragment-navigator"></div>
  <script type="application/json" id="ed-preload-fragments">{!! $fragments !!}</script>

  {{ $sentence->long_description }}

  <footer class="sentence-footer">
    Published {{ $sentence->created_at }}
    @if ($sentence->author_id)
    by 
    <a href="{{ $link->author($sentence->author_id, $sentence->author->nickname) }}">
      {{ $sentence->author->nickname }}
    </a>
    @endif
  </footer>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence.js" async></script>
@endsection
