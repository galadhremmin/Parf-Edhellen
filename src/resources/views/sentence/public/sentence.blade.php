@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $sentence['sentence']->name . ' (' . $language->name.')')
@section('body')

  {!! Breadcrumbs::render('sentence.public.sentence', $language->id, $language->name,
      $sentence['sentence']->id, $sentence['sentence']->name) !!}
  
  @if ($sentence['sentence']->is_neologism)
    @include('_shared._neologism', ['account' => $sentence['sentence']->account])
  @endif

  <div class="container">
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
  </div>

  <div id="ed-fragment-navigator" data-inject-module="sentence-inspector" data-inject-prop-sentence="{{ json_encode($sentence) }}"></div>

  @if (Auth::check())
  <p class="text-right">
    @if (Auth::user()->isAdministrator())
    <a href="{{ route('sentence.confirm-destroy', [ 'id' => $sentence['sentence']->id ]) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-trash"></span>
      Delete
    </a>
    @endif
    <a href="{{ $link->contributeSentence($sentence['sentence']->id) }}" class="btn btn-default">
      <span class="glyphicon glyphicon-edit"></span>
      Propose changes
    </a>
  </p>
  @endif  

  <footer class="sentence-footer">
    &mdash;
    Source [{{ $sentence['sentence']->source }}]. 
    Published <span class="date">{{ $sentence['sentence']->created_at }}</span>
    @if ($sentence['sentence']->updated_at)
    and edited <span class="date">{{ $sentence['sentence']->updated_at }}</span>
    @endif
    @if ($sentence['sentence']->account)
    by 
    <a href="{{ $link->author($sentence['sentence']->account->id, $sentence['sentence']->account->nickname) }}">
      {{ $sentence['sentence']->account->nickname }}
    </a>
    @endif
  </footer>
  <hr>
  @include('discuss._standalone', [
    'entity_id'   => $sentence['sentence']->id,
    'entity_type' => 'sentence'
  ])
@endsection

@section('styles')
  <link href="@assetpath(style-sentence.css)" rel="stylesheet">
@endsection
