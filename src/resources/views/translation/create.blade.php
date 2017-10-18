@extends('_layouts.default')

@section('title', 'Add gloss - Administration')
@section('body')

<h1>Add gloss</h1>
{!! Breadcrumbs::render('translation.create') !!}

<div id="ed-translation-form"></div>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/glaemscribe.js)" async></script>
  <script type="text/javascript" src="@assetpath(/js/translation-admin.js)" async></script>
@endsection
