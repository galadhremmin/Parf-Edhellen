@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $sentence['sentence']->name . ' (' . $language->name.')')
@section('body')

  {!! Breadcrumbs::render('sentence.public.sentence', $language->id, $language->name,
      $sentence['sentence']->id, $sentence['sentence']->name) !!}
  
  @if ($sentence['sentence']->is_neologism)
    @include('_shared._neologism', ['account' => $sentence['sentence']->account])
  @endif

  <header>
    @include('sentence.public._header')
    <h2>{{ $sentence['sentence']->name }}</h2>
  </header>

  @if (!empty($sentence['sentence']->description))
  <div class="abstract">
    @markdown($sentence['sentence']->description)
  </div>
  @endif

  @if (! empty($sentence['sentence']->long_description))
  <div class="long-text-body">
    @markdown($sentence['sentence']->long_description)
  </div>
  @endif

  <div id="ed-fragment-navigator" data-inject-module="sentence-inspector" data-inject-prop-sentence="{{ json_encode($sentence) }}"></div>

  @if (Auth::check())
  <p class="text-right">
    @if (Auth::user()->isAdministrator())
    <a href="{{ route('sentence.confirm-destroy', [ 'id' => $sentence['sentence']->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-trash"></span>
      Delete
    </a>
    <a href="{{ route('sentence.edit', [ 'id' => $sentence['sentence']->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Edit phrase
    </a>
    @endif
    <a href="{{ route('contribution.create', [ 'morph' => 'sentence', 'entity_id' => $sentence['sentence']->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Propose changes
    </a>
  </p>
  @endif  

  <footer class="sentence-footer">
    Source [{{ $sentence['sentence']->source }}]. 
    Published <em title="{{ $sentence['sentence']->created_at }}" class="date">{{ $sentence['sentence']->created_at }}</em>
    @if ($sentence['sentence']->updated_at)
    and edited <em title="{{ $sentence['sentence']->updated_at }}" class="date">{{ $sentence['sentence']->updated_at }}</em>
    @endif
    @if ($sentence['sentence']->account)
    by 
    <a href="{{ $link->author($sentence['sentence']->account->id, $sentence['sentence']->account->nickname) }}">
      {{ $sentence['sentence']->account->nickname }}
    </a>
    @endif
  </footer>
  <hr>
  @include('_shared._comments', [
    'entity_id' => $sentence['sentence']->id,
    'morph'     => 'sentence',
    'enabled'   => true
  ])
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/sentence.js)" async></script>
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection

@section('styles')
  <link href="@assetpath(/css/app.sentence.css)" rel="stylesheet">
@endsection
