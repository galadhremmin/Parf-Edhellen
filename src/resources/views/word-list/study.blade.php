@extends('_layouts.default')

@section('title', __('word-list.study') . ' — ' . $wordList->name)
@section('description', __('word-list.study-description'))
@section('body')

<h1>{{ $wordList->name }}</h1>

{!! Breadcrumbs::render('word-list.study', $wordList) !!}

<div data-inject-module="word-list-study"
     data-inject-prop-word-list-id="{{ $wordList->id }}"
     data-inject-prop-word-list-name="{{ $wordList->name }}"
     data-inject-prop-direction="{{ $direction }}"></div>

@endsection
