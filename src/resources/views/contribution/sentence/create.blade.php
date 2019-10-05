@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'sentence') !!}
  <div id="ed-sentence-form" data-admin="false"></div>

  <div id="ed-sentence-form"
    data-inject-module="form-sentence"
  @if (isset($sentence))
    data-inject-prop-sentence="@json($sentence)"
    data-inject-prop-sentence-fragments="@json($fragmentData)"
  @endif
  ></div>

@endsection
