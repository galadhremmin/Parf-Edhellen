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

  {!! $sentence->long_description !!}

  @if (Auth::check() && Auth::user()->isAdministrator())
  <p class="text-right">
    <a href="{{ route('sentence.edit', [ 'id' => $sentence->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Edit phrase
    </a>
  </p>
  @endif  

  <footer class="sentence-footer">
    Source [{{ $sentence->source }}]. 
    Published {{ $sentence->created_at }}
    @if ($sentence->account_id)
    by 
    <a href="{{ $link->author($sentence->account_id, $sentence->account->nickname) }}">
      {{ $sentence->account->nickname }}
    </a>
    @endif
  </footer>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence.js" async></script>
@endsection
