@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases')
@section('body')

  {!! Breadcrumbs::render('sentence.public.sentence', $language->id, $language->name,
      $sentence->id, $sentence->name) !!}
  
  @if ($sentence->is_neologism)
    @include('_shared._neologism', ['account' => $sentence->account])
  @endif

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
    Published <em title="{{ $sentence->created_at }}">{{ $sentence->created_at->format('Y-m-d') }}</em>
    @if ($sentence->updated_at)
    and edited <em title="{{ $sentence->updated_at }}">{{ $sentence->updated_at->format('Y-m-d H:i') }}</em>
    @endif
    @if ($sentence->account_id)
    by 
    <a href="{{ $link->author($sentence->account_id, $sentence->account->nickname) }}">
      {{ $sentence->account->nickname }}
    </a>
    @endif
  </footer>
  <hr>
  <div id="ed-comments" data-entity-id="{{ $sentence->id }}" data-context="sentence" data-account-id="{{ Auth::check() ? Auth::user()->id : '0' }}"></div>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence.js" async></script>
  <script type="text/javascript" src="/js/comment.js" async></script>
@endsection
