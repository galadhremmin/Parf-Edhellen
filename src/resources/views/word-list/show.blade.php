@extends('_layouts.default')

@section('title', $wordList->name)
@section('description', $wordList->description ?: __('word-list.description'))
@section('body')

<h1>{{ $wordList->name }}</h1>

{!! Breadcrumbs::render('word-list.show', $wordList) !!}

@if (! empty($wordList->description))
<p>{{ $wordList->description }}</p>
@endif

<div data-inject-module="word-list"
     data-inject-prop-word-list-id="{{ $wordList->id }}"
     data-inject-prop-can-edit="{{ $canEdit ? 'true' : 'false' }}"></div>

@endsection
