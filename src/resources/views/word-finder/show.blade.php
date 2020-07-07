@extends('_layouts.default')

@section('title', __('word-finder.title.show', [ 'language' => $game->language->name ]))
@section('body')

<h1>@lang('word-finder.title.show', [ 'language' => $game->language->name ])</h1>
  
{!! Breadcrumbs::render('word-finder.show', $game) !!}

<p>@lang('word-finder.instructions1', ['language' => $game->language->name ])</p>
<p>@lang('word-finder.instructions2', ['language' => $game->language->name ])</p>

<div data-inject-module="word-finder"
     data-inject-prop-language-id="{{ $game->language_id }}"></div>

@endsection
