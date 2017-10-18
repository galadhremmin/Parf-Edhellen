@extends('_layouts.default')

@section('title', $sentence->name.' - Administration')
@section('body')

<h1>Edit phrase</h1>
{!! Breadcrumbs::render('sentence.edit', $sentence) !!}

<div id="ed-sentence-form"></div>

<hr>

Alternatively, you can <a href="{{ route('sentence.confirm-destroy', ['id' => $sentence->id]) }}">delete the phrase</a>.

<script type="application/json" id="ed-preloaded-sentence">
{!! $sentence !!}
</script>
<script type="application/json" id="ed-preloaded-sentence-fragments">
{!! json_encode($sentenceData) !!}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/glaemscribe.js)" async></script>
  <script type="text/javascript" src="@assetpath(/js/sentence-admin.js)" async></script>
@endsection
