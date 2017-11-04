@extends('_layouts.default')

@section('title', 'Add gloss - Administration')
@section('body')

<h1>Add gloss</h1>
{!! Breadcrumbs::render('gloss.create') !!}

<div id="ed-gloss-form"></div>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/gloss-admin.js)" async></script>
@endsection
