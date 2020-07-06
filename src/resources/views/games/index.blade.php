@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Games')
@section('body')
<h1>Games</h1>

{!! Breadcrumbs::render('games') !!}

<div class="link-blocks">
  @foreach ($games as $game)
  <blockquote>
    <a class="block-link" href="{{ $game->route }}">
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
