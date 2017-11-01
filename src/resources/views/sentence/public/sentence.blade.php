@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $sentence->name . ' (' . $language->name.')')
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
  @markdown($sentence->description)
  @endif

  <div id="ed-fragment-navigator"></div>
  <script type="application/json" id="ed-preload-sentence-data">{!! json_encode($sentenceData) !!}</script>

  @markdown($sentence->long_description)

  @if (Auth::check())
  <p class="text-right">
    @if (Auth::user()->isAdministrator())
    <a href="{{ route('sentence.confirm-destroy', [ 'id' => $sentence->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-trash"></span>
      Delete
    </a>
    <a href="{{ route('sentence.edit', [ 'id' => $sentence->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Edit phrase
    </a>
    @endif
    <a href="{{ route('contribution.create', [ 'morph' => 'sentence', 'entity_id' => $sentence->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Propose changes
    </a>
  </p>
  @endif  

  <footer class="sentence-footer">
    Source [{{ $sentence->source }}]. 
    Published <em title="{{ $sentence->created_at }}" class="date">{{ $sentence->created_at }}</em>
    @if ($sentence->updated_at)
    and edited <em title="{{ $sentence->updated_at }}" class="date">{{ $sentence->updated_at }}</em>
    @endif
    @if ($sentence->account_id)
    by 
    <a href="{{ $link->author($sentence->account_id, $sentence->account->nickname) }}">
      {{ $sentence->account->nickname }}
    </a>
    @endif
  </footer>
  <hr>
  @include('_shared._comments', [
    'entity_id' => $sentence->id,
    'morph'     => 'sentence',
    'enabled'   => true
  ])
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/sentence.js)" async></script>
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
