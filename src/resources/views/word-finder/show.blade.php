@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>Word discoverer - {{ $language->name }}</h1>

<p>
  Below you have a list of words in English. Your task is to find the corresponding
  words in {{ $language->name }} using the letters below. You can combine the letters
  however you would like, but there is only one word per gloss.
</p>
<p>
  Tap on the letters to start assembling a word. The letters you have selected are
  presented in bold. If you regret your choice, tap on the letters to return them
  to the grid of available letters.
</p>

<div data-inject-module="word-finder"
     data-inject-prop-language-id="{{ $languageId }}"></div>

@endsection
