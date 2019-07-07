@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'gloss') !!}
  <div id="ed-gloss-form" data-inject-module="form-gloss"></div>

@endsection
