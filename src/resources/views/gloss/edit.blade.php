@extends('_layouts.default')

@section('title', 'Edit '.$gloss->word->word.' - Administration')
@section('body')

<h1>Edit gloss {{ $gloss->word->word }}</h1>
{!! Breadcrumbs::render('gloss.edit', $gloss) !!}

<div id="ed-gloss-form"></div>

<script type="application/json" id="ed-preloaded-gloss">
{!! $gloss !!}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/gloss-admin.js)" async></script>
@endsection
