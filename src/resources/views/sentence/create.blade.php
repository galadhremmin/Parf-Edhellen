@extends('_layouts.default')

@section('title', 'Add sentence - Administration')
@section('body')

<h1>Add phrase</h1>
{!! Breadcrumbs::render('sentence.create') !!}

<div id="ed-sentence-form"></div>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/sentence-admin.js)" async></script>
@endsection
