@extends('_layouts.default')

@section('title', 'Edit '.$gloss->word->word.' - Administration')
@section('body')

<h1>Edit gloss &ldquo;{{ $gloss->word->word }}&rdquo;</h1>
{!! Breadcrumbs::render('gloss.edit', $gloss) !!}

<div id="ed-gloss-form" data-inject-module="form-gloss" data-inject-prop-gloss="@json($gloss)"></div>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/gloss-admin.js)" async></script>
@endsection
