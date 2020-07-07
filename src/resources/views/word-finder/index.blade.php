@extends('_layouts.default')

@section('title', __('word-finder.title.index'))
@section('body')

<h1>@lang('word-finder.title.index')</h1>
  
{!! Breadcrumbs::render('word-finder') !!}

<p>@lang('word-finder.description')</p>

<div class="link-blocks">
  @foreach ($games as $game)
  <blockquote>
    <a class="block-link" href="{{ route('word-finder.show', ['gameId' => $game->language_id]) }}">
      <h3>
        {{ $game->title }}
      </h3>
      <p>
        {{ $game->description }}
      </p>
    </a>
  </blockquote>
  @endforeach
</div>

@endsection
