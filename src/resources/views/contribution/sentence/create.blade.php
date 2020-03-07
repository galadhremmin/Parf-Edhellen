@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'sentence') !!}
  <div id="ed-sentence-form" data-admin="false"></div>

  <div id="ed-sentence-form"ww
    data-inject-module="form-sentence"
  @if (isset($sentence))
    data-inject-prop-sentence="@json($sentence)"
    data-inject-prop-sentence-fragments="@json($fragments)"
    data-inject-prop-sentence-transformations="@json($transformations)"
    data-inject-prop-sentence-translations="@json($translations)"
    data-inject-prop-prefetched="true"
  @endif
  ></div>

@endsection
