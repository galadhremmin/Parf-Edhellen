@extends('_layouts.default')

@section('title', $sentence->name.' - Administration')
@section('body')

<h1>Edit phrase</h1>
{!! Breadcrumbs::render('sentence.edit', $sentence) !!}

<div id="ed-sentence-form"></div>

<script type="application/json" id="ed-preloaded-sentence">
{!! $sentence !!}
</script>
<script type="application/json" id="ed-preloaded-sentence-fragments">
{!! $fragments !!}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/sentence-admin.js" async></script>
@endsection
