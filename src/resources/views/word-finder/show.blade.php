@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>Word finder</h1>

<div data-inject-module="word-finder"
     data-inject-prop-language-id="{{ $languageId }}"></div>

@endsection
