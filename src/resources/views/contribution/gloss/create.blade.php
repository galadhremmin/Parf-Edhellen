@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'gloss') !!}

  <div id="ed-gloss-form" data-inject-module="form-gloss"
  @if (isset($payload)) 
    data-inject-prop-gloss="@json($payload)"
    data-inject-prop-inflections="@json($inflections)"
    data-inject-prop-confirm-button="Propose changes"
  @endif></div>

@endsection
