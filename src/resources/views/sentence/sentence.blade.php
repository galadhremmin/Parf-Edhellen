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
      @include('sentence._header')
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

  @ssr('sentence-inspector', ['sentence' => $sentence], [
    'element' => 'div',
    'id' => 'ed-fragment-navigator'
  ])

  @if (Auth::check())
  <p class="text-end">
    @if (Auth::user()->isAdministrator())
    <a href="{{ route('sentence.confirm-destroy', [ 'id' => $sentence['sentence']->id ]) }}" class="btn btn-secondary">
      <span class="TextIcon TextIcon--trash"></span>
      Delete
    </a>
    @endif
    <a href="{{ $link->contributeSentence($sentence['sentence']->id) }}" class="btn btn-secondary">
      <span class="TextIcon TextIcon--edit"></span>
      Propose changes
    </a>
  </p>
  @endif  

  <footer class="sentence-footer">
    &mdash;
    Source [{{ $sentence['sentence']->source }}]. 
    Published @date($sentence['sentence']->created_at)
    @if ($sentence['sentence']->updated_at)
    and edited @date($sentence['sentence']->updated_at)
    @endif
    @if ($sentence['sentence']->account)
    by 
    <a href="{{ $link->author($sentence['sentence']->account->id, $sentence['sentence']->account->nickname) }}">
      {{ $sentence['sentence']->account->nickname }}
    </a>
    @endif
  </footer>
  <hr>
  @include('_shared._ad', [
    'ad' => 'phrases'
  ])
  <hr>
  @include('discuss._standalone', [
    'entity_id'   => $sentence['sentence']->id,
    'entity_type' => 'sentence'
  ])
@endsection

@section('styles')
  <link href="@assetpath(style-sentence.css)" rel="stylesheet">
@endsection
